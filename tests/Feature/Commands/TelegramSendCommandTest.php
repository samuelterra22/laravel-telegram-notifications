<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('sends a message via CLI', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 123],
        ]),
    ]);

    $this->artisan('telegram:send', ['message' => 'Hello World', '--chat' => '-1001234'])
        ->assertSuccessful()
        ->expectsOutputToContain('Message sent');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
        && $request['text'] === 'Hello World'
        && $request['chat_id'] === '-1001234'
    );
});

it('uses default chat_id from config when --chat not provided', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 456],
        ]),
    ]);

    $this->artisan('telegram:send', ['message' => 'Hello'])
        ->assertSuccessful()
        ->expectsOutputToContain('Message sent');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
        && $request['chat_id'] === '-1001234567890'
    );
});

it('fails without chat_id', function () {
    config()->set('telegram-notifications.bots.default.chat_id', null);

    $this->artisan('telegram:send', ['message' => 'Hello'])
        ->assertFailed()
        ->expectsOutputToContain('No chat ID provided');
});

it('handles API error', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: chat not found',
        ], 400),
    ]);

    $this->artisan('telegram:send', ['message' => 'Hello', '--chat' => '-1001234'])
        ->assertFailed()
        ->expectsOutputToContain('Failed');
});

it('sends silent message', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 789],
        ]),
    ]);

    $this->artisan('telegram:send', ['message' => 'Quiet', '--chat' => '-1001234', '--silent' => true])
        ->assertSuccessful();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
        && $request['disable_notification'] === true
    );
});

it('sends to specific topic', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 101],
        ]),
    ]);

    $this->artisan('telegram:send', ['message' => 'Topic msg', '--chat' => '-1001234', '--topic' => '99'])
        ->assertSuccessful();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
        && $request['message_thread_id'] === '99'
    );
});

it('uses specific bot', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 202],
        ]),
    ]);

    $this->artisan('telegram:send', ['message' => 'Alert!', '--bot' => 'alerts', '--chat' => '-1009876543210'])
        ->assertSuccessful();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'alerts-token-456'));
});
