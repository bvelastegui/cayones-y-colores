<?php

namespace App\Services;

use App\Enums\TuitionStatus;
use App\Models\Payment;
use App\Models\Tuition;

class TuitionPaymentService
{
    public function synchronizeStatus(Tuition $tuition): void
    {
        $paidInCents = $this->paidInCents($tuition);
        $balanceInCents = $this->toCents((string) $tuition->amount) - $paidInCents;

        $status = match (true) {
            $balanceInCents <= 0 => TuitionStatus::Paid,
            $paidInCents > 0 => TuitionStatus::Partial,
            default => TuitionStatus::Pending,
        };

        $tuition->update(['status' => $status]);
    }

    public function remainingBalanceInCents(Tuition $tuition): int
    {
        return $this->toCents((string) $tuition->amount)
            - $this->paidInCents($tuition);
    }

    public function centsToDecimal(int $amountInCents): string
    {
        return sprintf('%d.%02d', intdiv($amountInCents, 100), $amountInCents % 100);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function paidInCents(Tuition $tuition): int
    {
        if ($tuition->relationLoaded('payments')) {
            return $tuition->payments->sum(
                fn (Payment $payment): int => $this->toCents((string) $payment->amount_paid),
            );
        }

        return $this->toCents((string) $tuition->payments()->sum('amount_paid'));
    }
}
