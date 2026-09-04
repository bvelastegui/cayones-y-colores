<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentLegalRepresentative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentLegalRepresentative>
 */
class StudentLegalRepresentativeFactory extends Factory
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
            'relationship' => 'madre',
            'id_type' => 'cedula',
            'id_number' => fake()->unique()->numerify('##########'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
        ];
    }
}
