<?php

use App\Enums\AdmissionStatus;
use App\Enums\UserRole;
use App\Models\Admission;
use App\Models\Course;
use App\Models\Level;
use App\Models\Representative;
use App\Models\Student;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a representative cannot inspect courses for another representatives student', function () {
    $owner = User::factory()->representative()->create();
    $ownerProfile = Representative::factory()->create(['user_id' => $owner->id]);
    $student = Student::factory()->for($ownerProfile)->create();
    $intruder = User::factory()->representative()->create();
    Representative::factory()->create(['user_id' => $intruder->id]);
    Sanctum::actingAs($intruder);

    $this->getJson("/api/me/students/{$student->id}/courses")->assertForbidden();
});

test('a representative can inspect courses for their own student', function () {
    $user = User::factory()->create(['role' => UserRole::Representative]);
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->for($representative)->create();
    $level = Level::factory()->create(['enrollment_fee' => 80, 'monthly_fee' => 150]);
    Admission::factory()->for($level)->create([
        'student_id' => $student->id,
        'representative_id' => $representative->id,
        'status' => AdmissionStatus::Approved,
    ]);
    $course = Course::factory()->for($level)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/me/students/{$student->id}/courses")
        ->assertOk()
        ->assertJsonFragment(['id' => $course->id]);
});

test('representative routes reject users with another role', function () {
    Sanctum::actingAs(User::factory()->teacher()->create());

    $this->getJson('/api/me/students')->assertForbidden();
});
