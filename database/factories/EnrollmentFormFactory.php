<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\EnrollmentForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrollmentForm>
 */
class EnrollmentFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'current_step' => 'student',
            'draft_data' => [],
        ];
    }
}
