<?php

namespace App\Contracts\Notifications;

use Illuminate\Support\Collection;

interface PushNotificationSender
{
    /**
     * @param  Collection<int, int>  $userIds
     * @param  array<string, mixed>  $data
     */
    public function sendToUsers(Collection $userIds, string $title, string $body, array $data = []): void;
}
