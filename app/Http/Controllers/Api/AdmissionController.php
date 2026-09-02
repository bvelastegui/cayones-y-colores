<?php

namespace App\Http\Controllers\Api;

use App\Enums\AdmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdmissionRequest;
use App\Models\Admission;
use App\Services\AdmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdmissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $admissions = Admission::with(['level', 'representative', 'student'])->paginate($request->integer('per_page', 15));

        return response()->json($admissions);
    }

    public function store(AdmissionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['status'] = AdmissionStatus::Pending->value;

        $admission = Admission::create($data);

        return response()->json($admission->load('level'), Response::HTTP_CREATED);
    }

    public function show(Admission $admission): JsonResponse
    {
        return response()->json($admission->load(['level', 'representative', 'student']));
    }

    public function update(AdmissionRequest $request, Admission $admission): JsonResponse
    {
        $data = $request->validated();

        $admission->update($data);

        return response()->json($admission->load(['level', 'representative', 'student']));
    }

    public function destroy(Admission $admission): JsonResponse
    {
        $admission->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function approve(Admission $admission, AdmissionService $admissionService): JsonResponse
    {
        return response()->json($admissionService->approve($admission));
    }

    public function reject(Admission $admission, AdmissionService $admissionService): JsonResponse
    {
        return response()->json($admissionService->reject($admission));
    }
}
