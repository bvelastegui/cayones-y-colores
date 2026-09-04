<?php

namespace App\Http\Controllers\Api;

use App\Actions\Teachers\CreateAcademicReportAction;
use App\Actions\Teachers\ListCourseStudentsAction;
use App\Actions\Teachers\ListTeacherCoursesAction;
use App\Actions\Teachers\ListTeacherReportsAction;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTeacherReportRequest;
use App\Models\Course;
use App\Models\Student;
use App\Services\EnrollmentAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TeacherDashboardController extends Controller
{
    public function courses(Request $request, ListTeacherCoursesAction $listCourses): JsonResponse
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json(['message' => 'Perfil de docente no encontrado.'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($listCourses->execute($teacher));
    }

    public function students(
        Request $request,
        Course $course,
        ListCourseStudentsAction $listStudents,
    ): JsonResponse {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json(['message' => 'Perfil de docente no encontrado.'], Response::HTTP_FORBIDDEN);
        }

        Gate::forUser($request->user())->authorize('teach', $course);

        return response()->json($listStudents->execute($course));
    }

    public function reports(Request $request, ListTeacherReportsAction $listReports): JsonResponse
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json(['message' => 'Perfil de docente no encontrado.'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($listReports->execute($teacher, $request->integer('per_page', 15)));
    }

    public function storeReport(StoreTeacherReportRequest $request, CreateAcademicReportAction $createReport): JsonResponse
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json(['message' => 'Perfil de docente no encontrado.'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $course = Course::findOrFail($data['course_id']);
        Gate::forUser($request->user())->authorize('teach', $course);
        $report = $createReport->execute($teacher, $data);

        return response()->json($report->load('student'), Response::HTTP_CREATED);
    }

    public function careProfile(
        Request $request,
        Student $student,
        EnrollmentAuditService $auditService,
    ): JsonResponse {
        $teacher = $request->user()->teacher;

        if (! $teacher || ! $student->enrollments()
            ->where('status', EnrollmentStatus::Active)
            ->whereHas('course.teachers', fn ($query) => $query->whereKey($teacher->id))
            ->exists()) {
            return response()->json(['message' => 'No tienes acceso a la ficha de cuidado.'], Response::HTTP_FORBIDDEN);
        }

        $auditService->recordAccess($student, $request->user(), 'teacher_care_profile', null, $request->ip());

        $student->load([
            'profile', 'medicalConditions', 'allergies', 'medications', 'healthInsurance', 'emergencyContacts',
        ]);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'preferred_name' => $student->profile?->preferred_name,
            ],
            'health' => $student->profile?->only([
                'blood_type', 'pediatrician_name', 'pediatrician_phone', 'developmental_notes',
                'care_instructions', 'medical_observations',
            ]),
            'medical_conditions' => $student->medicalConditions->map->only(['name', 'details', 'care_instructions']),
            'allergies' => $student->allergies->map->only(['allergen', 'severity', 'reaction', 'response_instructions']),
            'medications' => $student->medications->map->only(['name', 'dose', 'schedule', 'instructions']),
            'health_insurance' => $student->healthInsurance?->only([
                'has_insurance', 'provider', 'policy_number', 'plan_name', 'policy_holder', 'emergency_phone', 'expires_on',
            ]),
            'emergency_contacts' => $student->emergencyContacts->map->only([
                'position', 'full_name', 'relationship', 'phone', 'alternate_phone', 'address', 'authorized_pickup',
            ]),
        ]);
    }
}
