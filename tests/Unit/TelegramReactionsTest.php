<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Telegram;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ]),
    ]);

    $this->telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'default-token'],
            'alerts' => ['token' => 'alerts-token'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('sets emoji reaction', function () {
    $reaction = [['type' => 'emoji', 'emoji' => "\xF0\x9F\x91\x8D"]];

    $this->telegram->setMessageReaction('-1001234', 42, $reaction);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMessageReaction')
        && $request['chat_id'] === '-1001234'
        && $request['message_id'] === 42
        && $request['reaction'] === $reaction
    );
});

it('sets big reaction', function () {
    $reaction = [['type' => 'emoji', 'emoji' => "\xF0\x9F\x91\x8D"]];

    $this->telegram->setMessageReaction('-1001234', 42, $reaction, isBig: true);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMessageReaction')
        && $request['is_big'] === true
    );
});

it('does not include is_big when false', function () {
    $reaction = [['type' => 'emoji', 'emoji' => "\xF0\x9F\x91\x8D"]];

    $this->telegram->setMessageReaction('-1001234', 42, $reaction, isBig: false);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMessageReaction')
        && ! array_key_exists('is_big', $request->data())
    );
});

it('sets custom emoji reaction', function () {
    $reaction = [['type' => 'custom_emoji', 'custom_emoji_id' => '5368324170671202286']];

    $this->telegram->setMessageReaction('-1001234', 42, $reaction);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMessageReaction')
        && $request['reaction'] === $reaction
    );
});

it('passes additional options', function () {
    $reaction = [['type' => 'emoji', 'emoji' => "\xF0\x9F\x91\x8D"]];

    $this->telegram->setMessageReaction('-1001234', 42, $reaction, options: ['extra' => 'value']);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMessageReaction')
        && $request['extra'] === 'value'
    );
});
