<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

beforeEach(function () {
    $this->api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
    );
});

it('throws immediately on 429 without retry_after parameter', function () {
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

        // Only 1 request: no retry because retry_after is null
        Http::assertSentCount(1);

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

it('throws on 429 with non-JSON body', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response('<html>Rate Limited</html>', 429),
    ]);

    try {
        $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);
    } catch (TelegramApiException $e) {
        expect($e->isRateLimited())->toBeTrue()
            ->and($e->getRetryAfter())->toBeNull()
            ->and($e->getTelegramDescription())->toBe('Unknown error');

        // No retry because retry_after is null
        Http::assertSentCount(1);

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

it('throws after double 429 when retry also returns 429', function () {
    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 1],
            ], 429)
            ->push([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 60',
                'parameters' => ['retry_after' => 60],
            ], 429),
    ]);

    try {
        $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(429)
            ->and($e->getRetryAfter())->toBe(60);

        Http::assertSentCount(2);

        return;
    }

    $this->fail('Expected TelegramApiException');
});
