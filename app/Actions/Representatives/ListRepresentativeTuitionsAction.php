<?php

namespace App\Actions\Representatives;

use App\Models\Representative;
use App\Models\Tuition;
use App\Services\TuitionPaymentService;
use Illuminate\Database\Eloquent\Collection;

class ListRepresentativeTuitionsAction
{
    public function __construct(private TuitionPaymentService $tuitionPaymentService) {}

    /** @return Collection<int, Tuition> */
    public function execute(Representative $representative): Collection
    {
        $tuitions = Tuition::query()
            ->with([
                'student',
                'enrollment.academicPeriod',
                'payments' => fn ($query) => $query
                    ->orderByDesc('payment_date')
                    ->orderByDesc('id'),
            ])
            ->whereIn('student_id', $representative->students()->select('id'))
            ->orderByDesc('generation_date')
            ->get();

        return $tuitions->each(function (Tuition $tuition): void {
            $tuition->setAttribute(
                'remaining_balance',
                $this->tuitionPaymentService->centsToDecimal(
                    max(0, $this->tuitionPaymentService->remainingBalanceInCents($tuition)),
                ),
            );
        });
    }
}
