<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Enums\TuitionStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Tuition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with('tuition.student')->paginate($request->integer('per_page', 15));

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
        $this->syncTuitionStatus($payment->tuition);

        return response()->json($payment->fresh()->load('tuition.student'), Response::HTTP_CREATED);
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
        $this->syncTuitionStatus($payment->tuition);

        return response()->json($payment->fresh()->load('tuition.student'));
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $tuition = $payment->tuition;
        $payment->delete();
        $this->syncTuitionStatus($tuition);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function syncTuitionStatus(Tuition $tuition): void
    {
        $paid = (float) $tuition->payments()->sum('amount_paid');
        $balance = (float) $tuition->amount - $paid;

        if ($balance <= 0) {
            $tuition->update(['status' => TuitionStatus::Paid]);
        } elseif ($paid > 0) {
            $tuition->update(['status' => TuitionStatus::Partial]);
        } else {
            $tuition->update(['status' => TuitionStatus::Pending]);
        }
    }
}
