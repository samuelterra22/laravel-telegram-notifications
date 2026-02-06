<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

beforeEach(function () {
    $this->api = new TelegramBotApi(
        token: 'test-token-123',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('makes a successful API call', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 123],
        ]),
    ]);

    $result = $this->api->call('sendMessage', [
        'chat_id' => '-1001234567890',
        'text' => 'Hello',
    ]);

    expect($result)->toBeArray()
        ->and($result['ok'])->toBeTrue()
        ->and($result['result']['message_id'])->toBe(123);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'bot'.'test-token-123'.'/sendMessage')
            && $request['chat_id'] === '-1001234567890'
            && $request['text'] === 'Hello';
    });
});

it('throws TelegramApiException on error response', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: chat not found',
            'error_code' => 400,
        ], 400),
    ]);

    $this->api->call('sendMessage', ['chat_id' => 'invalid']);
})->throws(TelegramApiException::class, 'Telegram API error [sendMessage]: Bad Request: chat not found');

it('includes status code in exception', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Unauthorized',
        ], 401),
    ]);

    try {
        $this->api->call('getMe');
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(401)
            ->and($e->getApiMethod())->toBe('getMe')
            ->and($e->getTelegramDescription())->toBe('Unauthorized');
    }
});

it('handles rate limiting with retry_after', function () {
    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 1],
            ], 429)
            ->push([
                'ok' => true,
                'result' => ['message_id' => 456],
            ]),
    ]);

    $result = $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);

    expect($result['ok'])->toBeTrue()
        ->and($result['result']['message_id'])->toBe(456);
});

it('callSilent returns true on success', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => true,
        ]),
    ]);

    expect($this->api->callSilent('sendMessage', ['chat_id' => '-100123', 'text' => 'test']))->toBeTrue();
});

it('callSilent returns false on error without throwing', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Error',
        ], 500),
    ]);

    expect($this->api->callSilent('sendMessage', ['chat_id' => '-100123', 'text' => 'test']))->toBeFalse();
});

it('uploads a file via multipart', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 789],
        ]),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'test content');

    $result = $this->api->upload('sendDocument', [
        'chat_id' => '-100123',
    ], 'document', $tempFile);

    expect($result['ok'])->toBeTrue();

    unlink($tempFile);
});

it('throws on upload error', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: file too big',
        ], 400),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'test');

    try {
        $this->api->upload('sendDocument', ['chat_id' => '-100123'], 'document', $tempFile);
    } finally {
        unlink($tempFile);
    }
})->throws(TelegramApiException::class);

it('returns the token', function () {
    expect($this->api->getToken())->toBe('test-token-123');
});

it('returns the base URL', function () {
    expect($this->api->getBaseUrl())->toBe('https://api.telegram.org');
});

it('returns the timeout', function () {
    expect($this->api->getTimeout())->toBe(10);
});

it('uses custom base URL', function () {
    Http::fake([
        'custom.api.local/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(
        token: 'custom-token',
        baseUrl: 'https://custom.api.local',
    );

    $api->call('getMe');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'custom.api.local'));
});
