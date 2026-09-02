<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StudentRequest;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $students = Student::with(['representative', 'enrollments.course'])->paginate($request->integer('per_page', 15));

        return response()->json($students);
    }

    public function store(StudentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $student = Student::create($data);

        return response()->json($student->load('representative'), Response::HTTP_CREATED);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json($student->load(['representative', 'enrollments.course', 'tuitions.payments', 'academicReports']));
    }

    public function update(StudentRequest $request, Student $student): JsonResponse
    {
        $data = $request->validated();

        $student->update($data);

        return response()->json($student->load('representative'));
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
