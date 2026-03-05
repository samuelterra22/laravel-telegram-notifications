<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

beforeEach(function () {
    $this->api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
        maxRetries: 3,
        baseDelayMs: 1,
        useJitter: false,
    );
});

it('retries on 429 without retry_after parameter using backoff', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Too Many Requests',
        ], 429),
    ]);

    try {
        $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);
    } catch (TelegramApiException $e) {
        expect($e->isRateLimited())->toBeTrue()
            ->and($e->getRetryAfter())->toBeNull();

        // 1 initial + 3 retries (maxRetries=3)
        Http::assertSentCount(4);

        return;
    }

    $this->fail('Expected TelegramApiException');
});

it('retries immediately on 429 with retry_after=0', function () {
    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 0',
                'parameters' => ['retry_after' => 0],
            ], 429)
            ->push([
                'ok' => true,
                'result' => ['message_id' => 99],
            ]),
    ]);

    $result = $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);

    expect($result['ok'])->toBeTrue()
        ->and($result['result']['message_id'])->toBe(99);

    Http::assertSentCount(2);
});

it('retries on 429 with non-JSON body using backoff', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response('<html>Rate Limited</html>', 429),
    ]);

    try {
        $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);
    } catch (TelegramApiException $e) {
        expect($e->isRateLimited())->toBeTrue()
            ->and($e->getRetryAfter())->toBeNull()
            ->and($e->getTelegramDescription())->toBe('Unknown error');

        // 1 initial + 3 retries (maxRetries=3), all with backoff
        Http::assertSentCount(4);

        return;
    }

    $this->fail('Expected TelegramApiException');
});

it('does not retry non-429 errors', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: message text is empty',
        ], 400),
    ]);

    try {
        $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => '']);
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(400)
            ->and($e->isRateLimited())->toBeFalse();

        Http::assertSentCount(1);

        return;
    }

    $this->fail('Expected TelegramApiException');
});

it('throws after exhausting retries when all responses are 429', function () {
    Http::fake(fn () => Http::response([
        'ok' => false,
        'description' => 'Too Many Requests: retry after 0',
        'parameters' => ['retry_after' => 0],
    ], 429));

    try {
        $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(429)
            ->and($e->isRateLimited())->toBeTrue();

        // 1 initial + 3 retries (maxRetries=3)
        Http::assertSentCount(4);

        return;
    }

    $this->fail('Expected TelegramApiException');
});
