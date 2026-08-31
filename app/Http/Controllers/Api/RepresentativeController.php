<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_card' => ['required', 'string', 'max:50', 'unique:representatives'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:representatives'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $representative = Representative::create($data);

        return response()->json($representative, Response::HTTP_CREATED);
    }

    public function show(Representative $representative): JsonResponse
    {
        return response()->json($representative->load('students'));
    }

    public function update(Request $request, Representative $representative): JsonResponse
    {
        $data = $request->validate([
            'id_card' => ['sometimes', 'required', 'string', 'max:50', 'unique:representatives,id_card,'.$representative->id],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:representatives,email,'.$representative->id],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $representative->update($data);

        return response()->json($representative);
    }

    public function destroy(Representative $representative): JsonResponse
    {
        $representative->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
