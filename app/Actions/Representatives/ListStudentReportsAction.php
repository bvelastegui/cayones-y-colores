<?php

namespace App\Actions\Representatives;

use App\Models\AcademicReport;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class ListStudentReportsAction
{
    /** @return Collection<int, AcademicReport> */
    public function execute(Student $student): Collection
    {
        return $student->academicReports()->with('teacher')->latest()->get();
    }
}
