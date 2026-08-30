<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(): JsonResponse
    {
        $payments = Payment::with('tuition.student')->paginate(15);

        return response()->json($payments);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tuition_id' => ['required', 'exists:tuitions,id'],
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $payment = Payment::create($data);

        return response()->json($payment->load('tuition'), Response::HTTP_CREATED);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load('tuition.student'));
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $data = $request->validate([
            'tuition_id' => ['sometimes', 'required', 'exists:tuitions,id'],
            'payment_method' => ['sometimes', 'required', 'string', Rule::enum(PaymentMethod::class)],
            'amount_paid' => ['sometimes', 'required', 'numeric', 'min:0'],
            'payment_date' => ['sometimes', 'required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $payment->update($data);

        return response()->json($payment->load('tuition'));
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
