<?php

use App\Enums\AdmissionStatus;
use App\Models\Course;
use App\Models\Level;
use App\Models\User;

test('login returns the authenticated user and a token without exposing the password', function () {
    $user = User::factory()->create(['password' => bcrypt('secret-password')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'secret-password',
        'device_name' => 'functional-tests',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonStructure(['user', 'token'])
        ->assertJsonMissingPath('user.password');
    expect($response->json('token'))->toBeString()->not->toBeEmpty();
});

test('login rejects incorrect credentials with 422', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'incorrect',
        'device_name' => 'functional-tests',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

test('login validates its required payload with 422', function () {
    $this->postJson('/api/login')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password', 'device_name']);
});

test('vapid public key endpoint returns the configured value', function () {
    config()->set('services.webpush.public_key', 'test-public-key');

    $this->getJson('/api/vapid-public-key')
        ->assertOk()
        ->assertExactJson(['public_key' => 'test-public-key']);
});

test('levels are public, paginated, and include their courses', function () {
    $firstLevel = Level::factory()->create(['name' => 'Inicial 1']);
    $secondLevel = Level::factory()->create(['name' => 'Inicial 2']);
    $course = Course::factory()->for($firstLevel)->create(['parallel' => 'A']);

    $response = $this->getJson('/api/levels?per_page=1&page=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('total', 2)
        ->assertJsonFragment(['id' => $firstLevel->id, 'name' => 'Inicial 1'])
        ->assertJsonFragment(['id' => $course->id, 'parallel' => 'A'])
        ->assertJsonMissing(['id' => $secondLevel->id, 'name' => 'Inicial 2']);
});

test('a public admission is stored as pending and returns its level', function () {
    $level = Level::factory()->create();
    $payload = [
        'level_id' => $level->id,
        'applicant_first_name' => 'Ana',
        'applicant_last_name' => 'Pérez',
        'applicant_birth_date' => '2022-05-10',
        'representative_names' => 'María Pérez',
        'contact_email' => 'maria@example.com',
        'contact_phone' => '0999999999',
        'application_date' => '2026-09-04',
        'status' => AdmissionStatus::Approved->value,
    ];

    $response = $this->postJson('/api/admissions', $payload);

    $response
        ->assertCreated()
        ->assertJsonPath('status', AdmissionStatus::Pending->value)
        ->assertJsonPath('level.id', $level->id);
    $this->assertDatabaseHas('admissions', [
        'contact_email' => 'maria@example.com',
        'status' => AdmissionStatus::Pending->value,
    ]);
});

test('a public admission validates required fields with 422', function () {
    $this->postJson('/api/admissions')
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'level_id',
            'applicant_first_name',
            'applicant_last_name',
            'applicant_birth_date',
            'representative_names',
            'contact_email',
            'contact_phone',
            'application_date',
        ]);
});
