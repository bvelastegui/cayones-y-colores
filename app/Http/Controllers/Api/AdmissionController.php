<?php

namespace App\Http\Controllers\Api;

use App\Enums\AdmissionStatus;
use App\Http\Controllers\Controller;
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

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'applicant_first_name' => ['required', 'string', 'max:100'],
            'applicant_last_name' => ['required', 'string', 'max:100'],
            'applicant_birth_date' => ['required', 'date'],
            'representative_names' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'application_date' => ['required', 'date'],
        ]);

        $data['status'] = AdmissionStatus::Pending->value;

        $admission = Admission::create($data);

        return response()->json($admission->load('level'), Response::HTTP_CREATED);
    }

    public function show(Admission $admission): JsonResponse
    {
        return response()->json($admission->load(['level', 'representative', 'student']));
    }

    public function update(Request $request, Admission $admission): JsonResponse
    {
        $data = $request->validate([
            'level_id' => ['sometimes', 'required', 'exists:levels,id'],
            'applicant_first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'applicant_last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'applicant_birth_date' => ['sometimes', 'required', 'date'],
            'representative_names' => ['sometimes', 'required', 'string', 'max:255'],
            'contact_email' => ['sometimes', 'required', 'email', 'max:255'],
            'contact_phone' => ['sometimes', 'required', 'string', 'max:50'],
            'application_date' => ['sometimes', 'required', 'date'],
        ]);

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
