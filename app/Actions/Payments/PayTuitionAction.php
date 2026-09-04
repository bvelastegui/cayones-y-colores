<?php

namespace App\Actions\Payments;

use App\Data\Payments\GatewayPaymentResult;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PayphonePaymentStatus;
use App\Enums\TuitionConcept;
use App\Enums\TuitionStatus;
use App\Models\Enrollment;
use App\Models\PayphonePaymentAttempt;
use App\Models\Tuition;
use App\Models\User;
use App\Services\EnrollmentAuditService;
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
        private EnrollmentAuditService $enrollmentAuditService,
    ) {}

    /** @param Collection<int, Tuition> $tuitions */
    public function execute(
        Collection $tuitions,
        PaymentMethod $method,
        TuitionConcept $expectedConcept = TuitionConcept::Monthly,
        ?User $actor = null,
    ): GatewayPaymentResult {
        if ($tuitions->isEmpty()) {
            throw ValidationException::withMessages([
                'tuition_ids' => ['Selecciona al menos un rubro.'],
            ]);
        }

        $tuitionIds = $tuitions->modelKeys();
        sort($tuitionIds);

        $attempt = DB::transaction(function () use ($tuitionIds, $expectedConcept, $actor): PayphonePaymentAttempt {
            $lockedTuitions = Tuition::query()
                ->whereKey($tuitionIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($lockedTuitions->count() !== count($tuitionIds)) {
                throw ValidationException::withMessages([
                    'tuition_ids' => ['Uno de los rubros seleccionados ya no existe.'],
                ]);
            }

            foreach ($lockedTuitions as $tuition) {
                if ($tuition->concept !== $expectedConcept) {
                    throw ValidationException::withMessages([
                        'tuition_ids' => ['El pago contiene un tipo de rubro no permitido en esta operación.'],
                    ]);
                }
            }

            $enrollment = null;

            if ($expectedConcept === TuitionConcept::Enrollment && $lockedTuitions->count() !== 1) {
                throw ValidationException::withMessages([
                    'tuition_ids' => ['La matrícula debe pagarse sola en una transacción.'],
                ]);
            }

            if ($expectedConcept === TuitionConcept::Enrollment) {
                $enrollmentId = $lockedTuitions->firstOrFail()->enrollment_id;
                $enrollment = $enrollmentId === null
                    ? null
                    : Enrollment::query()->lockForUpdate()->find($enrollmentId);

                if (! $enrollment instanceof Enrollment
                    || ! in_array($enrollment->status, [EnrollmentStatus::PendingPayment, EnrollmentStatus::PaymentInProgress], true)) {
                    throw ValidationException::withMessages([
                        'tuition_ids' => ['El rubro no pertenece a una matrícula pendiente de pago.'],
                    ]);
                }
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
                    'tuition_ids' => ['Uno de los rubros ya está incluido en otro pago en proceso.'],
                ]);
            }

            $items = $lockedTuitions->map(function (Tuition $tuition): array {
                if ($tuition->status === TuitionStatus::Paid) {
                    throw ValidationException::withMessages([
                        'tuition_ids' => ['Uno de los rubros seleccionados ya está pagado.'],
                    ]);
                }

                $amountInCents = $this->tuitionPaymentService->remainingBalanceInCents($tuition);

                if ($amountInCents <= 0) {
                    throw ValidationException::withMessages([
                        'tuition_ids' => ['Uno de los rubros seleccionados no tiene saldo pendiente.'],
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

            if ($enrollment instanceof Enrollment) {
                $enrollment->update([
                    'status' => EnrollmentStatus::PaymentInProgress,
                    'payment_started_at' => now(),
                ]);
                $this->enrollmentAuditService->record($enrollment, $actor, 'enrollment_payment_started');
            }

            return $attempt;
        });

        if ($attempt->status === PayphonePaymentStatus::Prepared
            && $attempt->payphone_payment_id !== null
            && $attempt->payment_url !== null) {
            return new GatewayPaymentResult($attempt->payphone_payment_id, $attempt->payment_url);
        }

        try {
            $result = $this->gatewayResolver->resolve($method)->prepare(
                $expectedConcept === TuitionConcept::Enrollment
                    ? 'Pago de matrícula'
                    : 'Pago de '.$attempt->items()->count().' pensiones',
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

            if ($expectedConcept === TuitionConcept::Enrollment) {
                Enrollment::query()
                    ->whereIn('id', $attempt->items()->join('tuitions', 'tuitions.id', '=', 'payphone_payment_attempt_items.tuition_id')
                        ->pluck('tuitions.enrollment_id')->filter())
                    ->update(['status' => EnrollmentStatus::PendingPayment]);
            }

            throw $exception;
        }
    }
}
