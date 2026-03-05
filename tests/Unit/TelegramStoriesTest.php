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

it('posts a story with content', function () {
    $content = ['type' => 'photo', 'photo' => 'https://example.com/photo.jpg'];

    $this->telegram->postStory('-100123', $content);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/postStory')
        && $request['chat_id'] === '-100123'
        && $request['content'] === $content
    );
});

it('posts a story with active period', function () {
    $content = ['type' => 'photo', 'photo' => 'https://example.com/photo.jpg'];

    $this->telegram->postStory('-100123', $content, activePeriod: 86400);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/postStory')
        && $request['active_period'] === 86400
    );
});

it('posts a story with caption', function () {
    $content = ['type' => 'photo', 'photo' => 'https://example.com/photo.jpg'];

    $this->telegram->postStory('-100123', $content, caption: '<b>Hello</b>');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/postStory')
        && $request['caption'] === '<b>Hello</b>'
        && $request['parse_mode'] === 'HTML'
    );
});

it('does not include parse_mode without caption', function () {
    $content = ['type' => 'photo', 'photo' => 'https://example.com/photo.jpg'];

    $this->telegram->postStory('-100123', $content);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/postStory')
        && ! isset($request['parse_mode'])
    );
});

it('edits a story', function () {
    $content = ['type' => 'photo', 'photo' => 'https://example.com/new-photo.jpg'];

    $this->telegram->editStory('-100123', 42, $content);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editStory')
        && $request['chat_id'] === '-100123'
        && $request['story_id'] === 42
        && $request['content'] === $content
    );
});

it('edits story with caption', function () {
    $content = ['type' => 'photo', 'photo' => 'https://example.com/photo.jpg'];

    $this->telegram->editStory('-100123', 42, $content, caption: '<i>Updated</i>');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editStory')
        && $request['caption'] === '<i>Updated</i>'
        && $request['parse_mode'] === 'HTML'
    );
});

it('deletes a story', function () {
    $this->telegram->deleteStory('-100123', 42);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteStory')
        && $request['chat_id'] === '-100123'
        && $request['story_id'] === 42
    );
});

it('deletes multiple stories', function () {
    $this->telegram->deleteStories('-100123', [1, 2, 3]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteStories')
        && $request['chat_id'] === '-100123'
        && $request['story_ids'] === [1, 2, 3]
    );
});

it('passes options through', function () {
    $content = ['type' => 'photo', 'photo' => 'https://example.com/photo.jpg'];

    $this->telegram->postStory('-100123', $content, options: [
        'protect_content' => true,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/postStory')
        && $request['protect_content'] === true
    );
});
