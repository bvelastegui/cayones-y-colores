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

    public function execute(Tuition $tuition, PaymentMethod $method): GatewayPaymentResult
    {
        if ($tuition->status === TuitionStatus::Paid) {
            throw ValidationException::withMessages([
                'tuition_id' => ['La pensión ya está pagada.'],
            ]);
        }

        $attempt = DB::transaction(function () use ($tuition): PayphonePaymentAttempt {
            $lockedTuition = Tuition::query()->lockForUpdate()->findOrFail($tuition->id);
            $amountInCents = $this->tuitionPaymentService->remainingBalanceInCents($lockedTuition);

            if ($amountInCents <= 0) {
                throw ValidationException::withMessages([
                    'tuition_id' => ['La pensión no tiene saldo pendiente.'],
                ]);
            }

            $activeAttempt = PayphonePaymentAttempt::query()
                ->whereBelongsTo($lockedTuition)
                ->whereIn('status', [PayphonePaymentStatus::Creating, PayphonePaymentStatus::Prepared])
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($activeAttempt?->status === PayphonePaymentStatus::Creating) {
                throw ValidationException::withMessages([
                    'tuition_id' => ['Ya se está preparando un pago para esta pensión.'],
                ]);
            }

            if ($activeAttempt) {
                return $activeAttempt;
            }

            return PayphonePaymentAttempt::create([
                'tuition_id' => $lockedTuition->id,
                'client_transaction_id' => (string) Str::uuid(),
                'amount_in_cents' => $amountInCents,
                'status' => PayphonePaymentStatus::Creating,
                'expires_at' => now()->addMinutes(10),
            ]);
        });

        if ($attempt->status === PayphonePaymentStatus::Prepared
            && $attempt->payphone_payment_id !== null
            && $attempt->payment_url !== null) {
            return new GatewayPaymentResult($attempt->payphone_payment_id, $attempt->payment_url);
        }

        try {
            $result = $this->gatewayResolver->resolve($method)->prepare(
                $attempt->tuition()->with('student')->firstOrFail(),
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
