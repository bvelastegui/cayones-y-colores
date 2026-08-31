<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssignedRole;
use App\Http\Controllers\Controller;
use App\Models\CourseTeacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CourseTeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assignments = CourseTeacher::with(['course', 'teacher'])->paginate($request->integer('per_page', 15));

        return response()->json($assignments);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'assigned_role' => ['required', 'string', Rule::enum(AssignedRole::class)],
        ]);

        $assignment = CourseTeacher::create($data);

        return response()->json($assignment->load(['course', 'teacher']), Response::HTTP_CREATED);
    }

    public function show(CourseTeacher $courseTeacher): JsonResponse
    {
        return response()->json($courseTeacher->load(['course', 'teacher']));
    }

    public function update(Request $request, CourseTeacher $courseTeacher): JsonResponse
    {
        $data = $request->validate([
            'course_id' => ['sometimes', 'required', 'exists:courses,id'],
            'teacher_id' => ['sometimes', 'required', 'exists:teachers,id'],
            'assigned_role' => ['sometimes', 'required', 'string', Rule::enum(AssignedRole::class)],
        ]);

        $courseTeacher->update($data);

        return response()->json($courseTeacher->load(['course', 'teacher']));
    }

    public function destroy(CourseTeacher $courseTeacher): JsonResponse
    {
        $courseTeacher->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
