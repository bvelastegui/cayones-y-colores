<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentRecordAccessLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentRecordAccessLog>
 */
class StudentRecordAccessLogFactory extends Factory
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
            'user_id' => User::factory(),
            'scope' => 'full_profile',
        ];
    }
}
