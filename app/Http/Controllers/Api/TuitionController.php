<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GenerateTuitionsRequest;
use App\Http\Requests\Api\TuitionRequest;
use App\Models\Tuition;
use App\Services\TuitionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TuitionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tuitions = Tuition::with(['student', 'payments'])->paginate($request->integer('per_page', 15));

        return response()->json($tuitions);
    }

    public function store(TuitionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $tuition = Tuition::create($data);

        return response()->json($tuition->load(['student', 'payments']), Response::HTTP_CREATED);
    }

    public function show(Tuition $tuition): JsonResponse
    {
        return response()->json($tuition->load(['student', 'payments']));
    }

    public function update(TuitionRequest $request, Tuition $tuition): JsonResponse
    {
        $data = $request->validated();

        $tuition->update($data);

        return response()->json($tuition->load(['student', 'payments']));
    }

    public function destroy(Tuition $tuition): JsonResponse
    {
        $tuition->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function generate(GenerateTuitionsRequest $request, TuitionService $tuitionService): JsonResponse
    {
        $data = $request->validated();

        $generationDate = isset($data['date']) ? Carbon::createFromFormat('Y-m-d', $data['date']) : Carbon::today();
        $created = $tuitionService->generateMonthlyTuitions($generationDate);

        return response()->json([
            'created' => $created,
            'date' => $generationDate->toDateString(),
        ]);
    }
}
