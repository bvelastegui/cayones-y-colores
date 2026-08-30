<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AcademicReportController extends Controller
{
    public function index(): JsonResponse
    {
        $reports = AcademicReport::with(['student', 'teacher'])->paginate(15);

        return response()->json($reports);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'development_area' => ['required', 'string', 'max:100'],
            'evaluated_skill' => ['required', 'string', 'max:100'],
            'achievement_level' => ['required', 'string', 'max:100'],
            'observations' => ['nullable', 'string'],
        ]);

        $report = AcademicReport::create($data);

        return response()->json($report->load(['student', 'teacher']), Response::HTTP_CREATED);
    }

    public function show(AcademicReport $academicReport): JsonResponse
    {
        return response()->json($academicReport->load(['student', 'teacher']));
    }

    public function update(Request $request, AcademicReport $academicReport): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['sometimes', 'required', 'exists:students,id'],
            'teacher_id' => ['sometimes', 'required', 'exists:teachers,id'],
            'development_area' => ['sometimes', 'required', 'string', 'max:100'],
            'evaluated_skill' => ['sometimes', 'required', 'string', 'max:100'],
            'achievement_level' => ['sometimes', 'required', 'string', 'max:100'],
            'observations' => ['nullable', 'string'],
        ]);

        $academicReport->update($data);

        return response()->json($academicReport->load(['student', 'teacher']));
    }

    public function destroy(AcademicReport $academicReport): JsonResponse
    {
        $academicReport->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
