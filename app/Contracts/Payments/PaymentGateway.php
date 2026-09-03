<?php

namespace App\Contracts\Payments;

use App\Data\Payments\GatewayPaymentResult;
use App\Enums\PaymentMethod;
use App\Models\Tuition;

interface PaymentGateway
{
    public function method(): PaymentMethod;

    public function prepare(Tuition $tuition, string $clientTransactionId, int $amountInCents): GatewayPaymentResult;
}
