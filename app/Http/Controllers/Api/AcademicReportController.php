<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AcademicReportRequest;
use App\Models\AcademicReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AcademicReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reports = AcademicReport::with(['student', 'teacher'])->paginate($request->integer('per_page', 15));

        return response()->json($reports);
    }

    public function store(AcademicReportRequest $request): JsonResponse
    {
        $data = $request->validated();

        $report = AcademicReport::create($data);

        return response()->json($report->load(['student', 'teacher']), Response::HTTP_CREATED);
    }

    public function show(AcademicReport $academicReport): JsonResponse
    {
        return response()->json($academicReport->load(['student', 'teacher']));
    }

    public function update(AcademicReportRequest $request, AcademicReport $academicReport): JsonResponse
    {
        $data = $request->validated();

        $academicReport->update($data);

        return response()->json($academicReport->load(['student', 'teacher']));
    }

    public function destroy(AcademicReport $academicReport): JsonResponse
    {
        $academicReport->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
