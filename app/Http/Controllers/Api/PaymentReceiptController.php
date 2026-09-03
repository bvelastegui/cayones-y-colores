<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PaymentReceiptController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        Payment $payment,
        PaymentReceiptService $paymentReceiptService,
    ): Response {
        $payment->loadMissing('tuition.student');
        Gate::forUser($request->user())->authorize('manage', $payment->tuition->student);

        return $paymentReceiptService->download($payment, $request->user()->representative);
    }
}
