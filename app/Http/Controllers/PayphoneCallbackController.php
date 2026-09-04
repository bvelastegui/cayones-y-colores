<?php

namespace App\Http\Controllers;

use App\Actions\Payments\ConfirmPayphonePaymentAction;
use App\Http\Requests\PayphoneConfirmationRequest;
use App\Models\PayphonePaymentAttempt;
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
        $clientTransactionId = $request->string('clientTransactionId')->toString();

        try {
            $approved = $confirmPayment->execute(
                $request->integer('id'),
                $clientTransactionId,
            );

            return redirect($this->returnPath($clientTransactionId, $approved ? 'approved' : 'cancelled'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect($this->returnPath($clientTransactionId, 'error'));
        }
    }

    private function returnPath(string $clientTransactionId, string $result): string
    {
        $attempt = PayphonePaymentAttempt::query()
            ->where('client_transaction_id', $clientTransactionId)
            ->with('items.tuition:id,student_id,enrollment_id')
            ->first();
        $enrollmentTuition = $attempt?->items->first()?->tuition;

        if ($enrollmentTuition?->enrollment_id !== null) {
            return "/parent/enroll/{$enrollmentTuition->student_id}?payment={$result}";
        }

        return "/parent/payments?payment={$result}";
    }
}
