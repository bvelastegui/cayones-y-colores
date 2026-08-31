<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\TuitionStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Tuition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function enroll(Student $student, Course $course, User $user): Enrollment
    {
        if ($user->role !== UserRole::Representative) {
            throw ValidationException::withMessages([
                'role' => ['Solo los representantes pueden realizar matrículas.'],
            ]);
        }

        if ($student->representative->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'student' => ['El estudiante no pertenece a tu cuenta.'],
            ]);
        }

        if ($student->enrollments()->where('status', EnrollmentStatus::Active)->exists()) {
            throw ValidationException::withMessages([
                'student' => ['El estudiante ya tiene una matrícula activa.'],
            ]);
        }

        $activeCount = $course->enrollments()->where('status', EnrollmentStatus::Active)->count();
        $maxCapacity = $course->level->max_capacity;

        if ($activeCount >= $maxCapacity) {
            throw ValidationException::withMessages([
                'course' => ['El paralelo ha alcanzado su aforo máximo.'],
            ]);
        }

        $enrollment = DB::transaction(function () use ($student, $course): Enrollment {
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'enrollment_date' => now(),
                'status' => EnrollmentStatus::Active,
            ]);

            Tuition::create([
                'student_id' => $student->id,
                'amount' => $course->level->enrollment_fee,
                'generation_date' => now(),
                'due_date' => now()->addDays(10),
                'status' => TuitionStatus::Pending,
            ]);

            return $enrollment;
        });

        return $enrollment->load(['student', 'course.level']);
    }
}
