<?php

namespace Database\Factories;

use App\Enums\TuitionStatus;
use App\Models\Student;
use App\Models\Tuition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tuition>
 */
class TuitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $generationDate = fake()->dateTimeBetween('-3 months', '+1 month');
        $dueDate = clone $generationDate;
        $dueDate->setDate((int) $generationDate->format('Y'), (int) $generationDate->format('m'), 10);

        return [
            'student_id' => Student::factory(),
            'amount' => fake()->randomFloat(2, 100, 300),
            'generation_date' => $generationDate->format('Y-m-d'),
            'billing_period' => $generationDate->format('Y-m-01'),
            'due_date' => $dueDate->format('Y-m-d'),
            'status' => fake()->randomElement(TuitionStatus::cases())->value,
        ];
    }
}
