<?php

namespace App\Actions\Payments;

use App\Models\Payment;
use App\Models\Tuition;
use App\Services\TuitionPaymentService;
use Illuminate\Support\Facades\DB;

class DeletePaymentAction
{
    public function __construct(private TuitionPaymentService $tuitionPaymentService) {}

    public function execute(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $tuitionId = $payment->tuition_id;
            $payment->delete();

            $tuition = Tuition::find($tuitionId);

            if ($tuition) {
                $this->tuitionPaymentService->synchronizeStatus($tuition);
            }
        });
    }
}
