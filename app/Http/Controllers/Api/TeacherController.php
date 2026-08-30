<?php

namespace App\Http\Controllers\Api;

use App\Enums\TeacherType;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(): JsonResponse
    {
        $teachers = Teacher::with('courses')->paginate(15);

        return response()->json($teachers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'teacher_type' => ['required', 'string', Rule::enum(TeacherType::class)],
        ]);

        $teacher = Teacher::create($data);

        return response()->json($teacher, Response::HTTP_CREATED);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json($teacher->load(['courses', 'academicReports']));
    }

    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'teacher_type' => ['sometimes', 'required', 'string', Rule::enum(TeacherType::class)],
        ]);

        $teacher->update($data);

        return response()->json($teacher);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
