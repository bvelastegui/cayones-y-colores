<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = Course::with(['level', 'teachers', 'enrollments.student'])->paginate($request->integer('per_page', 15));

        return response()->json($courses);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'parallel' => ['required', 'string', 'max:10'],
        ]);

        $course = Course::create($data);

        return response()->json($course->load(['level', 'teachers']), Response::HTTP_CREATED);
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json($course->load(['level', 'teachers', 'enrollments.student', 'students']));
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'level_id' => ['sometimes', 'required', 'exists:levels,id'],
            'parallel' => ['sometimes', 'required', 'string', 'max:10'],
        ]);

        $course->update($data);

        return response()->json($course->load(['level', 'teachers']));
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
