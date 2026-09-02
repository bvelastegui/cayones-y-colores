<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payments\CreatePaymentAction;
use App\Actions\Payments\DeletePaymentAction;
use App\Actions\Payments\UpdatePaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentRequest;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with('tuition.student')->paginate($request->integer('per_page', 15));

        return response()->json($payments);
    }

    public function store(PaymentRequest $request, CreatePaymentAction $createPayment): JsonResponse
    {
        $data = $request->validated();

        $payment = $createPayment->execute($data);

        return response()->json($payment->fresh()->load('tuition.student'), Response::HTTP_CREATED);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load('tuition.student'));
    }

    public function update(PaymentRequest $request, Payment $payment, UpdatePaymentAction $updatePayment): JsonResponse
    {
        $data = $request->validated();

        $payment = $updatePayment->execute($payment, $data);

        return response()->json($payment->fresh()->load('tuition.student'));
    }

    public function destroy(Payment $payment, DeletePaymentAction $deletePayment): JsonResponse
    {
        $deletePayment->execute($payment);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
