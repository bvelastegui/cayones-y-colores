<?php

use App\Models\AcademicReport;
use App\Models\Representative;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a representative lists only their students', function () {
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $ownStudent = Student::factory()->for($representative)->create();
    $otherStudent = Student::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/me/students')
        ->assertOk()
        ->assertJsonFragment(['id' => $ownStudent->id])
        ->assertJsonMissing(['id' => $otherStudent->id]);
});

test('a representative receives null when their student has no enrollment', function () {
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->for($representative)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/me/students/{$student->id}/enrollment")
        ->assertOk()
        ->assertExactJson([]);
});

test('a representative lists reports for their student only', function () {
    $user = User::factory()->representative()->create();
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->for($representative)->create();
    $report = AcademicReport::factory()->for($student)->for(Teacher::factory())->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/me/students/{$student->id}/reports")
        ->assertOk()
        ->assertJsonFragment(['id' => $report->id]);
});

test('a representative receives 403 for another family student data', function (string $uri) {
    $user = User::factory()->representative()->create();
    Representative::factory()->create(['user_id' => $user->id]);
    $otherStudent = Student::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson(str_replace('{student}', (string) $otherStudent->id, $uri))->assertForbidden();
})->with([
    'enrollment' => '/api/me/students/{student}/enrollment',
    'reports' => '/api/me/students/{student}/reports',
]);

test('representative Payphone payment validates tuition identifiers with 422', function () {
    $user = User::factory()->representative()->create();
    Representative::factory()->create(['user_id' => $user->id]);
    Sanctum::actingAs($user);

    $this->postJson('/api/me/payments/payphone')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('tuition_ids');
});
