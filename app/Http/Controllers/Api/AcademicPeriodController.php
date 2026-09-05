<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AcademicPeriodRequest;
use App\Http\Requests\Api\RunEnrollmentAssignmentRequest;
use App\Models\AcademicPeriod;
use App\Services\AcademicPeriodService;
use App\Services\EnrollmentAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AcademicPeriodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            AcademicPeriod::query()->withCount('enrollments')->latest('starts_on')
                ->paginate($request->integer('per_page', 15)),
        );
    }

    public function store(AcademicPeriodRequest $request, AcademicPeriodService $academicPeriodService): JsonResponse
    {
        $academicPeriod = $academicPeriodService->create($request->validated());

        return response()->json($academicPeriod, Response::HTTP_CREATED);
    }

    public function show(AcademicPeriod $academicPeriod): JsonResponse
    {
        return response()->json($academicPeriod->loadCount('enrollments'));
    }

    public function update(
        AcademicPeriodRequest $request,
        AcademicPeriod $academicPeriod,
        AcademicPeriodService $academicPeriodService,
    ): JsonResponse {
        return response()->json($academicPeriodService->update($academicPeriod, $request->validated()));
    }

    public function destroy(AcademicPeriod $academicPeriod): JsonResponse
    {
        if ($academicPeriod->enrollments()->exists()) {
            throw ValidationException::withMessages([
                'period' => ['No se puede eliminar un periodo que ya tiene matrículas.'],
            ]);
        }

        $academicPeriod->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function runAssignments(
        RunEnrollmentAssignmentRequest $request,
        AcademicPeriod $academicPeriod,
        EnrollmentAllocationService $allocationService,
    ): JsonResponse {
        return response()->json($allocationService->allocatePeriod(
            $academicPeriod,
            $request->user(),
            $request->boolean('force'),
            $request->validated('reason'),
            $request->ip(),
        ));
    }
}
