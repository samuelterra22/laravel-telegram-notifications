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
        botsConfig: [
            'default' => ['token' => 'default-token'],
            'alerts' => ['token' => 'alerts-token'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('sets profile photo', function () {
    $this->telegram->setMyProfilePhoto('photo-file-id');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMyProfilePhoto')
        && $request['photo'] === 'photo-file-id'
    );
});

it('sets personal profile photo', function () {
    $this->telegram->setMyProfilePhoto('photo-file-id', isPersonal: true);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMyProfilePhoto')
        && $request['photo'] === 'photo-file-id'
        && $request['is_personal'] === true
    );
});

it('removes profile photo', function () {
    $this->telegram->removeMyProfilePhoto();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/removeMyProfilePhoto'));
});

it('removes personal profile photo', function () {
    $this->telegram->removeMyProfilePhoto(isPersonal: true);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/removeMyProfilePhoto')
        && $request['is_personal'] === true
    );
});

it('does not include is_personal when false', function () {
    $this->telegram->setMyProfilePhoto('photo-file-id', isPersonal: false);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMyProfilePhoto')
        && ! array_key_exists('is_personal', $request->data())
    );
});
