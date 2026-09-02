<?php

namespace App\Actions\Teachers;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class ListCourseStudentsAction
{
    /** @return Collection<int, Student> */
    public function execute(Course $course): Collection
    {
        return $course->students()->wherePivot('status', EnrollmentStatus::Active)->get();
    }
}
