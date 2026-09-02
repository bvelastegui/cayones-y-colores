<?php

namespace App\Listeners;

use App\Contracts\Notifications\PushNotificationSender;
use App\Events\MonthlyTuitionsGenerated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class SendMonthlyTuitionNotifications implements ShouldQueueAfterCommit
{
    public int $tries = 4;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60, 120];

    public function __construct(private PushNotificationSender $notificationSender) {}

    public function handle(MonthlyTuitionsGenerated $event): void
    {
        $this->notificationSender->sendToUsers(
            collect($event->userIds),
            'Nueva pensión generada',
            'Se generó una nueva pensión mensual. Ingresa al portal para revisar los detalles.',
            ['url' => '/parent/payments'],
        );
    }
}
