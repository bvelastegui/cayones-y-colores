<?php

namespace Database\Factories;

use App\Models\Representative;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'representative_id' => Representative::factory(),
            'id_card' => fake()->unique()->numerify('175#######'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => fake()->dateTimeBetween('-7 years', '-2 years')->format('Y-m-d'),
        ];
    }
}
