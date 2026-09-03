<?php

namespace Database\Factories;

use App\Models\PayphonePaymentAttempt;
use App\Models\PayphonePaymentAttemptItem;
use App\Models\Tuition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayphonePaymentAttemptItem>
 */
class PayphonePaymentAttemptItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payphone_payment_attempt_id' => PayphonePaymentAttempt::factory(),
            'tuition_id' => Tuition::factory(),
            'amount_in_cents' => fake()->numberBetween(100, 50_000),
        ];
    }
}
