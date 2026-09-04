<?php

namespace App\Services;

use App\Contracts\Payments\PaymentGateway;
use App\Data\Payments\GatewayPaymentConfirmation;
use App\Data\Payments\GatewayPaymentResult;
use App\Enums\PaymentMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LogicException;
use UnexpectedValueException;

class PayphonePaymentGateway implements PaymentGateway
{
    public function method(): PaymentMethod
    {
        return PaymentMethod::Payphone;
    }

    public function prepare(string $reference, string $clientTransactionId, int $amountInCents): GatewayPaymentResult
    {
        $response = $this->request()->post('/Prepare', [
            'amount' => $amountInCents,
            'amountWithoutTax' => $amountInCents,
            'amountWithTax' => 0,
            'tax' => 0,
            'service' => 0,
            'tip' => 0,
            'clientTransactionId' => $clientTransactionId,
            'reference' => $reference,
            'storeId' => $this->configuration('store_id'),
            'currency' => $this->configuration('currency'),
            'responseUrl' => $this->configuration('confirm_url'),
            'cancellationUrl' => url('/parent/payments?payment=cancelled'),
            'timeZone' => (int) $this->configuration('time_zone'),
        ])->throw();

        return new GatewayPaymentResult(
            paymentId: $this->requiredString($response, 'paymentId'),
            paymentUrl: $this->requiredString($response, 'payWithCard'),
        );
    }

    public function confirm(int $transactionId, string $clientTransactionId): GatewayPaymentConfirmation
    {
        $response = $this->request()->post('/V2/Confirm', [
            'id' => $transactionId,
            'clientTxId' => $clientTransactionId,
        ])->throw();

        return new GatewayPaymentConfirmation(
            transactionId: $this->requiredInteger($response, 'transactionId'),
            clientTransactionId: $this->requiredString($response, 'clientTransactionId'),
            amountInCents: $this->requiredInteger($response, 'amount'),
            currency: $this->requiredString($response, 'currency'),
            statusCode: $this->requiredInteger($response, 'statusCode'),
            transactionStatus: $this->requiredString($response, 'transactionStatus'),
        );
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->configuration('api_url'))
            ->acceptJson()
            ->asJson()
            ->withToken($this->configuration('token'))
            ->connectTimeout(5)
            ->timeout(15);
    }

    private function configuration(string $key): string
    {
        $value = config("services.payphone.{$key}");

        if ($value === null || $value === '') {
            throw new LogicException("PayPhone configuration [{$key}] is missing.");
        }

        return (string) $value;
    }

    private function requiredString(Response $response, string $key): string
    {
        $value = $response->json($key);

        if (! is_string($value) || $value === '') {
            throw new UnexpectedValueException("PayPhone response [{$key}] must be a non-empty string.");
        }

        return $value;
    }

    private function requiredInteger(Response $response, string $key): int
    {
        $value = $response->json($key);

        if (! is_int($value)) {
            throw new UnexpectedValueException("PayPhone response [{$key}] must be an integer.");
        }

        return $value;
    }
}
