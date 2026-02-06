<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;
use SamuelTerra22\TelegramNotifications\Telegram;

beforeEach(function () {
    $this->telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'test-token'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

// --- 400 Bad Request ---

it('throws TelegramApiException on 400 Bad Request', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: chat not found',
            'error_code' => 400,
        ], 400),
    ]);

    $this->telegram->sendMessage('-100invalid', 'Hello');
})->throws(TelegramApiException::class, 'Bad Request: chat not found');

it('exception contains correct status code for 400', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: message text is empty',
            'error_code' => 400,
        ], 400),
    ]);

    try {
        $this->telegram->sendMessage('-100123', '');
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(400)
            ->and($e->getApiMethod())->toBe('sendMessage')
            ->and($e->getTelegramDescription())->toBe('Bad Request: message text is empty')
            ->and($e->isRateLimited())->toBeFalse()
            ->and($e->getRetryAfter())->toBeNull();
    }
});

// --- 401 Unauthorized ---

it('throws TelegramApiException on 401 Unauthorized', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Unauthorized',
        ], 401),
    ]);

    $this->telegram->getMe();
})->throws(TelegramApiException::class, 'Unauthorized');

// --- 403 Forbidden ---

it('throws TelegramApiException on 403 Forbidden', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Forbidden: bot was blocked by the user',
        ], 403),
    ]);

    $this->telegram->sendMessage('-100123', 'Test');
})->throws(TelegramApiException::class, 'Forbidden: bot was blocked by the user');

// --- 404 Not Found ---

it('throws TelegramApiException on 404 Not Found', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Not Found',
        ], 404),
    ]);

    $this->telegram->sendMessage('-100123', 'Test');
})->throws(TelegramApiException::class, 'Not Found');

// --- 429 Rate Limiting ---

it('handles 429 rate limit with successful retry', function () {
    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 1],
            ], 429)
            ->push([
                'ok' => true,
                'result' => ['message_id' => 42],
            ]),
    ]);

    $result = $this->telegram->sendMessage('-100123', 'Retry test');

    expect($result['ok'])->toBeTrue()
        ->and($result['result']['message_id'])->toBe(42);

    Http::assertSentCount(2);
});

it('throws on 429 rate limit when retry also fails', function () {
    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 1',
                'parameters' => ['retry_after' => 1],
            ], 429)
            ->push([
                'ok' => false,
                'description' => 'Too Many Requests: retry after 30',
                'parameters' => ['retry_after' => 30],
            ], 429),
    ]);

    try {
        $this->telegram->sendMessage('-100123', 'Double rate limit');
        $this->fail('Expected TelegramApiException');
    } catch (TelegramApiException $e) {
        expect($e->getStatusCode())->toBe(429)
            ->and($e->isRateLimited())->toBeTrue()
            ->and($e->getRetryAfter())->toBe(30);
    }
});

it('rate limited exception exposes retry_after value', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Too Many Requests: retry after 15',
            'parameters' => ['retry_after' => 15],
        ], 429),
    ]);

    // Use the API directly without retry logic to test exception properties
    $api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    $exception = TelegramApiException::fromResponse(
        Http::post('https://api.telegram.org/bottest-token/sendMessage', ['chat_id' => '-100123', 'text' => 'test']),
        'sendMessage'
    );

    expect($exception->isRateLimited())->toBeTrue()
        ->and($exception->getRetryAfter())->toBe(15)
        ->and($exception->getStatusCode())->toBe(429);
});

// --- 500 Internal Server Error ---

it('throws TelegramApiException on 500 server error', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Internal Server Error',
        ], 500),
    ]);

    $this->telegram->sendMessage('-100123', 'Test');
})->throws(TelegramApiException::class, 'Internal Server Error');

// --- Connection / Timeout errors ---

it('handles connection error gracefully via callSilent', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Connection refused',
        ], 500),
    ]);

    $api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    $result = $api->callSilent('sendMessage', [
        'chat_id' => '-100123',
        'text' => 'test',
    ]);

    expect($result)->toBeFalse();
});

it('callSilent returns false on any error without throwing', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Gateway',
        ], 502),
    ]);

    $api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    $result = $api->callSilent('sendMessage', [
        'chat_id' => '-100123',
        'text' => 'test',
    ]);

    expect($result)->toBeFalse();
});

it('callSilent returns true on success', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ]),
    ]);

    $api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );

    $result = $api->callSilent('sendMessage', [
        'chat_id' => '-100123',
        'text' => 'test',
    ]);

    expect($result)->toBeTrue();
});

// --- Unknown error description ---

it('handles missing description in error response', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
        ], 400),
    ]);

    try {
        $this->telegram->sendMessage('-100123', 'Test');
    } catch (TelegramApiException $e) {
        expect($e->getTelegramDescription())->toBe('Unknown error');
    }
});

// --- Multiple sequential errors ---

it('each error creates a distinct exception', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'ok' => false,
                'description' => 'Forbidden: bot was kicked from the group chat',
            ], 403);
        }

        return Http::response([
            'ok' => false,
            'description' => 'Bad Request: chat not found',
        ], 400);
    });

    $exceptions = [];

    try {
        $this->telegram->sendMessage('-100123', 'first');
    } catch (TelegramApiException $e) {
        $exceptions[] = $e;
    }

    try {
        $this->telegram->sendMessage('-100456', 'second');
    } catch (TelegramApiException $e) {
        $exceptions[] = $e;
    }

    expect($exceptions)->toHaveCount(2)
        ->and($exceptions[0]->getStatusCode())->toBe(403)
        ->and($exceptions[1]->getStatusCode())->toBe(400)
        ->and($exceptions[0]->getTelegramDescription())->not->toBe($exceptions[1]->getTelegramDescription());
});
