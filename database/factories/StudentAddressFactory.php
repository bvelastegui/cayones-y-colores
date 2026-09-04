<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAddress>
 */
class StudentAddressFactory extends Factory
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
            'country' => 'Ecuador',
            'province' => fake()->city(),
            'city' => fake()->city(),
            'main_street' => fake()->streetName(),
        ];
    }
}
