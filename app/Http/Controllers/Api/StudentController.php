<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StudentRequest;
use App\Models\Student;
use App\Services\EnrollmentAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $students = Student::with(['representative', 'level', 'enrollments.course'])->paginate($request->integer('per_page', 15));

        return response()->json($students);
    }

    public function store(StudentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $student = Student::create($data);

        return response()->json($student->load(['representative', 'level']), Response::HTTP_CREATED);
    }

    public function show(Request $request, Student $student, EnrollmentAuditService $auditService): JsonResponse
    {
        $auditService->recordAccess($student, $request->user(), 'admin_full_profile', null, $request->ip());

        return response()->json($student->load([
            'representative', 'level', 'profile', 'residence', 'legalRepresentative', 'billingProfile',
            'healthInsurance', 'emergencyContacts', 'medicalConditions', 'allergies', 'medications',
            'enrollments.course', 'enrollments.form', 'tuitions.payments', 'academicReports',
        ]));
    }

    public function update(StudentRequest $request, Student $student): JsonResponse
    {
        $data = $request->validated();

        $student->update($data);

        return response()->json($student->load('representative'));
    }

    public function destroy(Student $student): JsonResponse
    {
        if ($student->enrollments()->exists()) {
            throw ValidationException::withMessages([
                'student' => ['No se puede eliminar un estudiante con historial de matrículas.'],
            ]);
        }

        $student->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
