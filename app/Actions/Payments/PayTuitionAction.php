<?php

namespace App\Actions\Payments;

use App\Data\Payments\GatewayPaymentResult;
use App\Enums\PaymentMethod;
use App\Enums\PayphonePaymentStatus;
use App\Enums\TuitionStatus;
use App\Models\PayphonePaymentAttempt;
use App\Models\Tuition;
use App\Services\PaymentGatewayResolver;
use App\Services\TuitionPaymentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PayTuitionAction
{
    public function __construct(
        private PaymentGatewayResolver $gatewayResolver,
        private TuitionPaymentService $tuitionPaymentService,
    ) {}

    /** @param Collection<int, Tuition> $tuitions */
    public function execute(Collection $tuitions, PaymentMethod $method): GatewayPaymentResult
    {
        if ($tuitions->isEmpty()) {
            throw ValidationException::withMessages([
                'tuition_ids' => ['Selecciona al menos una pensión.'],
            ]);
        }

        $tuitionIds = $tuitions->modelKeys();
        sort($tuitionIds);

        $attempt = DB::transaction(function () use ($tuitionIds): PayphonePaymentAttempt {
            $lockedTuitions = Tuition::query()
                ->whereKey($tuitionIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($lockedTuitions->count() !== count($tuitionIds)) {
                throw ValidationException::withMessages([
                    'tuition_ids' => ['Una de las pensiones seleccionadas ya no existe.'],
                ]);
            }

            $activeAttempt = PayphonePaymentAttempt::query()
                ->whereIn('status', [PayphonePaymentStatus::Creating, PayphonePaymentStatus::Prepared])
                ->where('expires_at', '>', now())
                ->whereHas('items', fn ($query) => $query->whereIn('tuition_id', $tuitionIds))
                ->with('items')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($activeAttempt !== null) {
                $attemptTuitionIds = $activeAttempt->items->pluck('tuition_id')->all();
                sort($attemptTuitionIds);

                if ($activeAttempt->status === PayphonePaymentStatus::Prepared
                    && $attemptTuitionIds === $tuitionIds) {
                    return $activeAttempt;
                }

                throw ValidationException::withMessages([
                    'tuition_ids' => ['Una de las pensiones ya está incluida en otro pago en proceso.'],
                ]);
            }

            $items = $lockedTuitions->map(function (Tuition $tuition): array {
                if ($tuition->status === TuitionStatus::Paid) {
                    throw ValidationException::withMessages([
                        'tuition_ids' => ['Una de las pensiones seleccionadas ya está pagada.'],
                    ]);
                }

                $amountInCents = $this->tuitionPaymentService->remainingBalanceInCents($tuition);

                if ($amountInCents <= 0) {
                    throw ValidationException::withMessages([
                        'tuition_ids' => ['Una de las pensiones seleccionadas no tiene saldo pendiente.'],
                    ]);
                }

                return [
                    'tuition_id' => $tuition->id,
                    'amount_in_cents' => $amountInCents,
                ];
            });

            $attempt = PayphonePaymentAttempt::create([
                'tuition_id' => $lockedTuitions->firstOrFail()->id,
                'client_transaction_id' => (string) Str::uuid(),
                'amount_in_cents' => $items->sum('amount_in_cents'),
                'status' => PayphonePaymentStatus::Creating,
                'expires_at' => now()->addMinutes(10),
            ]);

            $attempt->items()->createMany($items->all());

            return $attempt;
        });

        if ($attempt->status === PayphonePaymentStatus::Prepared
            && $attempt->payphone_payment_id !== null
            && $attempt->payment_url !== null) {
            return new GatewayPaymentResult($attempt->payphone_payment_id, $attempt->payment_url);
        }

        try {
            $result = $this->gatewayResolver->resolve($method)->prepare(
                'Pago de '.$attempt->items()->count().' pensiones',
                $attempt->client_transaction_id,
                $attempt->amount_in_cents,
            );

            $attempt->update([
                'status' => PayphonePaymentStatus::Prepared,
                'payphone_payment_id' => $result->paymentId,
                'payment_url' => $result->paymentUrl,
            ]);

            return $result;
        } catch (Throwable $exception) {
            $attempt->update(['status' => PayphonePaymentStatus::Failed]);

            throw $exception;
        }
    }
}
