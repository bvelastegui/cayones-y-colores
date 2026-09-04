<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentMedicalCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentMedicalCondition>
 */
class StudentMedicalConditionFactory extends Factory
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
            'name' => fake()->words(2, true),
            'details' => fake()->sentence(),
        ];
    }
}
