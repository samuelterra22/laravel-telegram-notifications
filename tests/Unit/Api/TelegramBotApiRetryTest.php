<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

it('retries on rate limit up to max attempts', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;
        if ($callCount <= 2) {
            return Http::response([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 1],
            ], 429);
        }

        return Http::response(['ok' => true, 'result' => ['message_id' => 1]]);
    });

    $api = new TelegramBotApi('token', 'https://api.telegram.org', 10, maxRetries: 3, baseDelayMs: 1, useJitter: false);
    $result = $api->call('sendMessage', ['text' => 'Hi']);
    expect($result['ok'])->toBeTrue();
    expect($callCount)->toBe(3);
});

it('throws after exhausting retries', function () {
    Http::fake(fn () => Http::response([
        'ok' => false,
        'description' => 'Too Many Requests: retry after 1',
        'parameters' => ['retry_after' => 1],
    ], 429));

    $api = new TelegramBotApi('token', 'https://api.telegram.org', 10, maxRetries: 2, baseDelayMs: 1, useJitter: false);
    $api->call('sendMessage', ['text' => 'Hi']);
})->throws(TelegramApiException::class);

it('does not retry non-rate-limit errors', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;

        return Http::response(['ok' => false, 'description' => 'Bad Request'], 400);
    });

    $api = new TelegramBotApi('token', 'https://api.telegram.org', 10, maxRetries: 3, baseDelayMs: 1);
    try {
        $api->call('sendMessage', ['text' => 'Hi']);
    } catch (\Throwable) {
    }
    expect($callCount)->toBe(1);
});

it('succeeds on first retry', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;
        if ($callCount === 1) {
            return Http::response([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 1],
            ], 429);
        }

        return Http::response(['ok' => true, 'result' => ['message_id' => 1]]);
    });

    $api = new TelegramBotApi('token', 'https://api.telegram.org', 10, maxRetries: 3, baseDelayMs: 1, useJitter: false);
    $result = $api->call('sendMessage', ['text' => 'Hi']);
    expect($result['ok'])->toBeTrue();
});

it('throws non-429 error during retry loop', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;
        if ($callCount === 1) {
            return Http::response([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 1],
            ], 429);
        }

        return Http::response(['ok' => false, 'description' => 'Forbidden'], 403);
    });

    $api = new TelegramBotApi('token', 'https://api.telegram.org', 10, maxRetries: 3, baseDelayMs: 1, useJitter: false);
    $api->call('sendMessage', ['text' => 'Hi']);
})->throws(TelegramApiException::class, 'Forbidden');

it('exposes retry config via getters', function () {
    $api = new TelegramBotApi('token', 'https://api.telegram.org', 10, maxRetries: 5, baseDelayMs: 2000, useJitter: false);
    expect($api->getMaxRetries())->toBe(5);
    expect($api->getBaseDelayMs())->toBe(2000);
    expect($api->getUseJitter())->toBeFalse();
});
