<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentBillingProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentBillingProfile>
 */
class StudentBillingProfileFactory extends Factory
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
            'tax_id_type' => 'cedula',
            'tax_id' => fake()->unique()->numerify('##########'),
            'business_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
        ];
    }
}
