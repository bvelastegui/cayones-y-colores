<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

test('current user endpoint returns the authenticated user', function () {
    $user = User::factory()->teacher()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email)
        ->assertJsonMissingPath('password');
});

test('logout deletes the current access token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('functional-tests');

    $this->withToken($token->plainTextToken)
        ->postJson('/api/logout')
        ->assertOk()
        ->assertExactJson(['message' => 'Sesión cerrada correctamente.']);
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
});

test('push subscriptions can be created, updated, and removed', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $endpoint = 'https://push.example.com/subscription/1';

    $this->postJson('/api/push/subscriptions', [
        'endpoint' => $endpoint,
        'keys' => ['p256dh' => 'first-key', 'auth' => 'first-auth'],
    ])->assertCreated();
    $this->postJson('/api/push/subscriptions', [
        'endpoint' => $endpoint,
        'keys' => ['p256dh' => 'updated-key', 'auth' => 'updated-auth'],
    ])->assertCreated();

    $this->assertDatabaseCount('push_subscriptions', 1);
    $this->assertDatabaseHas('push_subscriptions', [
        'user_id' => $user->id,
        'endpoint' => $endpoint,
        'p256dh' => 'updated-key',
        'auth' => 'updated-auth',
    ]);

    $this->deleteJson('/api/push/subscriptions', ['endpoint' => $endpoint])->assertNoContent();
    $this->assertDatabaseMissing('push_subscriptions', ['user_id' => $user->id, 'endpoint' => $endpoint]);
});

test('push subscription creation validates endpoint and keys with 422', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/push/subscriptions', ['endpoint' => 'not-a-url'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['endpoint', 'keys.p256dh', 'keys.auth']);
});

test('notifications are returned newest first and can all be marked as read', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $olderId = (string) Str::uuid();
    $newerId = (string) Str::uuid();
    DB::table('notifications')->insert([
        [
            'id' => $olderId,
            'type' => 'FunctionalTestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'older'], JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ],
        [
            'id' => $newerId,
            'type' => 'FunctionalTestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'newer'], JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->getJson('/api/notifications')
        ->assertOk()
        ->assertJsonPath('0.id', $newerId)
        ->assertJsonPath('1.id', $olderId);
    $this->postJson('/api/notifications/read')->assertNoContent();

    expect($user->unreadNotifications()->count())->toBe(0);
});
