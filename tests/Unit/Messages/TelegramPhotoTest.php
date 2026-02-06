<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPhoto;

it('creates a photo message', function () {
    $message = TelegramPhoto::create()
        ->to('-100123')
        ->photo('https://example.com/image.jpg');

    expect($message->getApiMethod())->toBe('sendPhoto');

    $array = $message->toArray();
    expect($array['chat_id'])->toBe('-100123')
        ->and($array['photo'])->toBe('https://example.com/image.jpg');
});

it('sets caption', function () {
    $message = TelegramPhoto::create()
        ->to('-100123')
        ->photo('https://example.com/image.jpg')
        ->caption('A beautiful photo');

    $array = $message->toArray();

    expect($array['caption'])->toBe('A beautiful photo')
        ->and($array['parse_mode'])->toBe('HTML');
});

it('does not include parse_mode without caption', function () {
    $message = TelegramPhoto::create()
        ->to('-100123')
        ->photo('https://example.com/image.jpg');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('sets parse mode', function () {
    $message = TelegramPhoto::create()
        ->to('-100123')
        ->photo('https://example.com/image.jpg')
        ->caption('*Bold*')
        ->parseMode(ParseMode::MarkdownV2);

    $array = $message->toArray();

    expect($array['parse_mode'])->toBe('MarkdownV2');
});

it('sets spoiler', function () {
    $message = TelegramPhoto::create()
        ->to('-100123')
        ->photo('https://example.com/image.jpg')
        ->spoiler();

    $array = $message->toArray();

    expect($array['has_spoiler'])->toBeTrue();
});

it('sets keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->url('View', 'https://example.com');

    $message = TelegramPhoto::create()
        ->to('-100123')
        ->photo('https://example.com/image.jpg')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray();
});

it('sets silent and protected', function () {
    $message = TelegramPhoto::create()
        ->to('-100123')
        ->photo('https://example.com/image.jpg')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets topic', function () {
    $message = TelegramPhoto::create()
        ->to('-100123')
        ->topic('42')
        ->photo('https://example.com/image.jpg');

    $array = $message->toArray();

    expect($array['message_thread_id'])->toBe('42');
});
