<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Jobs\SendTelegramBroadcast;
use SamuelTerra22\TelegramNotifications\Telegram;

it('sends to all chat ids', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);

    $job = new SendTelegramBroadcast('Hello', ['-100', '-200', '-300'], rateLimitMs: 0);
    $job->handle(app(Telegram::class));

    Http::assertSentCount(3);
});

it('continues on individual failures', function () {
    $count = 0;
    Http::fake(function () use (&$count) {
        $count++;
        if ($count === 1) {
            return Http::response(['ok' => false, 'description' => 'Forbidden'], 403);
        }

        return Http::response(['ok' => true, 'result' => ['message_id' => 1]]);
    });

    $job = new SendTelegramBroadcast('Hello', ['-100', '-200', '-300'], rateLimitMs: 0);
    $job->handle(app(Telegram::class));

    // Should not throw, and should have attempted all 3
    expect($count)->toBe(3);
});

it('uses correct parse mode', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);

    $job = new SendTelegramBroadcast('*bold*', ['-100'], 'MarkdownV2', rateLimitMs: 0);
    $job->handle(app(Telegram::class));

    Http::assertSent(fn ($r) => $r['parse_mode'] === 'MarkdownV2');
});

it('passes options through', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);

    $job = new SendTelegramBroadcast('Hi', ['-100'], options: ['disable_notification' => true], rateLimitMs: 0);
    $job->handle(app(Telegram::class));

    Http::assertSent(fn ($r) => $r['disable_notification'] === true);
});
