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
            'result' => [
                'message_id' => 42,
                'date' => 1700000000,
                'text' => 'Hello World',
                'chat' => ['id' => -100123, 'type' => 'group'],
            ],
        ]),
    ]);

    $this->telegram = new Telegram(
        botsConfig: ['default' => ['token' => 'test-token']],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('sends a basic message via fluent builder', function () {
    $response = $this->telegram->message('-100123')
        ->text('Hello World')
        ->send();

    expect($response)->toBeInstanceOf(TelegramResponse::class)
        ->and($response->ok())->toBeTrue()
        ->and($response->messageId())->toBe(42);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
        && $request['text'] === 'Hello World'
        && $request['parse_mode'] === 'HTML'
    );
});

it('sends an HTML message', function () {
    $this->telegram->message('-100123')
        ->html('<b>Bold</b>')
        ->send();

    Http::assertSent(fn ($request) => $request['text'] === '<b>Bold</b>'
        && $request['parse_mode'] === 'HTML'
    );
});

it('sends a markdown message', function () {
    $this->telegram->message('-100123')
        ->markdown('*Bold*')
        ->send();

    Http::assertSent(fn ($request) => $request['text'] === '*Bold*'
        && $request['parse_mode'] === 'MarkdownV2'
    );
});

it('sends a silent message', function () {
    $this->telegram->message('-100123')
        ->text('Quiet')
        ->silent()
        ->send();

    Http::assertSent(fn ($request) => $request['disable_notification'] === true);
});

it('sends a protected message', function () {
    $this->telegram->message('-100123')
        ->text('No forward')
        ->protected()
        ->send();

    Http::assertSent(fn ($request) => $request['protect_content'] === true);
});

it('disables web page preview', function () {
    $this->telegram->message('-100123')
        ->text('https://example.com')
        ->disablePreview()
        ->send();

    Http::assertSent(fn ($request) => $request['disable_web_page_preview'] === true);
});

it('sends with reply markup keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->callback('Click', 'data_1');

    $this->telegram->message('-100123')
        ->text('Choose')
        ->keyboard($keyboard)
        ->send();

    Http::assertSent(function ($request) {
        $markup = json_decode($request['reply_markup'], true);

        return $markup['inline_keyboard'][0][0]['text'] === 'Click';
    });
});

it('sends a reply to a message', function () {
    $this->telegram->message('-100123')
        ->text('Reply')
        ->replyTo(99)
        ->send();

    Http::assertSent(fn ($request) => $request['reply_to_message_id'] === 99);
});

it('sends to a topic', function () {
    $this->telegram->message('-100123')
        ->text('In topic')
        ->topic('42')
        ->send();

    Http::assertSent(fn ($request) => $request['message_thread_id'] === '42');
});

it('skips sending when sendWhen is false', function () {
    $response = $this->telegram->message('-100123')
        ->text('Skip this')
        ->sendWhen(false)
        ->send();

    expect($response->ok())->toBeTrue()
        ->and($response->messageId())->toBeNull();

    Http::assertNothingSent();
});

it('sends when sendWhen is true', function () {
    $this->telegram->message('-100123')
        ->text('Send this')
        ->sendWhen(true)
        ->send();

    Http::assertSent(fn ($request) => $request['text'] === 'Send this');
});

it('chains all options together', function () {
    $keyboard = InlineKeyboard::make()->callback('OK', 'ok');

    $this->telegram->message('-100123')
        ->html('<b>Important</b>')
        ->silent()
        ->protected()
        ->disablePreview()
        ->keyboard($keyboard)
        ->replyTo(10)
        ->topic('5')
        ->send();

    Http::assertSent(function ($request) {
        return $request['text'] === '<b>Important</b>'
            && $request['parse_mode'] === 'HTML'
            && $request['disable_notification'] === true
            && $request['protect_content'] === true
            && $request['disable_web_page_preview'] === true
            && $request['reply_to_message_id'] === 10
            && $request['message_thread_id'] === '5'
            && isset($request['reply_markup']);
    });
});

it('returns fluent self from all builder methods', function () {
    $msg = $this->telegram->message('-100123');

    expect($msg->text('t'))->toBe($msg)
        ->and($msg->html('h'))->toBe($msg)
        ->and($msg->markdown('m'))->toBe($msg)
        ->and($msg->silent())->toBe($msg)
        ->and($msg->protected())->toBe($msg)
        ->and($msg->disablePreview())->toBe($msg)
        ->and($msg->replyTo(1))->toBe($msg)
        ->and($msg->topic('1'))->toBe($msg)
        ->and($msg->sendWhen(true))->toBe($msg);
});
