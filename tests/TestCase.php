<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SamuelTerra22\TelegramNotifications\TelegramServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [TelegramServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('telegram-notifications.default', 'default');
        config()->set('telegram-notifications.bots.default.token', 'test-token-123');
        config()->set('telegram-notifications.bots.default.chat_id', '-1001234567890');
        config()->set('telegram-notifications.bots.default.topic_id', '42');
        config()->set('telegram-notifications.bots.alerts.token', 'alerts-token-456');
        config()->set('telegram-notifications.bots.alerts.chat_id', '-1009876543210');
        config()->set('telegram-notifications.api_base_url', 'https://api.telegram.org');
        config()->set('telegram-notifications.timeout', 10);
        config()->set('telegram-notifications.logging.enabled', true);
        config()->set('telegram-notifications.logging.bot', 'default');
        config()->set('telegram-notifications.logging.chat_id', '-1001234567890');
        config()->set('telegram-notifications.logging.topic_id', '99');
    }
}
