<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\EnrollmentAudit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrollmentAudit>
 */
class EnrollmentAuditFactory extends Factory
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
            'event' => 'test_event',
            'metadata' => [],
        ];
    }
}
