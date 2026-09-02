<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

#[Signature('push:generate-vapid-keys')]
#[Description('Genera un par de claves VAPID para notificaciones push.')]
class GenerateVapidKeys extends Command
{
    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->info('Claves VAPID generadas:');
        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->line('');
        $this->comment('Copia estas variables en tu archivo .env y configura VAPID_SUBJECT con un correo o URL.');

        return self::SUCCESS;
    }
}
