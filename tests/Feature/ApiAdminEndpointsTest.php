<?php

use App\Models\Level;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('an administrator opens the dashboard', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson('/api/admin/dashboard')->assertOk()->assertJsonIsObject();
});

test('an administrator lists every administrative resource', function (string $uri) {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson($uri)
        ->assertOk()
        ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);
})->with([
    'users' => '/api/users',
    'admissions' => '/api/admissions',
    'representatives' => '/api/representatives',
    'teachers' => '/api/teachers',
    'courses' => '/api/courses',
    'course teachers' => '/api/course-teachers',
    'students' => '/api/students',
    'enrollments' => '/api/enrollments',
    'academic periods' => '/api/academic-periods',
    'tuitions' => '/api/tuitions',
    'payments' => '/api/payments',
    'academic reports' => '/api/academic-reports',
]);

test('administrative resource creation validates an empty payload with 422', function (string $uri) {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson($uri)
        ->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors']);
})->with([
    'users' => '/api/users',
    'levels' => '/api/levels',
    'representatives' => '/api/representatives',
    'teachers' => '/api/teachers',
    'courses' => '/api/courses',
    'course teachers' => '/api/course-teachers',
    'students' => '/api/students',
    'enrollments' => '/api/enrollments',
    'academic periods' => '/api/academic-periods',
    'tuitions' => '/api/tuitions',
    'payments' => '/api/payments',
    'academic reports' => '/api/academic-reports',
]);

test('administrative resource operations return 404 for a missing record', function (string $method, string $uri) {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->json($method, $uri)->assertNotFound();
})->with([
    'show user' => ['GET', '/api/users/999999'],
    'update user' => ['PUT', '/api/users/999999'],
    'delete user' => ['DELETE', '/api/users/999999'],
    'show level' => ['GET', '/api/levels/999999'],
    'update level' => ['PUT', '/api/levels/999999'],
    'delete level' => ['DELETE', '/api/levels/999999'],
    'show admission' => ['GET', '/api/admissions/999999'],
    'update admission' => ['PUT', '/api/admissions/999999'],
    'delete admission' => ['DELETE', '/api/admissions/999999'],
    'approve admission' => ['POST', '/api/admissions/999999/approve'],
    'reject admission' => ['POST', '/api/admissions/999999/reject'],
    'show representative' => ['GET', '/api/representatives/999999'],
    'update representative' => ['PUT', '/api/representatives/999999'],
    'delete representative' => ['DELETE', '/api/representatives/999999'],
    'show teacher' => ['GET', '/api/teachers/999999'],
    'update teacher' => ['PUT', '/api/teachers/999999'],
    'delete teacher' => ['DELETE', '/api/teachers/999999'],
    'show course' => ['GET', '/api/courses/999999'],
    'update course' => ['PUT', '/api/courses/999999'],
    'delete course' => ['DELETE', '/api/courses/999999'],
    'show course teacher' => ['GET', '/api/course-teachers/999999'],
    'update course teacher' => ['PUT', '/api/course-teachers/999999'],
    'delete course teacher' => ['DELETE', '/api/course-teachers/999999'],
    'show student' => ['GET', '/api/students/999999'],
    'update student' => ['PUT', '/api/students/999999'],
    'delete student' => ['DELETE', '/api/students/999999'],
    'show enrollment' => ['GET', '/api/enrollments/999999'],
    'update enrollment' => ['PUT', '/api/enrollments/999999'],
    'delete enrollment' => ['DELETE', '/api/enrollments/999999'],
    'assign enrollment' => ['PUT', '/api/enrollments/999999/assignment'],
    'set enrollment outcome' => ['PUT', '/api/enrollments/999999/outcome'],
    'set enrollment exception' => ['PUT', '/api/enrollments/999999/exception'],
    'show academic period' => ['GET', '/api/academic-periods/999999'],
    'update academic period' => ['PUT', '/api/academic-periods/999999'],
    'delete academic period' => ['DELETE', '/api/academic-periods/999999'],
    'run assignments' => ['POST', '/api/academic-periods/999999/assignments/run'],
    'show tuition' => ['GET', '/api/tuitions/999999'],
    'update tuition' => ['PUT', '/api/tuitions/999999'],
    'delete tuition' => ['DELETE', '/api/tuitions/999999'],
    'show payment' => ['GET', '/api/payments/999999'],
    'update payment' => ['PUT', '/api/payments/999999'],
    'delete payment' => ['DELETE', '/api/payments/999999'],
    'show academic report' => ['GET', '/api/academic-reports/999999'],
    'update academic report' => ['PUT', '/api/academic-reports/999999'],
    'delete academic report' => ['DELETE', '/api/academic-reports/999999'],
]);

test('an administrator completes the level CRUD lifecycle', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $payload = [
        'name' => 'Inicial Funcional',
        'max_capacity' => 20,
        'student_aux_ratio' => 5,
        'enrollment_fee' => 125.50,
        'monthly_fee' => 95.25,
    ];

    $created = $this->postJson('/api/levels', $payload);

    $created->assertCreated()->assertJsonPath('name', 'Inicial Funcional');
    $levelId = $created->json('id');
    $this->getJson("/api/levels/{$levelId}")
        ->assertOk()
        ->assertJsonPath('max_capacity', 20);
    $this->putJson("/api/levels/{$levelId}", ['max_capacity' => 24])
        ->assertOk()
        ->assertJsonPath('max_capacity', 24);
    $this->assertDatabaseHas('levels', ['id' => $levelId, 'max_capacity' => 24]);
    $this->deleteJson("/api/levels/{$levelId}")->assertNoContent();
    $this->assertDatabaseMissing('levels', ['id' => $levelId]);
});

test('level creation rejects duplicate names with 422', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $level = Level::factory()->create(['name' => 'Inicial Duplicado']);

    $this->postJson('/api/levels', [
        'name' => $level->name,
        'max_capacity' => 20,
        'student_aux_ratio' => 5,
        'enrollment_fee' => 100,
        'monthly_fee' => 90,
    ])->assertUnprocessable()->assertJsonValidationErrors('name');
});

test('an administrator generates monthly tuitions for a valid date', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson('/api/tuitions/generate', ['date' => '2026-09-01'])
        ->assertOk()
        ->assertExactJson(['created' => 0, 'date' => '2026-09-01']);
});

test('monthly tuition generation rejects an invalid date with 422', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson('/api/tuitions/generate', ['date' => '09/01/2026'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('date');
});
