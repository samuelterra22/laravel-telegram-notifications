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

it('approves suggested post', function () {
    $this->telegram->approveSuggestedPost('-1001234', 42);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/approveSuggestedPost')
        && $request['chat_id'] === '-1001234'
        && $request['message_id'] === 42
    );
});

it('declines suggested post', function () {
    $this->telegram->declineSuggestedPost('-1001234', 42);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/declineSuggestedPost')
        && $request['chat_id'] === '-1001234'
        && $request['message_id'] === 42
    );
});

it('passes options through on approve', function () {
    $this->telegram->approveSuggestedPost('-1001234', 42, ['extra' => 'value']);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/approveSuggestedPost')
        && $request['extra'] === 'value'
    );
});

it('passes options through on decline', function () {
    $this->telegram->declineSuggestedPost('-1001234', 42, ['extra' => 'value']);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/declineSuggestedPost')
        && $request['extra'] === 'value'
    );
});
