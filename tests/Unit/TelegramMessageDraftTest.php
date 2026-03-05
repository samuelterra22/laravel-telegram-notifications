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

it('sends message draft', function () {
    $this->telegram->sendMessageDraft('-1001234', 'Draft text');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessageDraft')
        && $request['chat_id'] === '-1001234'
        && $request['text'] === 'Draft text'
        && $request['parse_mode'] === 'HTML'
    );
});

it('sends with business connection id', function () {
    $this->telegram->sendMessageDraft('-1001234', 'Draft text', businessConnectionId: 'biz-123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessageDraft')
        && $request['business_connection_id'] === 'biz-123'
    );
});

it('sends without parse mode', function () {
    $this->telegram->sendMessageDraft('-1001234', 'Draft text', parseMode: null);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessageDraft')
        && ! array_key_exists('parse_mode', $request->data())
    );
});

it('does not include business_connection_id when null', function () {
    $this->telegram->sendMessageDraft('-1001234', 'Draft text');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessageDraft')
        && ! array_key_exists('business_connection_id', $request->data())
    );
});
