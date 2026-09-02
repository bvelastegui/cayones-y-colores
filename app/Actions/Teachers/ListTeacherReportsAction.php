<?php

namespace App\Actions\Teachers;

use App\Models\AcademicReport;
use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTeacherReportsAction
{
    /** @return LengthAwarePaginator<int, AcademicReport> */
    public function execute(Teacher $teacher, int $perPage): LengthAwarePaginator
    {
        return AcademicReport::query()
            ->with('student')
            ->whereBelongsTo($teacher)
            ->latest()
            ->paginate($perPage);
    }
}
