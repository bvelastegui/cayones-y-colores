<?php

namespace App\Services;

use App\Contracts\Accounts\AccountInvitationSender;
use App\Mail\AccountCreated;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class MailAccountInvitationSender implements AccountInvitationSender
{
    public function send(User $user): void
    {
        $token = Password::broker()->createToken($user);
        $setupUrl = url('/set-password').'?'.http_build_query([
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new AccountCreated(
            name: $user->name,
            email: $user->email,
            setupUrl: $setupUrl,
            roleLabel: $user->role->label(),
        ));
    }
}
