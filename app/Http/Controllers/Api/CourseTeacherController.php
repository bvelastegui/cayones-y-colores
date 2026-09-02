<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CourseTeacherRequest;
use App\Models\CourseTeacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseTeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assignments = CourseTeacher::with(['course', 'teacher'])->paginate($request->integer('per_page', 15));

        return response()->json($assignments);
    }

    public function store(CourseTeacherRequest $request): JsonResponse
    {
        $data = $request->validated();

        $assignment = CourseTeacher::create($data);

        return response()->json($assignment->load(['course', 'teacher']), Response::HTTP_CREATED);
    }

    public function show(CourseTeacher $courseTeacher): JsonResponse
    {
        return response()->json($courseTeacher->load(['course', 'teacher']));
    }

    public function update(CourseTeacherRequest $request, CourseTeacher $courseTeacher): JsonResponse
    {
        $data = $request->validated();

        $courseTeacher->update($data);

        return response()->json($courseTeacher->load(['course', 'teacher']));
    }

    public function destroy(CourseTeacher $courseTeacher): JsonResponse
    {
        $courseTeacher->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
