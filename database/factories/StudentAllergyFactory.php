<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentAllergy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAllergy>
 */
class StudentAllergyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'allergen' => fake()->word(),
            'severity' => fake()->randomElement(['mild', 'moderate', 'severe']),
        ];
    }
}
