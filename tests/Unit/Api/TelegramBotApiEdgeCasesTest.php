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

it('throws on non-JSON response body (HTML error page)', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response('<html><body>502 Bad Gateway</body></html>', 502),
    ]);

    try {
        $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(502)
            ->and($e->getTelegramDescription())->toBe('Unknown error');

        return;
    }

    $this->fail('Expected TelegramApiException');
});

it('throws TypeError when API returns empty body with 200', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response('', 200),
    ]);

    // call() has array return type, but json() returns null for empty body
    $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);
})->throws(TypeError::class);

it('throws TypeError on 200 response with non-JSON body', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response('OK', 200),
    ]);

    // call() has array return type, but json() returns null for non-JSON
    $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);
})->throws(TypeError::class);

it('handles response missing ok field', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['result' => ['message_id' => 1]], 200),
    ]);

    $result = $this->api->call('sendMessage', ['chat_id' => '-100123', 'text' => 'test']);

    // No crash - response is returned as-is from json()
    expect($result)->toBeArray()
        ->and($result)->toHaveKey('result');
});

it('works with empty method string', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $result = $this->api->call('', ['chat_id' => '-100123']);

    expect($result['ok'])->toBeTrue();

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/bottest-token/'));
});

it('works with empty params array', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['id' => 123]]),
    ]);

    $result = $this->api->call('getMe');

    expect($result['ok'])->toBeTrue()
        ->and($result['result']['id'])->toBe(123);
});
