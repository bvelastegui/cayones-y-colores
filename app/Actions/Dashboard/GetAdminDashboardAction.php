<?php

namespace App\Actions\Dashboard;

use App\Enums\AdmissionStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\TuitionStatus;
use App\Models\Admission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\Tuition;
use App\Services\CourseCapacityService;

class GetAdminDashboardAction
{
    public function __construct(private CourseCapacityService $capacityService) {}

    /** @return array{stats: array<string, int>, alerts: array<int, array<string, mixed>>} */
    public function execute(): array
    {
        return [
            'stats' => [
                'pending_admissions' => Admission::where('status', AdmissionStatus::Pending)->count(),
                'active_students' => Enrollment::where('status', EnrollmentStatus::Active)->count(),
                'representatives' => Representative::count(),
                'teachers' => Teacher::count(),
                'courses' => Course::count(),
                'pending_tuitions' => Tuition::whereIn('status', [TuitionStatus::Pending, TuitionStatus::Partial])->count(),
            ],
            'alerts' => $this->capacityService->alerts(),
        ];
    }
}
