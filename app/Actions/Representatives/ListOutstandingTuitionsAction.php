<?php

namespace App\Actions\Representatives;

use App\Enums\TuitionStatus;
use App\Models\Representative;
use App\Models\Tuition;
use Illuminate\Database\Eloquent\Collection;

class ListOutstandingTuitionsAction
{
    /** @return Collection<int, Tuition> */
    public function execute(Representative $representative): Collection
    {
        return Tuition::query()
            ->with(['student', 'payments'])
            ->whereIn('student_id', $representative->students()->select('id'))
            ->whereIn('status', [TuitionStatus::Pending, TuitionStatus::Partial, TuitionStatus::Overdue])
            ->orderBy('due_date')
            ->get();
    }
}
