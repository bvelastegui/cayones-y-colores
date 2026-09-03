<?php

namespace App\Contracts\Payments;

use App\Data\Payments\GatewayPaymentResult;
use App\Enums\PaymentMethod;

interface PaymentGateway
{
    public function method(): PaymentMethod;

    public function prepare(string $reference, string $clientTransactionId, int $amountInCents): GatewayPaymentResult;
}
