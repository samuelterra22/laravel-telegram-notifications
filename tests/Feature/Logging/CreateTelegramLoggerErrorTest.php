<?php

declare(strict_types=1);

use Monolog\Logger;
use SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger;

it('throws for invalid level name', function () {
    $factory = new CreateTelegramLogger;

    $factory(['level' => 'invalid']);
})->throws(\UnhandledMatchError::class);

it('throws InvalidArgumentException for invalid bot name in logging config', function () {
    config()->set('telegram-notifications.logging.bot', 'nonexistent_bot');

    $factory = new CreateTelegramLogger;

    $factory(['level' => 'error']);
})->throws(InvalidArgumentException::class, 'Bot [nonexistent_bot] not configured.');

it('handles empty chat_id gracefully', function () {
    config()->set('telegram-notifications.logging.chat_id', '');

    $factory = new CreateTelegramLogger;

    $logger = $factory(['level' => 'error']);

    expect($logger)->toBeInstanceOf(Logger::class);
});

it('uses defaults when logging config section is null', function () {
    config()->set('telegram-notifications.logging', null);

    $factory = new CreateTelegramLogger;

    // Bot name defaults to 'default', which is configured in TestCase
    $logger = $factory(['level' => 'error']);

    expect($logger)->toBeInstanceOf(Logger::class);
});

it('accepts custom level from config', function () {
    $factory = new CreateTelegramLogger;

    $logger = $factory(['level' => 'debug']);

    expect($logger)->toBeInstanceOf(Logger::class)
        ->and($logger->getHandlers())->toHaveCount(1);
});

it('chat_id from $config parameter overrides logging config', function () {
    config()->set('telegram-notifications.logging.chat_id', '-100LOGGING');

    $factory = new CreateTelegramLogger;

    $logger = $factory(['level' => 'error', 'chat_id' => '-100CONFIG']);

    expect($logger)->toBeInstanceOf(Logger::class);

    // The handler should use the $config chat_id, not the logging config one
    // We verify by sending a log and checking the request
    \Illuminate\Support\Facades\Http::fake([
        'api.telegram.org/*' => \Illuminate\Support\Facades\Http::response(['ok' => true, 'result' => true]),
    ]);

    $logger->error('Test');

    \Illuminate\Support\Facades\Http::assertSent(fn ($request) => $request['chat_id'] === '-100CONFIG');
});

it('topic_id from $config parameter overrides logging config', function () {
    config()->set('telegram-notifications.logging.topic_id', '99');

    $factory = new CreateTelegramLogger;

    $logger = $factory(['level' => 'error', 'topic_id' => '77']);

    \Illuminate\Support\Facades\Http::fake([
        'api.telegram.org/*' => \Illuminate\Support\Facades\Http::response(['ok' => true, 'result' => true]),
    ]);

    $logger->error('Test');

    \Illuminate\Support\Facades\Http::assertSent(fn ($request) => $request['message_thread_id'] === '77');
});

it('null topic_id results in null topicId on handler', function () {
    config()->set('telegram-notifications.logging.topic_id', null);

    $factory = new CreateTelegramLogger;

    $logger = $factory(['level' => 'error']);

    \Illuminate\Support\Facades\Http::fake([
        'api.telegram.org/*' => \Illuminate\Support\Facades\Http::response(['ok' => true, 'result' => true]),
    ]);

    $logger->error('Test');

    \Illuminate\Support\Facades\Http::assertSent(function ($request) {
        // message_thread_id should not be present when topicId is null
        return ! array_key_exists('message_thread_id', $request->data());
    });
});

it('non-null topic_id is cast to string', function () {
    config()->set('telegram-notifications.logging.topic_id', 42);

    $factory = new CreateTelegramLogger;

    $logger = $factory(['level' => 'error']);

    \Illuminate\Support\Facades\Http::fake([
        'api.telegram.org/*' => \Illuminate\Support\Facades\Http::response(['ok' => true, 'result' => true]),
    ]);

    $logger->error('Test');

    \Illuminate\Support\Facades\Http::assertSent(fn ($request) => $request['message_thread_id'] === '42');
});
