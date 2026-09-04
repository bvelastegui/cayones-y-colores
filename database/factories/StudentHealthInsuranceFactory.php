<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentHealthInsurance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentHealthInsurance>
 */
class StudentHealthInsuranceFactory extends Factory
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
            'has_insurance' => false,
        ];
    }
}
