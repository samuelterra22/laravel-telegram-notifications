<?php

declare(strict_types=1);

use Monolog\Logger;
use SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;
use SamuelTerra22\TelegramNotifications\Logging\TelegramHandler;

it('creates a Logger instance', function () {
    $factory = new CreateTelegramLogger;

    $logger = $factory([
        'level' => 'error',
    ]);

    expect($logger)->toBeInstanceOf(Logger::class)
        ->and($logger->getName())->toBe('telegram');
});

it('sets handler level from config', function () {
    $factory = new CreateTelegramLogger;

    $logger = $factory([
        'level' => 'warning',
    ]);

    $handlers = $logger->getHandlers();

    expect($handlers)->toHaveCount(1)
        ->and($handlers[0])->toBeInstanceOf(TelegramHandler::class);
});

it('uses chat_id from config', function () {
    $factory = new CreateTelegramLogger;

    $logger = $factory([
        'level' => 'error',
        'chat_id' => '-100custom',
    ]);

    expect($logger)->toBeInstanceOf(Logger::class);
});

it('uses logging config defaults', function () {
    config()->set('telegram-notifications.logging.chat_id', '-100default');
    config()->set('telegram-notifications.logging.topic_id', '55');

    $factory = new CreateTelegramLogger;

    $logger = $factory([
        'level' => 'error',
    ]);

    expect($logger)->toBeInstanceOf(Logger::class);
});
