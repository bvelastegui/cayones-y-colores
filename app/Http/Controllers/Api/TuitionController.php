<?php

namespace App\Http\Controllers\Api;

use App\Enums\TuitionStatus;
use App\Http\Controllers\Controller;
use App\Models\Tuition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TuitionController extends Controller
{
    public function index(): JsonResponse
    {
        $tuitions = Tuition::with(['student', 'payments'])->paginate(15);

        return response()->json($tuitions);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'generation_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'status' => ['sometimes', 'required', 'string', Rule::enum(TuitionStatus::class)],
        ]);

        $tuition = Tuition::create($data);

        return response()->json($tuition->load(['student', 'payments']), Response::HTTP_CREATED);
    }

    public function show(Tuition $tuition): JsonResponse
    {
        return response()->json($tuition->load(['student', 'payments']));
    }

    public function update(Request $request, Tuition $tuition): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['sometimes', 'required', 'exists:students,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'generation_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'string', Rule::enum(TuitionStatus::class)],
        ]);

        $tuition->update($data);

        return response()->json($tuition->load(['student', 'payments']));
    }

    public function destroy(Tuition $tuition): JsonResponse
    {
        $tuition->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
