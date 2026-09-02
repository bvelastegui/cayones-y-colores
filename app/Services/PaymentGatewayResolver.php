<?php

namespace App\Services;

use App\Contracts\Payments\PaymentGateway;
use App\Enums\PaymentMethod;
use Illuminate\Contracts\Container\BindingResolutionException;

class PaymentGatewayResolver
{
    /**
     * @param  iterable<PaymentGateway>  $gateways
     */
    public function __construct(private iterable $gateways) {}

    public function resolve(PaymentMethod $method): PaymentGateway
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->method() === $method) {
                return $gateway;
            }
        }

        throw new BindingResolutionException("No payment gateway is registered for {$method->value}.");
    }
}
