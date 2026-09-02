<?php

namespace App\Services;

use App\Enums\TuitionStatus;
use App\Models\Tuition;

class TuitionPaymentService
{
    public function synchronizeStatus(Tuition $tuition): void
    {
        $paid = (float) $tuition->payments()->sum('amount_paid');
        $balance = (float) $tuition->amount - $paid;

        $status = match (true) {
            $balance <= 0 => TuitionStatus::Paid,
            $paid > 0 => TuitionStatus::Partial,
            default => TuitionStatus::Pending,
        };

        $tuition->update(['status' => $status]);
    }
}
