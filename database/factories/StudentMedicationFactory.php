<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentMedication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentMedication>
 */
class StudentMedicationFactory extends Factory
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
            'name' => fake()->word(),
            'dose' => fake()->numerify('## mg'),
        ];
    }
}
