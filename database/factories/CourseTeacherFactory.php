<?php

namespace Database\Factories;

use App\Enums\AssignedRole;
use App\Models\Course;
use App\Models\CourseTeacher;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseTeacher>
 */
class CourseTeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'teacher_id' => Teacher::factory(),
            'assigned_role' => fake()->randomElement(AssignedRole::cases())->value,
        ];
    }
}
