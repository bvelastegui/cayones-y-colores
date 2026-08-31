<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LevelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $levels = Level::with('courses')->paginate($request->integer('per_page', 15));

        return response()->json($levels);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:levels'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'student_aux_ratio' => ['required', 'integer', 'min:0'],
            'enrollment_fee' => ['required', 'numeric', 'min:0'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $level = Level::create($data);

        return response()->json($level, Response::HTTP_CREATED);
    }

    public function show(Level $level): JsonResponse
    {
        return response()->json($level->load(['courses', 'admissions']));
    }

    public function update(Request $request, Level $level): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100', 'unique:levels,name,'.$level->id],
            'max_capacity' => ['sometimes', 'required', 'integer', 'min:1'],
            'student_aux_ratio' => ['sometimes', 'required', 'integer', 'min:0'],
            'enrollment_fee' => ['sometimes', 'required', 'numeric', 'min:0'],
            'monthly_fee' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);

        $level->update($data);

        return response()->json($level);
    }

    public function destroy(Level $level): JsonResponse
    {
        $level->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
