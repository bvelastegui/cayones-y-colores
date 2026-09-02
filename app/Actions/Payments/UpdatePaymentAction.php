<?php

namespace App\Actions\Payments;

use App\Models\Payment;
use App\Models\Tuition;
use App\Services\TuitionPaymentService;
use Illuminate\Support\Facades\DB;

class UpdatePaymentAction
{
    public function __construct(private TuitionPaymentService $tuitionPaymentService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data): Payment {
            $originalTuitionId = $payment->tuition_id;
            $payment->update($data);

            $this->tuitionPaymentService->synchronizeStatus($payment->tuition);

            if ($originalTuitionId !== $payment->tuition_id) {
                $originalTuition = Tuition::find($originalTuitionId);

                if ($originalTuition) {
                    $this->tuitionPaymentService->synchronizeStatus($originalTuition);
                }
            }

            return $payment->refresh();
        });
    }
}
