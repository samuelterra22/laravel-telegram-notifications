<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

beforeEach(function () {
    $this->api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
    );
});

it('propagates ConnectionException from call()', function () {
    Http::fake(fn () => throw new ConnectionException('Connection refused'));

    $this->api->call('sendMessage', ['chat_id' => '123', 'text' => 'hi']);
})->throws(ConnectionException::class, 'Connection refused');

it('callSilent returns false on ConnectionException', function () {
    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    $result = $this->api->callSilent('sendMessage', ['chat_id' => '123', 'text' => 'hi']);

    expect($result)->toBeFalse();
});

it('throws TelegramApiException on HTTP 503 Service Unavailable', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Service Unavailable',
        ], 503),
    ]);

    $this->api->call('sendMessage', ['chat_id' => '123', 'text' => 'hi']);
})->throws(TelegramApiException::class, 'Service Unavailable');

it('throws TelegramApiException on HTTP 504 Gateway Timeout', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Gateway Timeout',
        ], 504),
    ]);

    try {
        $this->api->call('sendMessage', ['chat_id' => '123', 'text' => 'hi']);
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(504)
            ->and($e->getTelegramDescription())->toBe('Gateway Timeout');

        return;
    }

    test()->fail('Expected TelegramApiException was not thrown');
});

it('retries on 429 and succeeds on second attempt', function () {
    $callCount = 0;

    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 0],
            ], 429);
        }

        return Http::response([
            'ok' => true,
            'result' => ['message_id' => 42],
        ]);
    });

    $result = $this->api->call('sendMessage', ['chat_id' => '123', 'text' => 'hi']);

    expect($result['ok'])->toBeTrue()
        ->and($result['result']['message_id'])->toBe(42)
        ->and($callCount)->toBe(2);
});

it('throws non-429 error after rate limit retry fails with different error', function () {
    $callCount = 0;

    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 0],
            ], 429);
        }

        return Http::response([
            'ok' => false,
            'description' => 'Forbidden: bot was blocked by the user',
        ], 403);
    });

    try {
        $this->api->call('sendMessage', ['chat_id' => '123', 'text' => 'hi']);
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(403)
            ->and($e->getTelegramDescription())->toBe('Forbidden: bot was blocked by the user');

        return;
    }

    test()->fail('Expected TelegramApiException was not thrown');
});

it('upload does not retry on 429', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Too Many Requests: retry after 5',
            'parameters' => ['retry_after' => 5],
        ], 429),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tempFile, 'test content');

    try {
        $this->api->upload('sendDocument', ['chat_id' => '123'], 'document', $tempFile);
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(429);
        Http::assertSentCount(1);

        return;
    } finally {
        @unlink($tempFile);
    }

    test()->fail('Expected TelegramApiException was not thrown');
});

it('returns response as-is when ok is false but status is 200', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Some edge case',
        ], 200),
    ]);

    $result = $this->api->call('sendMessage', ['chat_id' => '123', 'text' => 'hi']);

    expect($result['ok'])->toBeFalse()
        ->and($result['description'])->toBe('Some edge case');
});

it('callSilent returns false when call throws TelegramApiException', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Internal Server Error',
        ], 500),
    ]);

    $result = $this->api->callSilent('sendMessage', ['chat_id' => '123', 'text' => 'hi']);

    expect($result)->toBeFalse();
});

it('does not retry on 429 without retry_after value', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Too Many Requests',
        ], 429),
    ]);

    try {
        $this->api->call('sendMessage', ['chat_id' => '123', 'text' => 'hi']);
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(429)
            ->and($e->getRetryAfter())->toBeNull();
        Http::assertSentCount(1);

        return;
    }

    test()->fail('Expected TelegramApiException was not thrown');
});
