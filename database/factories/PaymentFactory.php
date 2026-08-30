<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\Tuition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'payment_method' => fake()->randomElement(PaymentMethod::cases())->value,
            'amount_paid' => fake()->randomFloat(2, 50, 300),
            'payment_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'reference_number' => fake()->optional()->bothify('REF-####????'),
        ];
    }
}
