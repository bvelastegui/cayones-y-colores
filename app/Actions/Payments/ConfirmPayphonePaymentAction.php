<?php

namespace App\Actions\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PayphonePaymentStatus;
use App\Models\Payment;
use App\Models\PayphonePaymentAttempt;
use App\Services\PayphonePaymentGateway;
use App\Services\TuitionPaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmPayphonePaymentAction
{
    public function __construct(
        private PayphonePaymentGateway $gateway,
        private TuitionPaymentService $tuitionPaymentService,
    ) {}

    public function execute(int $transactionId, string $clientTransactionId): bool
    {
        $attempt = PayphonePaymentAttempt::query()
            ->where('client_transaction_id', $clientTransactionId)
            ->firstOrFail();

        if ($attempt->status === PayphonePaymentStatus::Approved) {
            return true;
        }

        $confirmation = $this->gateway->confirm($transactionId, $clientTransactionId);

        if ($confirmation->clientTransactionId !== $attempt->client_transaction_id
            || $confirmation->amountInCents !== $attempt->amount_in_cents) {
            throw ValidationException::withMessages([
                'transaction' => ['La confirmación de PayPhone no coincide con el pago preparado.'],
            ]);
        }

        if (! $confirmation->isApproved()) {
            $attempt->update([
                'status' => PayphonePaymentStatus::Cancelled,
                'transaction_id' => $confirmation->transactionId,
                'confirmed_at' => now(),
            ]);

            return false;
        }

        return DB::transaction(function () use ($attempt, $confirmation): bool {
            $lockedAttempt = PayphonePaymentAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($lockedAttempt->status === PayphonePaymentStatus::Approved) {
                return true;
            }

            $tuition = $lockedAttempt->tuition()->lockForUpdate()->firstOrFail();

            Payment::create([
                'tuition_id' => $tuition->id,
                'payment_method' => PaymentMethod::Payphone,
                'amount_paid' => $this->tuitionPaymentService->centsToDecimal($lockedAttempt->amount_in_cents),
                'payment_date' => now()->toDateString(),
                'reference_number' => "PAYPHONE-{$confirmation->transactionId}",
            ]);

            $lockedAttempt->update([
                'status' => PayphonePaymentStatus::Approved,
                'transaction_id' => $confirmation->transactionId,
                'confirmed_at' => now(),
            ]);

            $this->tuitionPaymentService->synchronizeStatus($tuition);

            return true;
        });
    }
}
