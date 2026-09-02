<?php

namespace App\Actions\Representatives;

use App\Enums\EnrollmentStatus;
use App\Models\Admission;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class ListAvailableCoursesAction
{
    /** @return Collection<int, Course> */
    public function execute(Student $student): Collection
    {
        $levelId = Admission::query()->whereBelongsTo($student)->value('level_id');

        return Course::query()
            ->with('level')
            ->where('level_id', $levelId)
            ->withCount([
                'enrollments as active_count' => fn ($query) => $query->where('status', EnrollmentStatus::Active),
            ])
            ->get();
    }
}
