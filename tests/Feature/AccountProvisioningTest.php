<?php

use App\Actions\Representatives\CreateRepresentativeAction;
use App\Contracts\Accounts\AccountInvitationSender;
use App\Enums\UserRole;
use App\Mail\AccountCreated;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('creating a representative provisions one linked account', function () {
    $representative = app(CreateRepresentativeAction::class)->execute([
        'id_card' => '1723456789',
        'first_name' => 'María',
        'last_name' => 'Pérez',
        'email' => 'maria@example.com',
        'phone' => '0991234567',
    ]);

    $user = User::where('email', 'maria@example.com')->sole();

    expect($representative->user_id)->toBe($user->id)
        ->and($user->role)->toBe(UserRole::Representative)
        ->and($user->password)->not->toBe('password');
});

test('account invitations contain a setup link and never expose a password', function () {
    Mail::fake();
    $user = User::factory()->representative()->create(['email' => 'family@example.com']);

    app(AccountInvitationSender::class)->send($user);

    Mail::assertSent(AccountCreated::class, function (AccountCreated $mail) use ($user): bool {
        return $mail->hasTo($user->email)
            && str_contains($mail->setupUrl, '/set-password?')
            && str_contains($mail->setupUrl, 'token=')
            && str_contains($mail->setupUrl, 'email=family%40example.com');
    });

    expect($user->password)->not->toBe('password');
});
