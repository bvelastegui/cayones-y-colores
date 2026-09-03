<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\Representative;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PaymentReceiptService
{
    public function download(Payment $payment, Representative $representative): Response
    {
        $payments = $this->receiptPayments($payment, $representative);
        $totalInCents = $payments->sum(
            fn (Payment $receiptPayment): int => (int) round((float) $receiptPayment->amount_paid * 100),
        );
        $reference = $payment->reference_number ?? 'PAGO-'.$payment->id;
        $filenameReference = Str::slug($reference) ?: (string) $payment->id;

        return Pdf::loadView('pdf.payment-receipt', [
            'payments' => $payments,
            'reference' => $reference,
            'total' => sprintf('%d.%02d', intdiv($totalInCents, 100), $totalInCents % 100),
        ])->setPaper('a4')->download("comprobante-{$filenameReference}.pdf");
    }

    /** @return Collection<int, Payment> */
    private function receiptPayments(Payment $payment, Representative $representative): Collection
    {
        if ($payment->payment_method !== PaymentMethod::Payphone || $payment->reference_number === null) {
            return new Collection([$payment->loadMissing('tuition.student')]);
        }

        return Payment::query()
            ->with('tuition.student')
            ->where('payment_method', PaymentMethod::Payphone)
            ->where('reference_number', $payment->reference_number)
            ->whereHas(
                'tuition.student',
                fn ($query) => $query->where('representative_id', $representative->id),
            )
            ->orderBy('id')
            ->get();
    }
}
