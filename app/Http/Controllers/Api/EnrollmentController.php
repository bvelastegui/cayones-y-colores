<?php

namespace App\Http\Controllers\Api;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function index(): JsonResponse
    {
        $enrollments = Enrollment::with(['student', 'course'])->paginate(15);

        return response()->json($enrollments);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'enrollment_date' => ['required', 'date'],
            'status' => ['sometimes', 'required', 'string', Rule::enum(EnrollmentStatus::class)],
        ]);

        $enrollment = Enrollment::create($data);

        return response()->json($enrollment->load(['student', 'course']), Response::HTTP_CREATED);
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        return response()->json($enrollment->load(['student', 'course']));
    }

    public function update(Request $request, Enrollment $enrollment): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['sometimes', 'required', 'exists:students,id'],
            'course_id' => ['sometimes', 'required', 'exists:courses,id'],
            'enrollment_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'string', Rule::enum(EnrollmentStatus::class)],
        ]);

        $enrollment->update($data);

        return response()->json($enrollment->load(['student', 'course']));
    }

    public function destroy(Enrollment $enrollment): JsonResponse
    {
        $enrollment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
