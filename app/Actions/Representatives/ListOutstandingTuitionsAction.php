<?php

namespace App\Actions\Representatives;

use App\Enums\TuitionStatus;
use App\Models\Representative;
use App\Models\Tuition;
use App\Services\TuitionPaymentService;
use Illuminate\Database\Eloquent\Collection;

class ListOutstandingTuitionsAction
{
    public function __construct(private TuitionPaymentService $tuitionPaymentService) {}

    /** @return Collection<int, Tuition> */
    public function execute(Representative $representative): Collection
    {
        $tuitions = Tuition::query()
            ->with(['student', 'payments'])
            ->whereIn('student_id', $representative->students()->select('id'))
            ->whereIn('status', [TuitionStatus::Pending, TuitionStatus::Partial, TuitionStatus::Overdue])
            ->orderBy('due_date')
            ->get();

        return $tuitions->each(function (Tuition $tuition): void {
            $tuition->setAttribute(
                'remaining_balance',
                $this->tuitionPaymentService->centsToDecimal(
                    $this->tuitionPaymentService->remainingBalanceInCents($tuition),
                ),
            );
        });
    }
}
