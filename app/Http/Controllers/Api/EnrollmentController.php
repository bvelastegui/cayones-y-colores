<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EnrollmentRequest;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $enrollments = Enrollment::with(['student', 'course'])->paginate($request->integer('per_page', 15));

        return response()->json($enrollments);
    }

    public function store(EnrollmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $enrollment = Enrollment::create($data);

        return response()->json($enrollment->load(['student', 'course']), Response::HTTP_CREATED);
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        return response()->json($enrollment->load(['student', 'course']));
    }

    public function update(EnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        $data = $request->validated();

        $enrollment->update($data);

        return response()->json($enrollment->load(['student', 'course']));
    }

    public function destroy(Enrollment $enrollment): JsonResponse
    {
        $enrollment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
