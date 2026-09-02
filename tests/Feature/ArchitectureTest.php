<?php

use App\Contracts\Accounts\AccountInvitationSender;
use App\Contracts\Notifications\PushNotificationSender;
use App\Contracts\Payments\PaymentGateway;
use App\Events\MonthlyTuitionsGenerated;
use App\Events\UserAccountCreated;
use App\Listeners\SendAccountInvitation;
use App\Listeners\SendMonthlyTuitionNotifications;
use App\Services\PayphonePaymentGateway;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Minishlink\WebPush\WebPush;

arch('integration contracts are interfaces')
    ->expect([
        AccountInvitationSender::class,
        PushNotificationSender::class,
        PaymentGateway::class,
    ])
    ->toBeInterfaces();

arch('controllers do not depend on infrastructure clients')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        DB::class,
        Mail::class,
        WebPush::class,
    ]);

arch('actions remain independent from the HTTP layer')
    ->expect('App\Actions')
    ->not->toUse('Illuminate\Http');

arch('payment gateways honor their substitutable contract')
    ->expect(PayphonePaymentGateway::class)
    ->toImplement(PaymentGateway::class);

test('external side effects are queued only after committed domain changes', function () {
    expect(is_subclass_of(UserAccountCreated::class, ShouldDispatchAfterCommit::class))->toBeTrue()
        ->and(is_subclass_of(MonthlyTuitionsGenerated::class, ShouldDispatchAfterCommit::class))->toBeTrue()
        ->and(is_subclass_of(SendAccountInvitation::class, ShouldQueueAfterCommit::class))->toBeTrue()
        ->and(is_subclass_of(SendMonthlyTuitionNotifications::class, ShouldQueueAfterCommit::class))->toBeTrue();
});
