<?php

namespace App\Listeners;

use App\Contracts\Accounts\AccountInvitationSender;
use App\Events\UserAccountCreated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class SendAccountInvitation implements ShouldQueueAfterCommit
{
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(private AccountInvitationSender $invitationSender) {}

    public function handle(UserAccountCreated $event): void
    {
        $this->invitationSender->send($event->user);
    }
}
