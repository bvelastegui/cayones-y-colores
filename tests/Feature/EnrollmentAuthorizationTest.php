<?php

use App\Enums\EnrollmentStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentForm;
use App\Models\Level;
use App\Models\Representative;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a representative cannot inspect or edit another family enrollment', function () {
    $owner = User::factory()->representative()->create();
    $ownerRepresentative = Representative::factory()->create(['user_id' => $owner->id]);
    $student = Student::factory()->for($ownerRepresentative)->create();
    $enrollment = Enrollment::factory()->for($student)->create(['status' => EnrollmentStatus::Draft]);
    EnrollmentForm::factory()->for($enrollment)->create();
    $otherUser = User::factory()->representative()->create();
    Representative::factory()->create(['user_id' => $otherUser->id]);
    Sanctum::actingAs($otherUser);

    $this->getJson("/api/me/students/{$student->id}/enrollment")->assertForbidden();
    $this->putJson("/api/me/enrollments/{$enrollment->id}/form/health", [
        'developmental_notes' => '',
        'care_instructions' => '',
        'additional_notes' => '',
    ])->assertForbidden();
});

test('only an assigned teacher can read the care profile and every successful access is audited', function () {
    $level = Level::factory()->create();
    $course = Course::factory()->for($level)->create();
    $student = Student::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create([
        'level_id' => $level->id,
        'status' => EnrollmentStatus::Active,
    ]);
    $teacherUser = User::factory()->create(['role' => UserRole::Teacher]);
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
    Sanctum::actingAs($teacherUser);

    $this->getJson("/api/teacher/students/{$student->id}/care-profile")->assertForbidden();

    $teacher->courses()->attach($course->id, ['assigned_role' => 'principal']);

    $this->getJson("/api/teacher/students/{$student->id}/care-profile")
        ->assertOk()
        ->assertJsonMissingPath('representative')
        ->assertJsonMissingPath('billing_profile')
        ->assertJsonMissingPath('residence');
    $this->assertDatabaseHas('student_record_access_logs', [
        'student_id' => $student->id,
        'user_id' => $teacherUser->id,
        'scope' => 'teacher_care_profile',
    ]);
});
