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

it('sends gift with user_id and gift_id', function () {
    $this->telegram->sendGift(
        userId: 123456,
        giftId: 'gift-abc',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendGift')
        && $request['user_id'] === 123456
        && $request['gift_id'] === 'gift-abc'
    );
});

it('sends gift with text message', function () {
    $this->telegram->sendGift(
        userId: 123456,
        giftId: 'gift-abc',
        text: 'Happy birthday!',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendGift')
        && $request['text'] === 'Happy birthday!'
    );
});

it('sends gift with text and parse mode', function () {
    $this->telegram->sendGift(
        userId: 123456,
        giftId: 'gift-abc',
        text: '<b>Congratulations!</b>',
        textParseMode: 'HTML',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendGift')
        && $request['text'] === '<b>Congratulations!</b>'
        && $request['text_parse_mode'] === 'HTML'
    );
});

it('does not include text_parse_mode when null', function () {
    $this->telegram->sendGift(
        userId: 123456,
        giftId: 'gift-abc',
        text: 'A gift for you',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendGift')
        && $request['text'] === 'A gift for you'
        && ! array_key_exists('text_parse_mode', $request->data())
    );
});

it('gets available gifts', function () {
    $this->telegram->getAvailableGifts();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getAvailableGifts'));
});

it('passes options through', function () {
    $this->telegram->getAvailableGifts(options: ['extra' => 'value']);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getAvailableGifts')
        && $request['extra'] === 'value'
    );
});
