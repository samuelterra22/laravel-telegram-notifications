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
        botsConfig: ['default' => ['token' => 'test-token']],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

// --- sendPhoto ---

it('sends a photo with caption', function () {
    $this->telegram->sendPhoto('-100123', 'https://example.com/photo.jpg', 'My photo');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPhoto')
        && $request['chat_id'] === '-100123'
        && $request['photo'] === 'https://example.com/photo.jpg'
        && $request['caption'] === 'My photo'
        && $request['parse_mode'] === 'HTML'
    );
});

it('sends a photo without caption', function () {
    $this->telegram->sendPhoto('-100123', 'https://example.com/photo.jpg');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPhoto')
        && $request['photo'] === 'https://example.com/photo.jpg'
        && ! isset($request['caption'])
    );
});

it('sends a photo with options', function () {
    $this->telegram->sendPhoto('-100123', 'photo_id', 'Caption', options: [
        'has_spoiler' => true,
    ]);

    Http::assertSent(fn ($request) => $request['has_spoiler'] === true);
});

// --- sendDocument ---

it('sends a document', function () {
    $this->telegram->sendDocument('-100123', 'https://example.com/file.pdf', 'A document');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendDocument')
        && $request['document'] === 'https://example.com/file.pdf'
        && $request['caption'] === 'A document'
    );
});

it('sends a document without caption', function () {
    $this->telegram->sendDocument('-100123', 'doc_file_id');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendDocument')
        && ! isset($request['caption'])
    );
});

// --- sendVideo ---

it('sends a video', function () {
    $this->telegram->sendVideo('-100123', 'https://example.com/video.mp4', 'A video');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendVideo')
        && $request['video'] === 'https://example.com/video.mp4'
        && $request['caption'] === 'A video'
    );
});

// --- sendAudio ---

it('sends an audio', function () {
    $this->telegram->sendAudio('-100123', 'https://example.com/audio.mp3', 'An audio');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendAudio')
        && $request['audio'] === 'https://example.com/audio.mp3'
        && $request['caption'] === 'An audio'
    );
});

// --- sendVoice ---

it('sends a voice', function () {
    $this->telegram->sendVoice('-100123', 'https://example.com/voice.ogg', 'Voice msg');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendVoice')
        && $request['voice'] === 'https://example.com/voice.ogg'
    );
});

// --- sendAnimation ---

it('sends an animation', function () {
    $this->telegram->sendAnimation('-100123', 'https://example.com/anim.gif', 'A gif');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendAnimation')
        && $request['animation'] === 'https://example.com/anim.gif'
        && $request['caption'] === 'A gif'
    );
});

// --- sendSticker ---

it('sends a sticker', function () {
    $this->telegram->sendSticker('-100123', 'sticker_file_id');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendSticker')
        && $request['sticker'] === 'sticker_file_id'
    );
});

it('sends a sticker with options', function () {
    $this->telegram->sendSticker('-100123', 'sticker_file_id', [
        'emoji' => '😀',
    ]);

    Http::assertSent(fn ($request) => $request['emoji'] === '😀');
});

// --- sendVideoNote ---

it('sends a video note', function () {
    $this->telegram->sendVideoNote('-100123', 'video_note_id');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendVideoNote')
        && $request['video_note'] === 'video_note_id'
    );
});

// --- sendLocation ---

it('sends a location', function () {
    $this->telegram->sendLocation('-100123', 40.7128, -74.0060);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendLocation')
        && $request['latitude'] === 40.7128
        && $request['longitude'] === -74.0060
    );
});

it('sends a location with options', function () {
    $this->telegram->sendLocation('-100123', 40.7128, -74.0060, [
        'live_period' => 3600,
    ]);

    Http::assertSent(fn ($request) => $request['live_period'] === 3600);
});

// --- sendVenue ---

it('sends a venue', function () {
    $this->telegram->sendVenue('-100123', 40.7128, -74.0060, 'Central Park', '59th St');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendVenue')
        && $request['title'] === 'Central Park'
        && $request['address'] === '59th St'
    );
});

// --- sendContact ---

it('sends a contact', function () {
    $this->telegram->sendContact('-100123', '+1234567890', 'John', 'Doe');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendContact')
        && $request['phone_number'] === '+1234567890'
        && $request['first_name'] === 'John'
        && $request['last_name'] === 'Doe'
    );
});

it('sends a contact without last name', function () {
    $this->telegram->sendContact('-100123', '+1234567890', 'John');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendContact')
        && $request['first_name'] === 'John'
        && ! isset($request['last_name'])
    );
});

// --- sendPoll ---

it('sends a poll', function () {
    $this->telegram->sendPoll('-100123', 'Favorite color?', ['Red', 'Blue', 'Green']);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPoll')
        && $request['question'] === 'Favorite color?'
        && $request['options'] === ['Red', 'Blue', 'Green']
    );
});

// --- sendDice ---

it('sends a dice', function () {
    $this->telegram->sendDice('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendDice')
        && $request['chat_id'] === '-100123'
    );
});

it('sends a dice with custom emoji', function () {
    $this->telegram->sendDice('-100123', '🎯');

    Http::assertSent(fn ($request) => $request['emoji'] === '🎯');
});

// --- sendMediaGroup ---

it('sends a media group', function () {
    $media = [
        ['type' => 'photo', 'media' => 'https://example.com/1.jpg'],
        ['type' => 'photo', 'media' => 'https://example.com/2.jpg'],
    ];

    $this->telegram->sendMediaGroup('-100123', $media);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMediaGroup')
        && count($request['media']) === 2
    );
});

// --- answerCallbackQuery ---

it('answers a callback query', function () {
    $this->telegram->answerCallbackQuery('callback_123', 'Done!');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/answerCallbackQuery')
        && $request['callback_query_id'] === 'callback_123'
        && $request['text'] === 'Done!'
    );
});

it('answers a callback query with alert', function () {
    $this->telegram->answerCallbackQuery('callback_123', 'Warning!', true);

    Http::assertSent(fn ($request) => $request['show_alert'] === true);
});

// --- answerInlineQuery ---

it('answers an inline query', function () {
    $results = [
        ['type' => 'article', 'id' => '1', 'title' => 'Test'],
    ];

    $this->telegram->answerInlineQuery('inline_123', $results);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/answerInlineQuery')
        && $request['inline_query_id'] === 'inline_123'
    );
});

// --- editMessageMedia ---

it('edits message media', function () {
    $media = ['type' => 'photo', 'media' => 'https://example.com/new.jpg'];

    $this->telegram->editMessageMedia('-100123', 456, $media);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageMedia')
        && $request['message_id'] === 456
    );
});

// --- Moderation ---

it('bans a chat member', function () {
    $this->telegram->banChatMember('-100123', 789);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/banChatMember')
        && $request['user_id'] === 789
    );
});

it('bans a chat member with options', function () {
    $this->telegram->banChatMember('-100123', 789, [
        'until_date' => 1700000000,
    ]);

    Http::assertSent(fn ($request) => $request['until_date'] === 1700000000);
});

it('unbans a chat member', function () {
    $this->telegram->unbanChatMember('-100123', 789);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/unbanChatMember')
        && $request['user_id'] === 789
    );
});

it('unbans a chat member with only_if_banned', function () {
    $this->telegram->unbanChatMember('-100123', 789, [
        'only_if_banned' => true,
    ]);

    Http::assertSent(fn ($request) => $request['only_if_banned'] === true);
});
