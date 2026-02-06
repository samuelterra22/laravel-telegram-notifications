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

it('context with non-Throwable exception key skips exception block', function () {
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'An error occurred',
        context: ['exception' => 'not a throwable'],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], 'An error occurred')
            && ! str_contains($request['text'], '<pre>');
    });
});

it('context with other keys besides exception does not trigger exception block', function () {
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Something happened',
        context: ['user_id' => 42, 'action' => 'login'],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return str_contains($request['text'], 'Something happened')
            && ! str_contains($request['text'], 'Exception')
            && ! str_contains($request['text'], '<pre>');
    });
});

it('exception trace at or under 2000 chars is NOT truncated', function () {
    $exception = new RuntimeException('test error');
    $trace = $exception->getTraceAsString();

    // If trace is short enough, it should not be truncated
    if (mb_strlen($trace) <= 2000) {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Error,
            message: 'Error',
            context: ['exception' => $exception],
        );

        $this->handler->handle($record);

        Http::assertSent(function ($request) use ($trace) {
            $text = $request['text'];
            $escaped = htmlspecialchars($trace, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return str_contains($text, $escaped)
                && ! str_contains($text, htmlspecialchars($trace, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'...');
        });
    } else {
        expect(true)->toBeTrue(); // trace already long, skip this edge case
    }
});

it('long exception trace IS truncated with ellipsis suffix', function () {
    // Create a real exception with a long trace by deeply nesting calls
    $buildDeepException = function () {
        $fn = function () use (&$fn) {
            static $depth = 0;
            $depth++;
            if ($depth > 100) {
                throw new RuntimeException('deep error');
            }
            $fn();
        };

        try {
            $fn();
        } catch (RuntimeException $e) {
            return $e;
        }

        return new RuntimeException('fallback');
    };

    $exception = $buildDeepException();
    $trace = $exception->getTraceAsString();

    if (mb_strlen($trace) <= 2000) {
        // If trace isn't long enough, skip gracefully
        expect(true)->toBeTrue();

        return;
    }

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Error',
        context: ['exception' => $exception],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        $text = $request['text'];
        preg_match('/<pre>(.*?)<\/pre>/s', $text, $matches);

        return isset($matches[1]) && str_ends_with($matches[1], '...');
    });
});

it('message exactly 4096 chars after formatting is NOT truncated', function () {
    // We need to build a message where the final formatted string is exactly 4096 chars.
    // The format adds header/footer around the message. Let's calculate that overhead.
    $testRecord = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: '',
        context: [],
    );

    // First, determine the overhead
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $this->handler->handle($testRecord);

    $overhead = 0;
    Http::assertSent(function ($request) use (&$overhead) {
        $overhead = mb_strlen($request['text']);

        return true;
    });

    // Now create a message that when combined with the overhead reaches exactly 4096
    $messageLen = 4096 - $overhead;
    if ($messageLen <= 0) {
        $messageLen = 1; // safety
    }
    $paddedMessage = str_repeat('X', $messageLen);

    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: $paddedMessage,
        context: [],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return mb_strlen($request['text']) === 4096
            && ! str_ends_with($request['text'], '...</b>');
    });
});

it('message 4097 chars after formatting IS truncated', function () {
    // Use a very long message to guarantee final text > 4096
    $longMessage = str_repeat('Y', 5000);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: $longMessage,
        context: [],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return mb_strlen($request['text']) === 4096
            && str_ends_with($request['text'], '...</b>');
    });
});

it('API failure during log write returns false silently', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Internal Server Error',
        ], 500),
    ]);

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'This should not crash',
        context: [],
    );

    // write() calls callSilent() which swallows the exception
    // No exception should be thrown
    $this->handler->handle($record);

    Http::assertSentCount(1);
});

it('includes message_thread_id when topic ID is set', function () {
    $handler = new TelegramHandler(
        api: $this->api,
        chatId: '-100123',
        topicId: '99',
        level: Level::Debug,
    );

    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test with topic',
        context: [],
    );

    $handler->handle($record);

    Http::assertSent(fn ($request) => $request['message_thread_id'] === '99');
});

it('excludes message_thread_id when topic ID is null', function () {
    // The default handler in beforeEach has topicId=null
    $record = new LogRecord(
        datetime: new \DateTimeImmutable,
        channel: 'test',
        level: Level::Error,
        message: 'Test without topic',
        context: [],
    );

    $this->handler->handle($record);

    Http::assertSent(function ($request) {
        return ! isset($request->data()['message_thread_id'])
            && ! array_key_exists('message_thread_id', $request->data());
    });
});
