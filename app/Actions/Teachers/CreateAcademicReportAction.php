<?php

namespace App\Actions\Teachers;

use App\Models\AcademicReport;
use App\Models\Teacher;

class CreateAcademicReportAction
{
    /** @param array<string, mixed> $data */
    public function execute(Teacher $teacher, array $data): AcademicReport
    {
        return AcademicReport::create([
            'student_id' => $data['student_id'],
            'teacher_id' => $teacher->id,
            'development_area' => $data['development_area'],
            'evaluated_skill' => $data['evaluated_skill'],
            'achievement_level' => $data['achievement_level'],
            'observations' => $data['observations'] ?? null,
        ])->load('student');
    }
}
