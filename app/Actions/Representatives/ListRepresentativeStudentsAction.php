<?php

namespace App\Actions\Representatives;

use App\Models\Representative;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class ListRepresentativeStudentsAction
{
    /** @return Collection<int, Student> */
    public function execute(Representative $representative): Collection
    {
        return $representative->students()
            ->with([
                'admission.level',
                'level',
                'enrollments' => fn ($query) => $query->latest('id'),
                'enrollments.level',
                'enrollments.course.level',
                'enrollments.course.courseTeachers.teacher',
            ])
            ->get();
    }
}
