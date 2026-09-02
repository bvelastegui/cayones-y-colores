<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LevelRequest;
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

    public function store(LevelRequest $request): JsonResponse
    {
        $data = $request->validated();

        $level = Level::create($data);

        return response()->json($level, Response::HTTP_CREATED);
    }

    public function show(Level $level): JsonResponse
    {
        return response()->json($level->load(['courses', 'admissions']));
    }

    public function update(LevelRequest $request, Level $level): JsonResponse
    {
        $data = $request->validated();

        $level->update($data);

        return response()->json($level);
    }

    public function destroy(Level $level): JsonResponse
    {
        $level->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
