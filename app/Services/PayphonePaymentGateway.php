<?php

namespace App\Services;

use App\Contracts\Payments\PaymentGateway;
use App\Data\Payments\GatewayPaymentResult;
use App\Enums\PaymentMethod;
use App\Models\Tuition;
use Illuminate\Support\Str;

class PayphonePaymentGateway implements PaymentGateway
{
    public function method(): PaymentMethod
    {
        return PaymentMethod::Payphone;
    }

    public function pay(Tuition $tuition): GatewayPaymentResult
    {
        return new GatewayPaymentResult(
            referenceNumber: 'PAYPHONE-'.Str::random(8),
        );
    }
}
