<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentEmergencyContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentEmergencyContact>
 */
class StudentEmergencyContactFactory extends Factory
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
            'position' => 1,
            'full_name' => fake()->name(),
            'relationship' => 'familiar',
            'phone' => fake()->phoneNumber(),
            'authorized_pickup' => true,
        ];
    }
}
