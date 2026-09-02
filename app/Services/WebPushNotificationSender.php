<?php

namespace App\Services;

use App\Contracts\Notifications\PushNotificationSender;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushNotificationSender implements PushNotificationSender
{
    public function __construct(private WebPush $webPush) {}

    /**
     * @param  Collection<int, int>  $userIds
     * @param  array<string, mixed>  $data
     */
    public function sendToUsers(Collection $userIds, string $title, string $body, array $data = []): void
    {
        $users = User::query()->with('pushSubscriptions')->whereKey($userIds)->get();
        $payload = json_encode(compact('title', 'body', 'data'), JSON_THROW_ON_ERROR);

        foreach ($users as $user) {
            foreach ($user->pushSubscriptions as $subscription) {
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

        foreach ($this->webPush->flush() as $report) {
            if (! $report->isSuccess() && $report->isSubscriptionExpired()) {
                PushSubscription::query()->where('endpoint', $report->getEndpoint())->delete();
            }
        }
    }
}
