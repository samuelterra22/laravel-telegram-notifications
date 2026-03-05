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

it('sends paid media with photo', function () {
    $media = [['type' => 'photo', 'media' => 'https://example.com/photo.jpg']];

    $this->telegram->sendPaidMedia(
        chatId: '-1001234',
        starCount: 50,
        media: $media,
        caption: 'Premium photo',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPaidMedia')
        && $request['chat_id'] === '-1001234'
        && $request['star_count'] === 50
        && $request['media'] === $media
        && $request['caption'] === 'Premium photo'
        && $request['parse_mode'] === 'HTML'
    );
});

it('sends paid media without caption and no parse_mode', function () {
    $media = [['type' => 'photo', 'media' => 'https://example.com/photo.jpg']];

    $this->telegram->sendPaidMedia(
        chatId: '-1001234',
        starCount: 25,
        media: $media,
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPaidMedia')
        && $request['chat_id'] === '-1001234'
        && $request['star_count'] === 25
        && ! array_key_exists('caption', $request->data())
        && ! array_key_exists('parse_mode', $request->data())
    );
});

it('sends paid media with caption and custom parse_mode', function () {
    $media = [['type' => 'video', 'media' => 'https://example.com/video.mp4']];

    $this->telegram->sendPaidMedia(
        chatId: '-1001234',
        starCount: 100,
        media: $media,
        caption: '*Bold caption*',
        parseMode: 'Markdown',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPaidMedia')
        && $request['caption'] === '*Bold caption*'
        && $request['parse_mode'] === 'Markdown'
    );
});

it('sends paid media with reply_markup option', function () {
    $media = [['type' => 'photo', 'media' => 'https://example.com/photo.jpg']];
    $markup = ['inline_keyboard' => [[['text' => 'Buy', 'callback_data' => 'buy']]]];

    $this->telegram->sendPaidMedia(
        chatId: '-1001234',
        starCount: 50,
        media: $media,
        caption: 'Premium',
        options: ['reply_markup' => $markup],
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPaidMedia')
        && $request['reply_markup'] === json_encode($markup)
    );
});
