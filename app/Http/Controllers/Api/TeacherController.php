<?php

namespace App\Http\Controllers\Api;

use App\Actions\Teachers\CreateTeacherAction;
use App\Actions\Teachers\UpdateTeacherAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teachers = Teacher::with('courses')->paginate($request->integer('per_page', 15));

        return response()->json($teachers);
    }

    public function store(TeacherRequest $request, CreateTeacherAction $createTeacher): JsonResponse
    {
        $data = $request->validated();

        $teacher = $createTeacher->execute($data);

        return response()->json($teacher->load('user'), Response::HTTP_CREATED);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json($teacher->load(['courses', 'academicReports']));
    }

    public function update(TeacherRequest $request, Teacher $teacher, UpdateTeacherAction $updateTeacher): JsonResponse
    {
        $data = $request->validated();

        $teacher = $updateTeacher->execute($teacher, $data);

        return response()->json($teacher->load('user'));
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
