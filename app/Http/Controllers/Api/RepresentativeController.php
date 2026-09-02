<?php

namespace App\Http\Controllers\Api;

use App\Actions\Representatives\CreateRepresentativeAction;
use App\Actions\Representatives\UpdateRepresentativeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RepresentativeRequest;
use App\Models\Representative;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RepresentativeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $representatives = Representative::with('students')->paginate($request->integer('per_page', 15));

        return response()->json($representatives);
    }

    public function store(RepresentativeRequest $request, CreateRepresentativeAction $createRepresentative): JsonResponse
    {
        $data = $request->validated();

        $representative = $createRepresentative->execute($data);

        return response()->json($representative->load('user'), Response::HTTP_CREATED);
    }

    public function show(Representative $representative): JsonResponse
    {
        return response()->json($representative->load('students'));
    }

    public function update(
        RepresentativeRequest $request,
        Representative $representative,
        UpdateRepresentativeAction $updateRepresentative,
    ): JsonResponse {
        $data = $request->validated();

        $representative = $updateRepresentative->execute($representative, $data);

        return response()->json($representative->load('user'));
    }

    public function destroy(Representative $representative): JsonResponse
    {
        $representative->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
