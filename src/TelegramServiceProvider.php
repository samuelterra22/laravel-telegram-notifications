<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications;

use SamuelTerra22\TelegramNotifications\Channels\TelegramChannel;
use SamuelTerra22\TelegramNotifications\Commands\TelegramGetMeCommand;
use SamuelTerra22\TelegramNotifications\Commands\TelegramSetWebhookCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TelegramServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('telegram-notifications')
            ->hasConfigFile()
            ->hasCommands([
                TelegramSetWebhookCommand::class,
                TelegramGetMeCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Telegram::class, function ($app) {
            $config = $app['config']['telegram-notifications'];

            return new Telegram(
                botsConfig: $config['bots'] ?? [],
                defaultBot: $config['default'] ?? 'default',
                baseUrl: $config['api_base_url'] ?? 'https://api.telegram.org',
                timeout: $config['timeout'] ?? 10,
            );
        });

        $this->app->singleton(TelegramChannel::class, function ($app) {
            return new TelegramChannel($app->make(Telegram::class));
        });
    }
}
