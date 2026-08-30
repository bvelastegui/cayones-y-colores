<?php

namespace Database\Seeders;

use App\Enums\AssignedRole;
use App\Models\Course;
use App\Models\CourseTeacher;
use App\Models\Teacher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseTeacherSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $teachers = Teacher::all();

        if ($courses->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        foreach ($courses as $course) {
            $principal = $teachers->random();

            CourseTeacher::factory()->create([
                'course_id' => $course->id,
                'teacher_id' => $principal->id,
                'assigned_role' => AssignedRole::Principal->value,
            ]);

            $auxiliaryCount = fake()->numberBetween(0, 2);
            $auxiliaries = $teachers->except($principal->id)->random(min($auxiliaryCount, $teachers->count() - 1));

            foreach ($auxiliaries as $auxiliary) {
                CourseTeacher::factory()->create([
                    'course_id' => $course->id,
                    'teacher_id' => $auxiliary->id,
                    'assigned_role' => AssignedRole::Auxiliary->value,
                ]);
            }
        }
    }
}
