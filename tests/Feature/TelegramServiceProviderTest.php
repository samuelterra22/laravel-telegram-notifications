<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Channels\TelegramChannel;
use SamuelTerra22\TelegramNotifications\Facades\Telegram as TelegramFacade;
use SamuelTerra22\TelegramNotifications\Telegram;

it('registers Telegram as singleton', function () {
    $instance1 = app(Telegram::class);
    $instance2 = app(Telegram::class);

    expect($instance1)->toBeInstanceOf(Telegram::class)
        ->and($instance1)->toBe($instance2);
});

it('registers TelegramChannel as singleton', function () {
    $instance = app(TelegramChannel::class);

    expect($instance)->toBeInstanceOf(TelegramChannel::class);
});

it('resolves Telegram with correct config', function () {
    $telegram = app(Telegram::class);

    expect($telegram->getDefaultBot())->toBe('default')
        ->and($telegram->getBotsConfig())->toHaveKey('default')
        ->and($telegram->bot()->getToken())->toBe('test-token-123');
});

it('facade resolves correctly', function () {
    expect(TelegramFacade::getFacadeRoot())->toBeInstanceOf(Telegram::class);
});

it('config file is set', function () {
    expect(config('telegram-notifications.default'))->toBe('default')
        ->and(config('telegram-notifications.bots.default.token'))->toBe('test-token-123')
        ->and(config('telegram-notifications.timeout'))->toBe(10);
});

it('registers artisan commands', function () {
    $this->artisan('list')
        ->assertSuccessful();
});
