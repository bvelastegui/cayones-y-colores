<?php

namespace App\Services;

use App\Enums\AssignedRole;
use App\Enums\EnrollmentStatus;
use App\Models\Course;

class CourseCapacityService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function alerts(): array
    {
        $courses = Course::with(['level', 'teachers', 'enrollments'])->get();
        $alerts = [];

        foreach ($courses as $course) {
            $activeCount = $course->enrollments
                ->where('status', EnrollmentStatus::Active->value)
                ->count();

            $maxCapacity = $course->level->max_capacity;
            $auxiliaryCount = $course->courseTeachers()
                ->where('assigned_role', AssignedRole::Auxiliary->value)
                ->count();
            $requiredAuxiliaries = $course->level->requiredAuxiliaries($activeCount);

            if ($activeCount >= $maxCapacity) {
                $alerts[] = [
                    'type' => 'capacity',
                    'severity' => 'critical',
                    'course_id' => $course->id,
                    'course' => "{$course->level->name} - Paralelo {$course->parallel}",
                    'message' => "Aforo máximo alcanado ({$activeCount}/{$maxCapacity} estudiantes). Inscripciones bloqueadas.",
                    'active_count' => $activeCount,
                    'max_capacity' => $maxCapacity,
                ];
            }

            if ($course->level->requiresAuxiliary() && $auxiliaryCount < $requiredAuxiliaries) {
                $alerts[] = [
                    'type' => 'auxiliary',
                    'severity' => 'warning',
                    'course_id' => $course->id,
                    'course' => "{$course->level->name} - Paralelo {$course->parallel}",
                    'message' => "Tiene {$activeCount} estudiantes. Requiere de manera obligatoria {$requiredAuxiliaries} profesor(es) auxiliar(es); actualmente tiene {$auxiliaryCount}.",
                    'active_count' => $activeCount,
                    'required_auxiliaries' => $requiredAuxiliaries,
                    'current_auxiliaries' => $auxiliaryCount,
                ];
            }
        }

        return $alerts;
    }
}
