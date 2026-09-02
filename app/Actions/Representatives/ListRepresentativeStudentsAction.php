<?php

namespace App\Actions\Representatives;

use App\Enums\EnrollmentStatus;
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
                'enrollments' => fn ($query) => $query->where('status', EnrollmentStatus::Active)->limit(1),
                'enrollments.course.level',
            ])
            ->get();
    }
}
