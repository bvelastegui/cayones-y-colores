<?php

namespace Database\Factories;

use App\Models\AcademicReport;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicReport>
 */
class AcademicReportFactory extends Factory
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
            'teacher_id' => Teacher::factory(),
            'development_area' => fake()->randomElement([
                'Desarrollo físico y motor',
                'Comunicación y lenguaje',
                'Relación con el entorno',
                'Desarrollo socioemocional',
                'Desarrollo cognitivo',
            ]),
            'evaluated_skill' => fake()->sentence(3),
            'achievement_level' => fake()->randomElement(['Inicial', 'En Proceso', 'Logrado', 'Avanzado']),
            'observations' => fake()->optional()->paragraph(),
        ];
    }
}
