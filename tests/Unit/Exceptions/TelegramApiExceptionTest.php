<?php

declare(strict_types=1);

use Illuminate\Http\Client\Response;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

it('uses "Unknown error" when response body has no description field', function () {
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('json')->andReturn(['ok' => false]);
    $response->shouldReceive('status')->andReturn(400);

    $exception = TelegramApiException::fromResponse($response, 'sendMessage');

    expect($exception->getTelegramDescription())->toBe('Unknown error')
        ->and($exception->getMessage())->toBe('Telegram API error [sendMessage]: Unknown error');
});

it('returns null retryAfter when 429 response has no parameters key', function () {
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('json')->andReturn([
        'ok' => false,
        'description' => 'Too Many Requests',
    ]);
    $response->shouldReceive('status')->andReturn(429);

    $exception = TelegramApiException::fromResponse($response, 'sendMessage');

    expect($exception->getRetryAfter())->toBeNull()
        ->and($exception->isRateLimited())->toBeTrue();
});

it('returns null retryAfter when 429 response has parameters but no retry_after', function () {
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('json')->andReturn([
        'ok' => false,
        'description' => 'Too Many Requests',
        'parameters' => ['migrate_to_chat_id' => -100123],
    ]);
    $response->shouldReceive('status')->andReturn(429);

    $exception = TelegramApiException::fromResponse($response, 'sendMessage');

    expect($exception->getRetryAfter())->toBeNull()
        ->and($exception->isRateLimited())->toBeTrue();
});

it('isRateLimited returns false for non-429 status codes', function (int $statusCode) {
    $exception = new TelegramApiException(
        message: 'test',
        statusCode: $statusCode,
        apiMethod: 'sendMessage',
    );

    expect($exception->isRateLimited())->toBeFalse();
})->with([400, 401, 403, 404, 500, 502, 503]);

it('getters return values passed to constructor', function () {
    $exception = new TelegramApiException(
        message: 'Telegram API error [sendPhoto]: Bad Request',
        statusCode: 400,
        apiMethod: 'sendPhoto',
        telegramDescription: 'Bad Request',
        retryAfter: 30,
    );

    expect($exception->getStatusCode())->toBe(400)
        ->and($exception->getApiMethod())->toBe('sendPhoto')
        ->and($exception->getTelegramDescription())->toBe('Bad Request')
        ->and($exception->getRetryAfter())->toBe(30)
        ->and($exception->getCode())->toBe(400)
        ->and($exception->getMessage())->toBe('Telegram API error [sendPhoto]: Bad Request');
});

it('formats exception message with method and description', function () {
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('json')->andReturn([
        'ok' => false,
        'description' => 'Forbidden: bot was blocked by the user',
    ]);
    $response->shouldReceive('status')->andReturn(403);

    $exception = TelegramApiException::fromResponse($response, 'getChat');

    expect($exception->getMessage())
        ->toBe('Telegram API error [getChat]: Forbidden: bot was blocked by the user');
});
