<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\TuitionStatus;
use App\Models\Payment;
use App\Models\Tuition;
use Illuminate\Support\Str;

class PayphoneService
{
    /**
     * Initiate a Payphone payment for the given tuition.
     *
     * This is a stub implementation: when real Payphone credentials are
     * configured it should call the Payphone API and return the approval URL.
     * For now it simulates an approved payment and records it immediately.
     */
    public function payTuition(Tuition $tuition): Payment
    {
        $balance = $tuition->remainingBalance();

        $payment = Payment::create([
            'tuition_id' => $tuition->id,
            'payment_method' => PaymentMethod::Payphone,
            'amount_paid' => $balance,
            'payment_date' => now()->toDateString(),
            'reference_number' => 'PAYPHONE-'.Str::random(8),
        ]);

        $this->syncTuitionStatus($tuition);

        return $payment;
    }

    private function syncTuitionStatus(Tuition $tuition): void
    {
        $paid = (float) $tuition->payments()->sum('amount_paid');
        $balance = (float) $tuition->amount - $paid;

        if ($balance <= 0) {
            $tuition->update(['status' => TuitionStatus::Paid]);
        } elseif ($paid > 0) {
            $tuition->update(['status' => TuitionStatus::Partial]);
        } else {
            $tuition->update(['status' => TuitionStatus::Pending]);
        }
    }
}
