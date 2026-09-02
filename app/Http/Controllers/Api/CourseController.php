<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CourseRequest;
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

    public function store(CourseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $course = Course::create($data);

        return response()->json($course->load(['level', 'teachers']), Response::HTTP_CREATED);
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json($course->load(['level', 'teachers', 'enrollments.student', 'students']));
    }

    public function update(CourseRequest $request, Course $course): JsonResponse
    {
        $data = $request->validated();

        $course->update($data);

        return response()->json($course->load(['level', 'teachers']));
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
