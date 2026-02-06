<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Telegram;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => true,
        ]),
    ]);

    $this->telegram = new Telegram(
        botsConfig: ['default' => ['token' => 'test-token']],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('sends typing action', function () {
    $this->telegram->typing('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChatAction')
        && $request['action'] === 'typing'
    );
});

it('sends uploading photo action', function () {
    $this->telegram->uploadingPhoto('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChatAction')
        && $request['action'] === 'upload_photo'
    );
});

it('sends uploading document action', function () {
    $this->telegram->uploadingDocument('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChatAction')
        && $request['action'] === 'upload_document'
    );
});

it('sends recording video action', function () {
    $this->telegram->recordingVideo('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChatAction')
        && $request['action'] === 'record_video'
    );
});

it('sends recording voice action', function () {
    $this->telegram->recordingVoice('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChatAction')
        && $request['action'] === 'record_voice'
    );
});
