<?php

namespace App\Data\Payments;

final readonly class GatewayPaymentConfirmation
{
    public function __construct(
        public int $transactionId,
        public string $clientTransactionId,
        public int $amountInCents,
        public string $currency,
        public int $statusCode,
        public string $transactionStatus,
    ) {}

    public function isApproved(): bool
    {
        return $this->statusCode === 3 && $this->transactionStatus === 'Approved';
    }
}
