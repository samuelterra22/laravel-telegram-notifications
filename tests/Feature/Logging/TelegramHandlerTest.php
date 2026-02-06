<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Monolog\Level;
use Monolog\LogRecord;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Logging\TelegramHandler;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => true,
        ]),
    ]);

    $this->api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
    );
});

it('sends a log message to telegram', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(
        api: $api,
        chatId: '-100123',
        level: Level::Error,
    );

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Something went wrong',
        context: [],
    );

    $handler->handle($record);

    Http::assertSentCount(1);

    $recorded = Http::recorded();
    $request = $recorded[0][0];

    expect($request->url())->toContain('/sendMessage')
        ->and($request['chat_id'])->toBe('-100123')
        ->and($request['parse_mode'])->toBe('HTML')
        ->and($request['text'])->toContain('Something went wrong')
        ->and($request['text'])->toContain('Error');
});

it('includes emoji for each level', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Debug,
    );

    $levels = [
        Level::Debug,
        Level::Info,
        Level::Notice,
        Level::Warning,
        Level::Error,
        Level::Critical,
        Level::Alert,
        Level::Emergency,
    ];

    foreach ($levels as $level) {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
        ]);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: $level,
            message: 'Test',
            context: [],
        );

        $handler->handle($record);

        Http::assertSent(function ($request) use ($level) {
            return str_contains($request['text'], $level->name);
        });
    }
});

it('includes exception details in log', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Error,
    );

    $exception = new RuntimeException('Test exception');

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'An error occurred',
        context: ['exception' => $exception],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], 'RuntimeException')
            && str_contains($request['text'], 'An error occurred');
    });
});

it('sends to topic when configured', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        topicId: '99',
        level: Level::Error,
    );

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Error with topic',
        context: [],
    );

    $handler->handle($record);

    Http::assertSent(fn ($request) => $request['message_thread_id'] === '99');
});

it('respects minimum log level', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Error,
    );

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Debug,
        message: 'Debug message',
        context: [],
    );

    $handler->handle($record);

    Http::assertNothingSent();
});

it('includes app name and environment', function () {
    config()->set('app.name', 'TestApp');
    config()->set('app.env', 'testing');

    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Error,
    );

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test',
        context: [],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], 'TestApp')
            && str_contains($request['text'], 'testing');
    });
});

it('escapes HTML in messages', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Error,
    );

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: '<script>alert("xss")</script>',
        context: [],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        return ! str_contains($request['text'], '<script>')
            && str_contains($request['text'], '&lt;script&gt;');
    });
});

it('truncates very long stack traces', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Error,
    );

    $exception = new RuntimeException(str_repeat('Error message ', 500));

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Long error',
        context: ['exception' => $exception],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        return mb_strlen($request['text']) <= 4096;
    });
});

it('truncates trace exceeding 2000 characters', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Error,
    );

    // Create an exception with a very long trace by nesting calls
    $createDeepException = function () {
        $fn = function (int $depth) use (&$fn): \Throwable {
            if ($depth <= 0) {
                return new RuntimeException('Deep exception');
            }

            try {
                throw $fn($depth - 1);
            } catch (\Throwable $e) {
                return new RuntimeException('Level '.$depth, 0, $e);
            }
        };

        return $fn(100);
    };

    $exception = $createDeepException();

    // Ensure our trace is long enough
    expect(mb_strlen($exception->getTraceAsString()))->toBeGreaterThan(2000);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Deep error',
        context: ['exception' => $exception],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], '...')
            && mb_strlen($request['text']) <= 4096;
    });
});

it('truncates overall message exceeding 4096 characters', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Error,
    );

    $longMessage = str_repeat('A very long error message. ', 200);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: $longMessage,
        context: [],
    );

    $handler->handle($record);

    Http::assertSentCount(1);

    $recorded = Http::recorded();
    $request = $recorded[0][0];

    expect(mb_strlen($request['text']))->toBeLessThanOrEqual(4096)
        ->and($request['text'])->toEndWith('...</b>');
});

it('never throws on send failure', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => false], 500),
    ]);

    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        level: Level::Error,
    );

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test',
        context: [],
    );

    // Should not throw
    $handler->handle($record);

    expect(true)->toBeTrue();
});
