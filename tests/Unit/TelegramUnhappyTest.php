<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Telegram;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

    $this->telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'default-token-123'],
            'alerts' => ['token' => 'alerts-token-456'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('bot(null) uses default bot name', function () {
    $bot1 = $this->telegram->bot(null);
    $bot2 = $this->telegram->bot('default');

    expect($bot1)->toBe($bot2)
        ->and($bot1->getToken())->toBe('default-token-123');
});

it('bot with empty string throws InvalidArgumentException', function () {
    $this->telegram->bot('');
})->throws(InvalidArgumentException::class, 'Bot [] not configured.');

it('lazy loading returns same instance on second call', function () {
    $first = $this->telegram->bot('default');
    $second = $this->telegram->bot('default');

    expect($first)->toBe($second);
});

it('sendMessage with null parseMode filters it out', function () {
    $this->telegram->sendMessage('-100123', 'Hello', null);

    Http::assertSent(function ($request) {
        return $request['text'] === 'Hello'
            && ! array_key_exists('parse_mode', $request->data());
    });
});

it('sendMessage with null topicId filters it out', function () {
    $this->telegram->sendMessage('-100123', 'Hello', 'HTML', null);

    Http::assertSent(function ($request) {
        return $request['text'] === 'Hello'
            && ! array_key_exists('message_thread_id', $request->data());
    });
});

it('pinChatMessage with disableNotification=false filters it out', function () {
    $this->telegram->pinChatMessage('-100123', 42, false);

    Http::assertSent(function ($request) {
        return $request['message_id'] === 42
            && ! array_key_exists('disable_notification', $request->data());
    });
});

it('pinChatMessage with disableNotification=true includes it', function () {
    $this->telegram->pinChatMessage('-100123', 42, true);

    Http::assertSent(function ($request) {
        return $request['message_id'] === 42
            && $request['disable_notification'] === true;
    });
});

it('unpinChatMessage with null messageId filters it out', function () {
    $this->telegram->unpinChatMessage('-100123', null);

    Http::assertSent(function ($request) {
        return $request['chat_id'] === '-100123'
            && ! array_key_exists('message_id', $request->data());
    });
});

it('deleteWebhook with dropPendingUpdates=false includes it', function () {
    $this->telegram->deleteWebhook(false);

    Http::assertSent(function ($request) {
        // deleteWebhook does NOT use array_filter, so false is sent
        return array_key_exists('drop_pending_updates', $request->data());
    });
});
