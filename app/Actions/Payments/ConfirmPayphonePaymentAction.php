<?php

namespace App\Actions\Payments;

use App\Data\Payments\GatewayPaymentConfirmation;
use App\Enums\AcademicPeriodStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PayphonePaymentStatus;
use App\Enums\TuitionConcept;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PayphonePaymentAttempt;
use App\Models\PayphonePaymentAttemptItem;
use App\Models\Tuition;
use App\Services\EnrollmentAuditService;
use App\Services\PayphonePaymentGateway;
use App\Services\TuitionPaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmPayphonePaymentAction
{
    public function __construct(
        private PayphonePaymentGateway $gateway,
        private TuitionPaymentService $tuitionPaymentService,
        private EnrollmentAuditService $enrollmentAuditService,
    ) {}

    public function execute(int $transactionId, string $clientTransactionId): bool
    {
        $attempt = PayphonePaymentAttempt::query()
            ->where('client_transaction_id', $clientTransactionId)
            ->firstOrFail();

        if ($attempt->status === PayphonePaymentStatus::Approved) {
            return true;
        }

        if ($attempt->status === PayphonePaymentStatus::Cancelled) {
            return false;
        }

        $confirmation = $this->gateway->confirm($transactionId, $clientTransactionId);

        if ($confirmation->transactionId !== $transactionId
            || $confirmation->clientTransactionId !== $attempt->client_transaction_id
            || $confirmation->amountInCents !== $attempt->amount_in_cents
            || $confirmation->currency !== config('services.payphone.currency')) {
            throw ValidationException::withMessages([
                'transaction' => ['La confirmación de PayPhone no coincide con el pago preparado.'],
            ]);
        }

        if (! $confirmation->isApproved()) {
            return $this->cancel($attempt, $confirmation);
        }

        return DB::transaction(function () use ($attempt, $confirmation): bool {
            $lockedAttempt = PayphonePaymentAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($lockedAttempt->status === PayphonePaymentStatus::Approved) {
                return true;
            }

            $items = PayphonePaymentAttemptItem::query()
                ->whereBelongsTo($lockedAttempt, 'attempt')
                ->orderBy('tuition_id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'transaction' => ['El pago preparado no contiene pensiones.'],
                ]);
            }

            $tuitions = Tuition::query()
                ->whereKey($items->pluck('tuition_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $tuition = $tuitions->get($item->tuition_id);

                if (! $tuition instanceof Tuition) {
                    throw ValidationException::withMessages([
                        'transaction' => ['Una pensión del pago preparado ya no existe.'],
                    ]);
                }

                Payment::create([
                    'tuition_id' => $tuition->id,
                    'payment_method' => PaymentMethod::Payphone,
                    'amount_paid' => $this->tuitionPaymentService->centsToDecimal($item->amount_in_cents),
                    'payment_date' => now()->toDateString(),
                    'reference_number' => "PAYPHONE-{$confirmation->transactionId}",
                ]);

                $this->tuitionPaymentService->synchronizeStatus($tuition);

                if ($tuition->concept === TuitionConcept::Enrollment && $tuition->enrollment_id !== null) {
                    $enrollment = Enrollment::query()->with('form')->lockForUpdate()->findOrFail($tuition->enrollment_id);
                    $enrollment->form?->update([
                        'snapshot_data' => $enrollment->form->snapshot_data ?? $enrollment->form->submitted_data,
                    ]);
                    $enrollment->update([
                        'status' => EnrollmentStatus::PaidPendingAssignment,
                        'paid_at' => now(),
                    ]);
                    $this->enrollmentAuditService->record($enrollment, null, 'enrollment_payment_confirmed', [
                        'transaction_id' => $confirmation->transactionId,
                    ]);

                    $period = $enrollment->academicPeriod()->lockForUpdate()->first();
                    if ($period !== null && now()->gte($period->enrollment_closes_at)) {
                        $period->update([
                            'status' => AcademicPeriodStatus::Closed,
                            'allocation_completed_at' => null,
                        ]);
                    }
                }
            }

            $lockedAttempt->update([
                'status' => PayphonePaymentStatus::Approved,
                'transaction_id' => $confirmation->transactionId,
                'confirmed_at' => now(),
            ]);

            return true;
        });
    }

    private function cancel(
        PayphonePaymentAttempt $attempt,
        GatewayPaymentConfirmation $confirmation,
    ): bool {
        return DB::transaction(function () use ($attempt, $confirmation): bool {
            $lockedAttempt = PayphonePaymentAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($lockedAttempt->status === PayphonePaymentStatus::Approved) {
                return true;
            }

            if ($lockedAttempt->status === PayphonePaymentStatus::Cancelled) {
                return false;
            }

            $lockedAttempt->update([
                'status' => PayphonePaymentStatus::Cancelled,
                'transaction_id' => $confirmation->transactionId,
                'confirmed_at' => now(),
            ]);

            $tuitions = Tuition::query()
                ->whereKey($lockedAttempt->items()->pluck('tuition_id'))
                ->where('concept', TuitionConcept::Enrollment)
                ->whereNotNull('enrollment_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($tuitions as $tuition) {
                $enrollment = Enrollment::query()->lockForUpdate()->find($tuition->enrollment_id);

                if (! $enrollment instanceof Enrollment
                    || $enrollment->status !== EnrollmentStatus::PaymentInProgress) {
                    continue;
                }

                $hasAnotherActiveAttempt = PayphonePaymentAttempt::query()
                    ->where('id', '!=', $lockedAttempt->id)
                    ->whereIn('status', [
                        PayphonePaymentStatus::Creating,
                        PayphonePaymentStatus::Prepared,
                        PayphonePaymentStatus::Approved,
                    ])
                    ->whereHas('items', fn ($query) => $query->where('tuition_id', $tuition->id))
                    ->exists();

                if ($hasAnotherActiveAttempt) {
                    continue;
                }

                $enrollment->update(['status' => EnrollmentStatus::PendingPayment]);
                $this->enrollmentAuditService->record($enrollment, null, 'enrollment_payment_cancelled', [
                    'transaction_id' => $confirmation->transactionId,
                ]);
            }

            return false;
        });
    }
}
