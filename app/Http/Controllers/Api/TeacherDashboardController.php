<?php

namespace App\Http\Controllers\Api;

use App\Actions\Teachers\CreateAcademicReportAction;
use App\Actions\Teachers\ListCourseStudentsAction;
use App\Actions\Teachers\ListTeacherCoursesAction;
use App\Actions\Teachers\ListTeacherReportsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTeacherReportRequest;
use App\Models\Course;
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
}
