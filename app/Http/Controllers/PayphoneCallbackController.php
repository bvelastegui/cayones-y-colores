<?php

namespace App\Http\Controllers;

use App\Actions\Payments\ConfirmPayphonePaymentAction;
use App\Http\Requests\PayphoneConfirmationRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PayphoneCallbackController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        PayphoneConfirmationRequest $request,
        ConfirmPayphonePaymentAction $confirmPayment,
    ): RedirectResponse {
        try {
            $approved = $confirmPayment->execute(
                $request->integer('id'),
                $request->string('clientTransactionId')->toString(),
            );

            return redirect('/parent/payments?payment='.($approved ? 'approved' : 'cancelled'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect('/parent/payments?payment=error');
        }
    }
}
