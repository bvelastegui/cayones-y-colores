<?php

namespace App\Actions\Teachers;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

class ListTeacherCoursesAction
{
    /** @return Collection<int, Course> */
    public function execute(Teacher $teacher): Collection
    {
        return $teacher->courses()
            ->with('level')
            ->withCount([
                'enrollments as active_students_count' => fn ($query) => $query->where('status', EnrollmentStatus::Active),
            ])
            ->get();
    }
}
