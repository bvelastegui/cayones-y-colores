<?php

namespace Database\Factories;

use App\Enums\AcademicPeriodStatus;
use App\Models\AcademicPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicPeriod>
 */
class AcademicPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->numerify('Periodo ####'),
            'starts_on' => now()->addMonth()->startOfMonth()->toDateString(),
            'ends_on' => now()->addMonths(11)->endOfMonth()->toDateString(),
            'enrollment_opens_at' => now()->subWeek(),
            'enrollment_closes_at' => now()->addWeek(),
            'status' => AcademicPeriodStatus::Open,
        ];
    }
}
