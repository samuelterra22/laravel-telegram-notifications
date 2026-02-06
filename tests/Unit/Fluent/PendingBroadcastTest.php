<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Responses\TelegramResponse;
use SamuelTerra22\TelegramNotifications\Telegram;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ]),
    ]);

    $this->telegram = new Telegram(
        botsConfig: ['default' => ['token' => 'test-token']],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('broadcasts to multiple chats', function () {
    $responses = $this->telegram->broadcast(['-100001', '-100002', '-100003'])
        ->text('Announcement')
        ->send();

    expect($responses)->toHaveCount(3)
        ->and($responses[0])->toBeInstanceOf(TelegramResponse::class)
        ->and($responses[0]->ok())->toBeTrue();

    Http::assertSentCount(3);
});

it('adds chats via to() method', function () {
    $responses = $this->telegram->broadcast()
        ->to('-100001', '-100002')
        ->text('Hello')
        ->send();

    expect($responses)->toHaveCount(2);

    Http::assertSentCount(2);
});

it('combines constructor and to() chats', function () {
    $responses = $this->telegram->broadcast(['-100001'])
        ->to('-100002')
        ->text('Hello')
        ->send();

    expect($responses)->toHaveCount(2);

    Http::assertSentCount(2);
});

it('broadcasts with html content', function () {
    $this->telegram->broadcast(['-100001'])
        ->html('<b>Bold</b>')
        ->send();

    Http::assertSent(fn ($request) => $request['text'] === '<b>Bold</b>'
        && $request['parse_mode'] === 'HTML'
    );
});

it('broadcasts with markdown content', function () {
    $this->telegram->broadcast(['-100001'])
        ->markdown('*Bold*')
        ->send();

    Http::assertSent(fn ($request) => $request['parse_mode'] === 'MarkdownV2');
});

it('broadcasts silently', function () {
    $this->telegram->broadcast(['-100001'])
        ->text('Quiet')
        ->silent()
        ->send();

    Http::assertSent(fn ($request) => $request['disable_notification'] === true);
});

it('broadcasts with protect content', function () {
    $this->telegram->broadcast(['-100001'])
        ->text('Protected')
        ->protected()
        ->send();

    Http::assertSent(fn ($request) => $request['protect_content'] === true);
});

it('broadcasts with keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->callback('Click', 'data_1');

    $this->telegram->broadcast(['-100001'])
        ->text('Choose')
        ->keyboard($keyboard)
        ->send();

    Http::assertSent(function ($request) {
        return isset($request['reply_markup']);
    });
});

it('handles failures with onFailure callback', function () {
    // Use a separate URL prefix to avoid matching the beforeEach fake
    Http::fake([
        'api.fail.test/*' => Http::response(['ok' => false, 'description' => 'Bad Request'], 400),
    ]);

    $telegram = new Telegram(
        botsConfig: ['default' => ['token' => 'fail-token']],
        defaultBot: 'default',
        baseUrl: 'https://api.fail.test',
        timeout: 10,
    );

    $failures = [];

    $responses = $telegram->broadcast(['-100001', '-100002'])
        ->text('Fail')
        ->onFailure(function (string $chatId, Throwable $e) use (&$failures) {
            $failures[] = $chatId;
        })
        ->send();

    expect($failures)->toHaveCount(2)
        ->and($responses)->toHaveCount(2)
        ->and($responses[0]->ok())->toBeFalse();
});

it('returns empty array when no chats', function () {
    $responses = $this->telegram->broadcast([])
        ->text('No one')
        ->send();

    expect($responses)->toHaveCount(0);

    Http::assertNothingSent();
});

it('returns fluent self from all builder methods', function () {
    $broadcast = $this->telegram->broadcast();

    expect($broadcast->to('-100001'))->toBe($broadcast)
        ->and($broadcast->text('t'))->toBe($broadcast)
        ->and($broadcast->html('h'))->toBe($broadcast)
        ->and($broadcast->markdown('m'))->toBe($broadcast)
        ->and($broadcast->silent())->toBe($broadcast)
        ->and($broadcast->protected())->toBe($broadcast)
        ->and($broadcast->rateLimit(100))->toBe($broadcast)
        ->and($broadcast->onFailure(fn () => null))->toBe($broadcast);
});

it('rate limits broadcasts', function () {
    $start = microtime(true);

    $this->telegram->broadcast(['-100001', '-100002'])
        ->text('Slow')
        ->rateLimit(50)
        ->send();

    $elapsed = (microtime(true) - $start) * 1000;

    expect($elapsed)->toBeGreaterThan(40);
});
