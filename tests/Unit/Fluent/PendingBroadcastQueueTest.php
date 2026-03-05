<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use SamuelTerra22\TelegramNotifications\Facades\Telegram;
use SamuelTerra22\TelegramNotifications\Jobs\SendTelegramBroadcast;

it('dispatches broadcast job', function () {
    Queue::fake();

    Telegram::broadcast(['-100', '-200'])
        ->text('Announcement')
        ->rateLimit(100)
        ->queue();

    Queue::assertPushed(SendTelegramBroadcast::class, function ($job) {
        return $job->text === 'Announcement'
            && $job->chatIds === ['-100', '-200']
            && $job->rateLimitMs === 100;
    });
});

it('dispatches to specific queue', function () {
    Queue::fake();

    Telegram::broadcast(['-100'])->text('Hi')->queue('telegram');

    Queue::assertPushedOn('telegram', SendTelegramBroadcast::class);
});

it('dispatches to specific connection', function () {
    Queue::fake();

    Telegram::broadcast(['-100'])->text('Hi')->queue(connection: 'redis');

    Queue::assertPushed(SendTelegramBroadcast::class);
});

it('dispatches with markdown parse mode', function () {
    Queue::fake();

    Telegram::broadcast(['-100'])->markdown('*bold*')->queue();

    Queue::assertPushed(SendTelegramBroadcast::class, function ($job) {
        return $job->parseMode === 'MarkdownV2';
    });
});
