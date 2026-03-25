<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Telegram;

it('returns username for default bot', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => [
                'token' => 'fake-token',
                'username' => 'my_bot',
            ],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->getUsername())->toBe('my_bot');
});

it('returns username for specific bot', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'token-1', 'username' => 'bot_one'],
            'logger' => ['token' => 'token-2', 'username' => 'bot_two'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->getUsername('logger'))->toBe('bot_two');
});

it('returns null when no username configured', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'fake-token'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->getUsername())->toBeNull();
});

it('generates integration link with start param', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'fake-token', 'username' => 'my_bot'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->getIntegrationLink('abc123'))
        ->toBe('https://t.me/my_bot?start=abc123');
});

it('generates integration link without start param', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'fake-token', 'username' => 'my_bot'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->getIntegrationLink())
        ->toBe('https://t.me/my_bot');
});

it('returns null for integration link when no username', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'fake-token'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->getIntegrationLink('abc123'))->toBeNull();
});

it('generates integration link for specific bot', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'token-1', 'username' => 'bot_one'],
            'alerts' => ['token' => 'token-2', 'username' => 'alerts_bot'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->getIntegrationLink('start_param', 'alerts'))
        ->toBe('https://t.me/alerts_bot?start=start_param');
});

it('stores username in TelegramBotApi instance', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'fake-token', 'username' => 'my_bot'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->bot()->getUsername())->toBe('my_bot');
});

it('returns null username from TelegramBotApi when not configured', function (): void {
    $telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'fake-token'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    expect($telegram->bot()->getUsername())->toBeNull();
});
