<?php

namespace App\Http\Controllers\Api;

use App\Enums\EnrollmentStatus;
use App\Enums\TuitionStatus;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\Tuition;
use App\Services\CourseCapacityService;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function __construct(private CourseCapacityService $capacityService) {}

    public function __invoke(): JsonResponse
    {
        $stats = [
            'pending_admissions' => Admission::where('status', 'pending')->count(),
            'active_students' => Enrollment::where('status', EnrollmentStatus::Active)->count(),
            'representatives' => Representative::count(),
            'teachers' => Teacher::count(),
            'courses' => Course::count(),
            'pending_tuitions' => Tuition::whereIn('status', [TuitionStatus::Pending, TuitionStatus::Partial])->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'alerts' => $this->capacityService->alerts(),
        ]);
    }
}
