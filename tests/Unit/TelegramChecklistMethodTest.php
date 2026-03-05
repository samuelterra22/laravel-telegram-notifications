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

it('sends checklist via Telegram service', function () {
    $checklist = [
        ['text' => 'Buy groceries', 'checked' => false],
        ['text' => 'Walk the dog', 'checked' => true],
    ];

    $this->telegram->sendChecklist('-100123', 'Daily Tasks', $checklist);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChecklist')
        && $request['chat_id'] === '-100123'
        && $request['title'] === 'Daily Tasks'
        && $request['checklist'] === $checklist
    );
});

it('sends checklist with options', function () {
    $checklist = [
        ['text' => 'Task 1', 'checked' => false],
    ];

    $this->telegram->sendChecklist('-100123', 'Tasks', $checklist, options: [
        'disable_notification' => true,
        'protect_content' => true,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChecklist')
        && $request['disable_notification'] === true
        && $request['protect_content'] === true
    );
});
