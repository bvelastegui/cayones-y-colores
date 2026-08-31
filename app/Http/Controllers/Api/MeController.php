<?php

namespace App\Http\Controllers\Api;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Course;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MeController extends Controller
{
    public function students(Request $request): JsonResponse
    {
        $representative = $request->user()->representative;

        if (! $representative) {
            return response()->json([]);
        }

        $students = $representative->students()
            ->with([
                'admission.level',
                'enrollments' => fn ($query) => $query->where('status', EnrollmentStatus::Active)->limit(1),
                'enrollments.course.level',
            ])
            ->get();

        return response()->json($students);
    }

    public function availableCourses(Request $request, Student $student): JsonResponse
    {
        if ($student->representative->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], Response::HTTP_FORBIDDEN);
        }

        $levelId = Admission::where('student_id', $student->id)->value('level_id');

        $courses = Course::query()
            ->with('level')
            ->where('level_id', $levelId)
            ->withCount([
                'enrollments as active_count' => function ($query): void {
                    $query->where('status', EnrollmentStatus::Active);
                },
            ])
            ->get();

        return response()->json($courses);
    }

    public function enroll(Request $request, EnrollmentService $enrollmentService): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $course = Course::findOrFail($data['course_id']);

        $enrollment = $enrollmentService->enroll($student, $course, $request->user());

        return response()->json($enrollment, Response::HTTP_CREATED);
    }
}
