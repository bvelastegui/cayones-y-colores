<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\postJson;

test('an invited user can establish a password with a valid token', function () {
    $user = User::factory()->create(['email' => 'invited@example.com']);
    $token = Password::broker()->createToken($user);

    postJson('/api/reset-password', [
        'email' => $user->email,
        'token' => $token,
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ])->assertOk();

    expect(Hash::check('NewSecurePassword123!', $user->refresh()->password))->toBeTrue();
});

test('a tampered setup token is rejected without changing the password', function () {
    $user = User::factory()->create(['email' => 'invited@example.com']);
    $originalPassword = $user->password;

    postJson('/api/reset-password', [
        'email' => $user->email,
        'token' => 'tampered-token',
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');

    expect($user->refresh()->password)->toBe($originalPassword);
});

test('password confirmation is required', function () {
    postJson('/api/reset-password', [
        'email' => 'invited@example.com',
        'token' => 'token',
        'password' => 'NewSecurePassword123!',
    ])->assertUnprocessable()->assertJsonValidationErrors('password');

});
