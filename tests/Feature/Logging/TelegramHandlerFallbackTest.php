<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Monolog\Level;
use Monolog\LogRecord;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Logging\TelegramHandler;

/*
|--------------------------------------------------------------------------
| Fix 1: write() falls back to plain text when HTML send fails
|--------------------------------------------------------------------------
*/

it('retries with plain text when HTML send fails', function () {
    $callCount = 0;
    Http::fake(function ($request) use (&$callCount) {
        $callCount++;

        // First call (HTML) fails, second call (plain text) succeeds
        if ($callCount === 1) {
            return Http::response([
                'ok' => false,
                'description' => "Bad Request: can't parse entities",
            ], 400);
        }

        return Http::response(['ok' => true, 'result' => true]);
    });

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Something went wrong',
        context: [],
    );

    $handler->handle($record);

    expect($callCount)->toBe(2);

    $recorded = Http::recorded();

    // First call has parse_mode=HTML
    expect($recorded[0][0]['parse_mode'])->toBe('HTML')
        ->and($recorded[0][0]['text'])->toContain('<b>');

    // Second call has no parse_mode and no HTML tags
    expect($recorded[1][0]['text'])->not->toContain('<b>')
        ->and($recorded[1][0]['text'])->not->toContain('</b>')
        ->and(array_key_exists('parse_mode', $recorded[1][0]->data()))->toBeFalse();
});

it('does not retry when HTML send succeeds', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test message',
        context: [],
    );

    $handler->handle($record);

    Http::assertSentCount(1);
});

it('preserves topic_id in the plain text fallback call', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response(['ok' => false, 'description' => 'Bad Request'], 400);
        }

        return Http::response(['ok' => true, 'result' => true]);
    });

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', topicId: '42', level: Level::Error);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Error with topic',
        context: [],
    );

    $handler->handle($record);

    $recorded = Http::recorded();

    // Both calls should include message_thread_id
    expect($recorded[0][0]['message_thread_id'])->toBe('42')
        ->and($recorded[1][0]['message_thread_id'])->toBe('42');
});

it('preserves chat_id in the plain text fallback call', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response(['ok' => false, 'description' => 'Bad Request'], 400);
        }

        return Http::response(['ok' => true, 'result' => true]);
    });

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100999', level: Level::Error);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test',
        context: [],
    );

    $handler->handle($record);

    $recorded = Http::recorded();

    expect($recorded[1][0]['chat_id'])->toBe('-100999');
});

it('strips all HTML tags from the fallback message', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response(['ok' => false, 'description' => 'parse error'], 400);
        }

        return Http::response(['ok' => true, 'result' => true]);
    });

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    $exception = new RuntimeException('Test exception');
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Error occurred',
        context: ['exception' => $exception],
    );

    $handler->handle($record);

    $recorded = Http::recorded();
    $fallbackText = $recorded[1][0]['text'];

    // No HTML tags at all in the fallback
    expect($fallbackText)->not->toContain('<b>')
        ->and($fallbackText)->not->toContain('</b>')
        ->and($fallbackText)->not->toContain('<pre>')
        ->and($fallbackText)->not->toContain('</pre>')
        // But still contains the actual log content
        ->and($fallbackText)->toContain('Error occurred')
        ->and($fallbackText)->toContain('Error');
});

it('never throws even when both HTML and plain text calls fail', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Server error'], 500),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test',
        context: [],
    );

    // Should not throw even when both attempts fail
    $handler->handle($record);

    Http::assertSentCount(2);
});

/*
|--------------------------------------------------------------------------
| Fix 2: Safe HTML truncation closes unclosed tags
|--------------------------------------------------------------------------
*/

it('closes unclosed <pre> tag when truncation cuts inside trace block', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    // Create an exception with a trace long enough to push total message > 4096
    // and have the truncation cut inside the <pre> block
    $exception = new RuntimeException(str_repeat('E', 2000));

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: str_repeat('M', 1500),
        context: ['exception' => $exception],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        $text = $request['text'];

        // Message should be within Telegram's limit
        expect(mb_strlen($text))->toBeLessThanOrEqual(4096);

        // If <pre> was opened but not closed by truncation, the suffix should close it
        $openPre = substr_count($text, '<pre>');
        $closePre = substr_count($text, '</pre>');

        return $openPre === $closePre;
    });
});

it('closes unclosed <b> tag when truncation cuts inside bold text', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    // Long message that pushes past 4096, truncation should land where <b> tags are balanced
    $longMessage = str_repeat('X', 5000);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: $longMessage,
        context: [],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        $text = $request['text'];

        expect(mb_strlen($text))->toBeLessThanOrEqual(4096);

        $openBold = substr_count($text, '<b>');
        $closeBold = substr_count($text, '</b>');

        return $openBold === $closeBold;
    });
});

it('produces valid HTML after truncation with exception context', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    // Create a message that will definitely exceed 4096 chars when formatted
    $exception = new RuntimeException(str_repeat('Exception message ', 300));

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: str_repeat('Log message ', 200),
        context: ['exception' => $exception],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        $text = $request['text'];

        expect(mb_strlen($text))->toBeLessThanOrEqual(4096);

        // All tags must be balanced
        $openPre = substr_count($text, '<pre>');
        $closePre = substr_count($text, '</pre>');
        $openBold = substr_count($text, '<b>');
        $closeBold = substr_count($text, '</b>');

        return $openPre === $closePre && $openBold === $closeBold;
    });
});

it('does not add unnecessary closing tags when truncation happens outside HTML tags', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    // Long plain message with no exception — all <b> tags are self-closing in the header
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: str_repeat('A', 5000),
        context: [],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        $text = $request['text'];

        // Should end with just '...' (no stray closing tags)
        return str_ends_with($text, '...')
            && ! str_ends_with($text, '...</b>')
            && ! str_ends_with($text, '...</pre>');
    });
});

it('does not truncate messages exactly at 4096 characters', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    // First determine the overhead
    $emptyRecord = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: '',
        context: [],
    );

    $handler->handle($emptyRecord);

    $overhead = 0;
    Http::assertSent(function ($request) use (&$overhead) {
        $overhead = mb_strlen($request['text']);

        return true;
    });

    // Create a message that makes the total exactly 4096
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $messageLen = 4096 - $overhead;
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: str_repeat('Z', $messageLen),
        context: [],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        return mb_strlen($request['text']) === 4096
            && ! str_contains($request['text'], '...');
    });
});

it('truncated message never exceeds 4096 characters regardless of open tags', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $api = new TelegramBotApi(token: 'test-token', baseUrl: 'https://api.telegram.org');
    $handler = new TelegramHandler(api: $api, chatId: '-100123', level: Level::Error);

    // Very long message that forces truncation
    $exception = new RuntimeException(str_repeat('X', 10000));

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: str_repeat('Y', 10000),
        context: ['exception' => $exception],
    );

    $handler->handle($record);

    Http::assertSent(function ($request) {
        return mb_strlen($request['text']) <= 4096;
    });
});

/*
|--------------------------------------------------------------------------
| Fix 3: CreateTelegramLogger returns NullHandler when disabled
|--------------------------------------------------------------------------
*/

it('returns NullHandler when enabled is false in logging config', function () {
    config()->set('telegram-notifications.logging.enabled', false);

    $factory = new \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;
    $logger = $factory(['level' => 'error']);

    expect($logger)->toBeInstanceOf(\Monolog\Logger::class)
        ->and($logger->getName())->toBe('telegram')
        ->and($logger->getHandlers())->toHaveCount(1)
        ->and($logger->getHandlers()[0])->toBeInstanceOf(\Monolog\Handler\NullHandler::class);
});

it('returns NullHandler when enabled is false in channel config', function () {
    config()->set('telegram-notifications.logging.enabled', true);

    $factory = new \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;
    $logger = $factory(['level' => 'error', 'enabled' => false]);

    expect($logger)->toBeInstanceOf(\Monolog\Logger::class)
        ->and($logger->getHandlers()[0])->toBeInstanceOf(\Monolog\Handler\NullHandler::class);
});

it('returns NullHandler when no enabled key is set anywhere', function () {
    config()->set('telegram-notifications.logging.enabled', null);

    $factory = new \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;
    $logger = $factory(['level' => 'error']);

    expect($logger->getHandlers()[0])->toBeInstanceOf(\Monolog\Handler\NullHandler::class);
});

it('returns real TelegramHandler when enabled is true in logging config', function () {
    config()->set('telegram-notifications.logging.enabled', true);

    $factory = new \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;
    $logger = $factory(['level' => 'error']);

    expect($logger->getHandlers()[0])->toBeInstanceOf(TelegramHandler::class);
});

it('returns real TelegramHandler when enabled is true in channel config', function () {
    config()->set('telegram-notifications.logging.enabled', false);

    $factory = new \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;
    $logger = $factory(['level' => 'error', 'enabled' => true]);

    expect($logger->getHandlers()[0])->toBeInstanceOf(TelegramHandler::class);
});

it('channel config enabled overrides logging config enabled', function () {
    // logging config says enabled, but channel config says disabled
    config()->set('telegram-notifications.logging.enabled', true);

    $factory = new \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;
    $logger = $factory(['level' => 'error', 'enabled' => false]);

    expect($logger->getHandlers()[0])->toBeInstanceOf(\Monolog\Handler\NullHandler::class);
});

it('NullHandler logger does not send any HTTP requests', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    config()->set('telegram-notifications.logging.enabled', false);

    $factory = new \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;
    $logger = $factory(['level' => 'error']);

    $logger->error('This should not be sent to Telegram');

    Http::assertNothingSent();
});
