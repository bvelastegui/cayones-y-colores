<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Psr\Log\NullLogger;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush(
            [
                'VAPID' => [
                    'subject' => config('services.webpush.subject'),
                    'publicKey' => config('services.webpush.public_key'),
                    'privateKey' => config('services.webpush.private_key'),
                ],
            ],
            [],
            null,
            null,
            null,
            null,
            new NullLogger
        );
    }

    /**
     * Send a push notification to a single user.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $subscriptions = $user->pushSubscriptions;

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        foreach ($subscriptions as $subscription) {
            $this->webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->p256dh,
                    'authToken' => $subscription->auth,
                    'contentEncoding' => 'aesgcm',
                ]),
                $payload,
            );
        }
    }

    /**
     * Send a push notification to many users.
     *
     * @param  Collection<int, User>  $users
     */
    public function sendToUsers(Collection $users, string $title, string $body, array $data = []): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $data);
        }
    }

    /**
     * Flush queued notifications and remove invalid subscriptions.
     *
     * @return array<int, array{endpoint: string, reason: string}>
     */
    public function flush(): array
    {
        $invalid = [];

        foreach ($this->webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                $invalid[] = [
                    'endpoint' => $report->getEndpoint(),
                    'reason' => $report->getReason(),
                ];
            }
        }

        return $invalid;
    }
}
