<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
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
            'preferred_name' => fake()->firstName(),
            'nationality' => 'Ecuatoriana',
        ];
    }
}
