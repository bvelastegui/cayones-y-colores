<?php

namespace App\Data\Payments;

final readonly class GatewayPaymentResult
{
    public function __construct(
        public string $paymentId,
        public string $paymentUrl,
    ) {}
}
