<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        $students = Student::with(['representative', 'enrollments.course'])->paginate(15);

        return response()->json($students);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'representative_id' => ['required', 'exists:representatives,id'],
            'id_card' => ['required', 'string', 'max:50', 'unique:students'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
        ]);

        $student = Student::create($data);

        return response()->json($student->load('representative'), Response::HTTP_CREATED);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json($student->load(['representative', 'enrollments.course', 'tuitions.payments', 'academicReports']));
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $data = $request->validate([
            'representative_id' => ['sometimes', 'required', 'exists:representatives,id'],
            'id_card' => ['sometimes', 'required', 'string', 'max:50', 'unique:students,id_card,'.$student->id],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'birth_date' => ['sometimes', 'required', 'date'],
        ]);

        $student->update($data);

        return response()->json($student->load('representative'));
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
