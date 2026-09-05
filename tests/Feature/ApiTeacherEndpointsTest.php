<?php

use App\Enums\AssignedRole;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\CourseTeacher;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a teacher lists their assigned courses and active student count', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $assignedCourse = Course::factory()->create();
    $otherCourse = Course::factory()->create();
    CourseTeacher::factory()->for($assignedCourse)->for($teacher)->create([
        'assigned_role' => AssignedRole::Principal->value,
    ]);
    Enrollment::factory()->for($assignedCourse)->create(['status' => EnrollmentStatus::Active->value]);
    Sanctum::actingAs($user);

    $this->getJson('/api/teacher/courses')
        ->assertOk()
        ->assertJsonFragment(['id' => $assignedCourse->id, 'active_students_count' => 1])
        ->assertJsonMissing(['id' => $otherCourse->id]);
});

test('a teacher lists active students in an assigned course', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $course = Course::factory()->create();
    CourseTeacher::factory()->for($course)->for($teacher)->create([
        'assigned_role' => AssignedRole::Principal->value,
    ]);
    $activeEnrollment = Enrollment::factory()->for($course)->create(['status' => EnrollmentStatus::Active->value]);
    $inactiveEnrollment = Enrollment::factory()->for($course)->create(['status' => EnrollmentStatus::Finalized->value]);
    Sanctum::actingAs($user);

    $this->getJson("/api/teacher/courses/{$course->id}/students")
        ->assertOk()
        ->assertJsonFragment(['id' => $activeEnrollment->student_id])
        ->assertJsonMissing(['id' => $inactiveEnrollment->student_id]);
});

test('a teacher cannot list students from an unassigned course', function () {
    $user = User::factory()->teacher()->create();
    Teacher::factory()->create(['user_id' => $user->id]);
    $course = Course::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/teacher/courses/{$course->id}/students")->assertForbidden();
});

test('a teacher creates and lists an academic report for an assigned course', function () {
    $user = User::factory()->teacher()->create();
    $teacher = Teacher::factory()->create(['user_id' => $user->id]);
    $course = Course::factory()->create();
    $student = Student::factory()->create();
    CourseTeacher::factory()->for($course)->for($teacher)->create([
        'assigned_role' => AssignedRole::Principal->value,
    ]);
    Enrollment::factory()->for($course)->for($student)->create(['status' => EnrollmentStatus::Active->value]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/teacher/reports', [
        'student_id' => $student->id,
        'course_id' => $course->id,
        'development_area' => 'Comunicación y lenguaje',
        'evaluated_skill' => 'Expresión oral',
        'achievement_level' => 'A',
        'observations' => 'Avance esperado',
    ]);

    $response->assertCreated()->assertJsonPath('teacher_id', $teacher->id);
    $this->assertDatabaseHas('academic_reports', [
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'evaluated_skill' => 'Expresión oral',
    ]);
    $this->getJson('/api/teacher/reports')
        ->assertOk()
        ->assertJsonFragment(['id' => $response->json('id')]);
});

test('teacher report creation validates required fields with 422', function () {
    $user = User::factory()->teacher()->create();
    Teacher::factory()->create(['user_id' => $user->id]);
    Sanctum::actingAs($user);

    $this->postJson('/api/teacher/reports')
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'student_id',
            'course_id',
            'development_area',
            'evaluated_skill',
            'achievement_level',
        ]);
});
