<?php

namespace App\Actions\Payments;

use App\Enums\PaymentMethod;
use App\Enums\TuitionStatus;
use App\Models\Payment;
use App\Models\Tuition;
use App\Services\PaymentGatewayResolver;
use App\Services\TuitionPaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayTuitionAction
{
    public function __construct(
        private PaymentGatewayResolver $gatewayResolver,
        private TuitionPaymentService $tuitionPaymentService,
    ) {}

    public function execute(Tuition $tuition, PaymentMethod $method): Payment
    {
        if ($tuition->status === TuitionStatus::Paid) {
            throw ValidationException::withMessages([
                'tuition_id' => ['La pensión ya está pagada.'],
            ]);
        }

        return DB::transaction(function () use ($tuition, $method): Payment {
            $lockedTuition = Tuition::query()->lockForUpdate()->findOrFail($tuition->id);
            $amount = $lockedTuition->remainingBalance();

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'tuition_id' => ['La pensión no tiene saldo pendiente.'],
                ]);
            }

            $result = $this->gatewayResolver->resolve($method)->pay($lockedTuition);

            $payment = Payment::create([
                'tuition_id' => $lockedTuition->id,
                'payment_method' => $method,
                'amount_paid' => $amount,
                'payment_date' => now()->toDateString(),
                'reference_number' => $result->referenceNumber,
            ]);

            $this->tuitionPaymentService->synchronizeStatus($lockedTuition);

            return $payment;
        });
    }
}
