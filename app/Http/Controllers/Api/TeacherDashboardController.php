<?php

namespace App\Http\Controllers\Api;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicReport;
use App\Models\Course;
use App\Models\CourseTeacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeacherDashboardController extends Controller
{
    public function courses(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json(['message' => 'Perfil de docente no encontrado.'], Response::HTTP_FORBIDDEN);
        }

        $courses = $teacher->courses()
            ->with(['level'])
            ->withCount([
                'enrollments as active_students_count' => function ($query): void {
                    $query->where('status', EnrollmentStatus::Active);
                },
            ])
            ->get();

        return response()->json($courses);
    }

    public function students(Request $request, Course $course): JsonResponse
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json(['message' => 'Perfil de docente no encontrado.'], Response::HTTP_FORBIDDEN);
        }

        $hasCourse = CourseTeacher::where('teacher_id', $teacher->id)
            ->where('course_id', $course->id)
            ->exists();

        if (! $hasCourse) {
            return response()->json(['message' => 'No tienes asignado este curso.'], Response::HTTP_FORBIDDEN);
        }

        $students = $course->students()
            ->wherePivot('status', EnrollmentStatus::Active)
            ->get();

        return response()->json($students);
    }

    public function reports(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json(['message' => 'Perfil de docente no encontrado.'], Response::HTTP_FORBIDDEN);
        }

        $reports = AcademicReport::with('student')
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($reports);
    }

    public function storeReport(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return response()->json(['message' => 'Perfil de docente no encontrado.'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'development_area' => ['required', 'string', 'max:100'],
            'evaluated_skill' => ['required', 'string', 'max:100'],
            'achievement_level' => ['required', 'string', 'in:A,EP,I,NE'],
            'observations' => ['nullable', 'string'],
        ]);

        $hasCourse = CourseTeacher::where('teacher_id', $teacher->id)
            ->where('course_id', $data['course_id'])
            ->exists();

        if (! $hasCourse) {
            return response()->json(['message' => 'No tienes asignado este curso.'], Response::HTTP_FORBIDDEN);
        }

        $report = AcademicReport::create([
            'student_id' => $data['student_id'],
            'teacher_id' => $teacher->id,
            'development_area' => $data['development_area'],
            'evaluated_skill' => $data['evaluated_skill'],
            'achievement_level' => $data['achievement_level'],
            'observations' => $data['observations'] ?? null,
        ]);

        return response()->json($report->load('student'), Response::HTTP_CREATED);
    }
}
