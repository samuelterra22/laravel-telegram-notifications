<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Monolog\Level;
use Monolog\LogRecord;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Logging\TelegramHandler;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $this->api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
    );

    $this->handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Debug,
    );
});

it('sends log with empty message string without crash', function () {
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: '',
        context: [],
    );

    $this->handler->handle($record);

    Http::assertSentCount(1);

    Http::assertSent(fn ($request) => str_contains($request['text'], '<b>Message:</b>'));
});

it('escapes HTML entities in message', function () {
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Value is "100 > 50 & 200 < 300"',
        context: [],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], '&gt;')
            && str_contains($request['text'], '&lt;')
            && str_contains($request['text'], '&amp;')
            && str_contains($request['text'], '&quot;');
    });
});

it('formats exception with empty message without crash', function () {
    $exception = new RuntimeException('');

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'An error occurred',
        context: ['exception' => $exception],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], 'RuntimeException')
            && str_contains($request['text'], 'An error occurred');
    });
});

it('truncates log with very long exception message combined with context', function () {
    $longExceptionMessage = str_repeat('E', 5000);
    $exception = new RuntimeException($longExceptionMessage);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: str_repeat('M', 3000),
        context: ['exception' => $exception],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return mb_strlen($request['text']) <= 4096;
    });
});

it('sends clean output with no context and no exception', function () {
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Warning,
        message: 'Simple warning',
        context: [],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], 'Simple warning')
            && str_contains($request['text'], 'Warning')
            && ! str_contains($request['text'], 'Exception')
            && ! str_contains($request['text'], '<pre>');
    });
});

it('crashes when config app.name is null due to strict types', function () {
    // config('app.name', 'Laravel') returns null when config is explicitly set to null
    // because Laravel's config stores null, ignoring the default parameter.
    // With declare(strict_types=1), escapeHtml(null) throws TypeError.
    config()->set('app.name', null);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test',
        context: [],
    );

    $this->handler->handle($record);
})->throws(TypeError::class);

it('uses configured app name and env in log output', function () {
    config()->set('app.name', 'MyApp');
    config()->set('app.env', 'staging');

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test',
        context: [],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], 'MyApp')
            && str_contains($request['text'], 'staging');
    });
});
