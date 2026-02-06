<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVoice;

it('creates a voice message', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->voice('https://example.com/voice.ogg');

    expect($message->getApiMethod())->toBe('sendVoice');

    $array = $message->toArray();
    expect($array['chat_id'])->toBe('-100123')
        ->and($array['voice'])->toBe('https://example.com/voice.ogg');
});

it('sets caption', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->voice('https://example.com/voice.ogg')
        ->caption('Voice note');

    $array = $message->toArray();

    expect($array['caption'])->toBe('Voice note')
        ->and($array['parse_mode'])->toBe('HTML');
});

it('does not include parse_mode without caption', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->voice('https://example.com/voice.ogg');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('sets parse mode', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->voice('https://example.com/voice.ogg')
        ->caption('*Bold*')
        ->parseMode(ParseMode::MarkdownV2);

    $array = $message->toArray();

    expect($array['parse_mode'])->toBe('MarkdownV2');
});

it('sets duration', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->voice('https://example.com/voice.ogg')
        ->duration(30);

    $array = $message->toArray();

    expect($array['duration'])->toBe(30);
});

it('sets keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Reply', 'https://example.com');

    $message = TelegramVoice::create()
        ->to('-100123')
        ->voice('https://example.com/voice.ogg')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray();
});

it('sets silent and protected', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->voice('https://example.com/voice.ogg')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets topic', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->topic('42')
        ->voice('https://example.com/voice.ogg');

    $array = $message->toArray();

    expect($array['message_thread_id'])->toBe('42');
});

it('sets bot', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->bot('secondary')
        ->voice('https://example.com/voice.ogg');

    expect($message->getBot())->toBe('secondary');
});
