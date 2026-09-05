<?php

use App\Enums\UserRole;
use App\Models\Representative;
use App\Models\Student;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('a representative cannot inspect another representatives student enrollment', function () {
    $owner = User::factory()->representative()->create();
    $ownerProfile = Representative::factory()->create(['user_id' => $owner->id]);
    $student = Student::factory()->for($ownerProfile)->create();
    $intruder = User::factory()->representative()->create();
    Representative::factory()->create(['user_id' => $intruder->id]);
    Sanctum::actingAs($intruder);

    $this->getJson("/api/me/students/{$student->id}/enrollment")->assertForbidden();
});

test('a representative can inspect their own students current enrollment', function () {
    $user = User::factory()->create(['role' => UserRole::Representative]);
    $representative = Representative::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->for($representative)->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/me/students/{$student->id}/enrollment")
        ->assertOk()
        ->assertExactJson([]);
});

test('representative routes reject users with another role', function () {
    Sanctum::actingAs(User::factory()->teacher()->create());

    $this->getJson('/api/me/students')->assertForbidden();
});
