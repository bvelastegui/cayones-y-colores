<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentIds = Student::pluck('id')->shuffle()->take(30)->toArray();
        $courseIds = Course::pluck('id')->toArray();

        $combinations = [];

        foreach ($studentIds as $studentId) {
            $courseId = fake()->randomElement($courseIds);
            $key = "{$studentId}-{$courseId}";

            if (isset($combinations[$key])) {
                continue;
            }

            $combinations[$key] = true;

            Enrollment::factory()->create([
                'student_id' => $studentId,
                'course_id' => $courseId,
            ]);
        }
    }
}
