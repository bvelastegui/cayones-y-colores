<?php

namespace App\Actions\Payments;

use App\Models\Payment;
use App\Services\TuitionPaymentService;
use Illuminate\Support\Facades\DB;

class CreatePaymentAction
{
    public function __construct(private TuitionPaymentService $tuitionPaymentService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Payment
    {
        return DB::transaction(function () use ($data): Payment {
            $payment = Payment::create($data);
            $this->tuitionPaymentService->synchronizeStatus($payment->tuition);

            return $payment;
        });
    }
}
