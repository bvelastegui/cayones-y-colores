<?php

namespace App\Contracts\Accounts;

use App\Models\User;

interface AccountInvitationSender
{
    public function send(User $user): void;
}
