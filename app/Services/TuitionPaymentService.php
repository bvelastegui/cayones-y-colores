<?php

namespace App\Services;

use App\Enums\TuitionStatus;
use App\Models\Tuition;

class TuitionPaymentService
{
    public function synchronizeStatus(Tuition $tuition): void
    {
        $paidInCents = $this->toCents((string) $tuition->payments()->sum('amount_paid'));
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
            - $this->toCents((string) $tuition->payments()->sum('amount_paid'));
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
}
