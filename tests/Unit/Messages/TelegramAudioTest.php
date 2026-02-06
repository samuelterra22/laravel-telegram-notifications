<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramAudio;

it('creates an audio message', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3');

    expect($message->getApiMethod())->toBe('sendAudio');

    $array = $message->toArray();
    expect($array['chat_id'])->toBe('-100123')
        ->and($array['audio'])->toBe('https://example.com/song.mp3');
});

it('sets caption', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->caption('Great song');

    $array = $message->toArray();

    expect($array['caption'])->toBe('Great song')
        ->and($array['parse_mode'])->toBe('HTML');
});

it('does not include parse_mode without caption', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('sets parse mode', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->caption('*Bold*')
        ->parseMode(ParseMode::MarkdownV2);

    $array = $message->toArray();

    expect($array['parse_mode'])->toBe('MarkdownV2');
});

it('sets duration', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->duration(240);

    $array = $message->toArray();

    expect($array['duration'])->toBe(240);
});

it('sets performer', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->performer('Artist Name');

    $array = $message->toArray();

    expect($array['performer'])->toBe('Artist Name');
});

it('sets title', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->title('Song Title');

    $array = $message->toArray();

    expect($array['title'])->toBe('Song Title');
});

it('sets thumbnail', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->thumbnail('https://example.com/cover.jpg');

    $array = $message->toArray();

    expect($array['thumbnail'])->toBe('https://example.com/cover.jpg');
});

it('sets all audio metadata', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->caption('Best track')
        ->duration(180)
        ->performer('DJ Bot')
        ->title('Telegram Beats');

    $array = $message->toArray();

    expect($array['caption'])->toBe('Best track')
        ->and($array['duration'])->toBe(180)
        ->and($array['performer'])->toBe('DJ Bot')
        ->and($array['title'])->toBe('Telegram Beats');
});

it('sets keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Listen', 'https://example.com');

    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray();
});

it('sets silent and protected', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/song.mp3')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets topic', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->topic('42')
        ->audio('https://example.com/song.mp3');

    $array = $message->toArray();

    expect($array['message_thread_id'])->toBe('42');
});

it('sets bot', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->bot('secondary')
        ->audio('https://example.com/song.mp3');

    expect($message->getBot())->toBe('secondary');
});
