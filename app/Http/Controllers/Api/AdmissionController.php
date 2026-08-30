<?php

namespace App\Http\Controllers\Api;

use App\Enums\AdmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AdmissionController extends Controller
{
    public function index(): JsonResponse
    {
        $admissions = Admission::with('level')->paginate(15);

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
            'status' => ['sometimes', 'required', 'string', Rule::enum(AdmissionStatus::class)],
            'application_date' => ['required', 'date'],
        ]);

        $admission = Admission::create($data);

        return response()->json($admission->load('level'), Response::HTTP_CREATED);
    }

    public function show(Admission $admission): JsonResponse
    {
        return response()->json($admission->load('level'));
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
            'status' => ['sometimes', 'required', 'string', Rule::enum(AdmissionStatus::class)],
            'application_date' => ['sometimes', 'required', 'date'],
        ]);

        $admission->update($data);

        return response()->json($admission->load('level'));
    }

    public function destroy(Admission $admission): JsonResponse
    {
        $admission->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
