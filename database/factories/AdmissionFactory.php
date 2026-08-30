<?php

namespace Database\Factories;

use App\Enums\AdmissionStatus;
use App\Models\Admission;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admission>
 */
class AdmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'applicant_first_name' => fake()->firstName(),
            'applicant_last_name' => fake()->lastName(),
            'applicant_birth_date' => fake()->dateTimeBetween('-6 years', '-2 years')->format('Y-m-d'),
            'representative_names' => fake()->name(),
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(AdmissionStatus::cases())->value,
            'application_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
        ];
    }
}
