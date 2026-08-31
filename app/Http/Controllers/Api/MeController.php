<?php

namespace App\Http\Controllers\Api;

use App\Enums\EnrollmentStatus;
use App\Enums\TuitionStatus;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Course;
use App\Models\Representative;
use App\Models\Student;
use App\Models\Tuition;
use App\Services\EnrollmentService;
use App\Services\PayphoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MeController extends Controller
{
    public function students(Request $request): JsonResponse
    {
        $representative = $this->representative($request);

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
        if (! $this->ownsStudent($request, $student)) {
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

        if (! $this->ownsStudent($request, $student)) {
            return response()->json(['message' => 'No autorizado.'], Response::HTTP_FORBIDDEN);
        }

        $enrollment = $enrollmentService->enroll($student, $course, $request->user());

        return response()->json($enrollment, Response::HTTP_CREATED);
    }

    public function tuitions(Request $request): JsonResponse
    {
        $representative = $this->representative($request);

        if (! $representative) {
            return response()->json([]);
        }

        $studentIds = $representative->students()->pluck('id');

        $tuitions = Tuition::with(['student', 'payments'])
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', [TuitionStatus::Pending, TuitionStatus::Partial, TuitionStatus::Overdue])
            ->orderBy('due_date')
            ->get();

        return response()->json($tuitions);
    }

    public function reports(Request $request, Student $student): JsonResponse
    {
        if (! $this->ownsStudent($request, $student)) {
            return response()->json(['message' => 'No autorizado.'], Response::HTTP_FORBIDDEN);
        }

        $reports = $student->academicReports()
            ->with('teacher')
            ->latest()
            ->get();

        return response()->json($reports);
    }

    public function payWithPayphone(Request $request, PayphoneService $payphoneService): JsonResponse
    {
        $data = $request->validate([
            'tuition_id' => ['required', 'exists:tuitions,id'],
        ]);

        $tuition = Tuition::findOrFail($data['tuition_id']);

        if (! $this->ownsStudent($request, $tuition->student)) {
            return response()->json(['message' => 'No autorizado.'], Response::HTTP_FORBIDDEN);
        }

        if ($tuition->status === TuitionStatus::Paid) {
            return response()->json(['message' => 'La pensión ya está pagada.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payment = $payphoneService->payTuition($tuition);

        return response()->json([
            'payment' => $payment->load('tuition.student'),
            'message' => 'Pago procesado exitosamente.',
        ]);
    }

    private function representative(Request $request): ?Representative
    {
        return $request->user()->representative;
    }

    private function ownsStudent(Request $request, Student $student): bool
    {
        $representative = $this->representative($request);

        if (! $representative) {
            return false;
        }

        return $student->representative_id === $representative->id;
    }
}
