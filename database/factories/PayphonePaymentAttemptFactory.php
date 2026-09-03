<?php

namespace Database\Factories;

use App\Enums\PayphonePaymentStatus;
use App\Models\PayphonePaymentAttempt;
use App\Models\Tuition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayphonePaymentAttempt>
 */
class PayphonePaymentAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tuition_id' => Tuition::factory(),
            'client_transaction_id' => fake()->uuid(),
            'amount_in_cents' => fake()->numberBetween(100, 50_000),
            'status' => PayphonePaymentStatus::Prepared,
            'payphone_payment_id' => fake()->bothify('??????????????????'),
            'payment_url' => fake()->url(),
            'transaction_id' => null,
            'expires_at' => now()->addMinutes(10),
            'confirmed_at' => null,
        ];
    }
}
