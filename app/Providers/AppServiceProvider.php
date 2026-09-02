<?php

namespace App\Providers;

use App\Contracts\Accounts\AccountInvitationSender;
use App\Contracts\Notifications\PushNotificationSender;
use App\Services\MailAccountInvitationSender;
use App\Services\PaymentGatewayResolver;
use App\Services\PayphonePaymentGateway;
use App\Services\WebPushNotificationSender;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Minishlink\WebPush\WebPush;
use Psr\Log\NullLogger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AccountInvitationSender::class, MailAccountInvitationSender::class);
        $this->app->bind(PushNotificationSender::class, WebPushNotificationSender::class);

        $this->app->singleton(WebPush::class, fn (): WebPush => new WebPush(
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
            new NullLogger,
        ));

        $this->app->bind(PayphonePaymentGateway::class);
        $this->app->tag([PayphonePaymentGateway::class], 'payment-gateways');
        $this->app->bind(
            PaymentGatewayResolver::class,
            fn (Application $app): PaymentGatewayResolver => new PaymentGatewayResolver(
                $app->tagged('payment-gateways'),
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
