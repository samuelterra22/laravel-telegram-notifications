<?php

declare(strict_types=1);

use Monolog\Logger;
use SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;

it('throws for invalid level name', function () {
    $factory = new CreateTelegramLogger;

    $factory(['level' => 'invalid']);
})->throws(\UnhandledMatchError::class);

it('throws InvalidArgumentException for invalid bot name in logging config', function () {
    config()->set('telegram-notifications.logging.bot', 'nonexistent_bot');

    $factory = new CreateTelegramLogger;

    $factory(['level' => 'error']);
})->throws(InvalidArgumentException::class, 'Bot [nonexistent_bot] not configured.');

it('handles empty chat_id gracefully', function () {
    config()->set('telegram-notifications.logging.chat_id', '');

    $factory = new CreateTelegramLogger;

    $logger = $factory(['level' => 'error']);

    expect($logger)->toBeInstanceOf(Logger::class);
});

it('uses defaults when logging config section is null', function () {
    config()->set('telegram-notifications.logging', null);

    $factory = new CreateTelegramLogger;

    // Bot name defaults to 'default', which is configured in TestCase
    $logger = $factory(['level' => 'error']);

    expect($logger)->toBeInstanceOf(Logger::class);
});

it('accepts custom level from config', function () {
    $factory = new CreateTelegramLogger;

    $logger = $factory(['level' => 'debug']);

    expect($logger)->toBeInstanceOf(Logger::class)
        ->and($logger->getHandlers())->toHaveCount(1);
});
