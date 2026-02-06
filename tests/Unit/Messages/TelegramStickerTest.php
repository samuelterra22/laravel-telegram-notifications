<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramSticker;

it('creates a sticker message', function () {
    $message = TelegramSticker::create()
        ->to('-100123')
        ->sticker('CAACAgIAAxkBAAI');

    expect($message->getApiMethod())->toBe('sendSticker');

    $array = $message->toArray();
    expect($array['chat_id'])->toBe('-100123')
        ->and($array['sticker'])->toBe('CAACAgIAAxkBAAI');
});

it('sets sticker via url', function () {
    $message = TelegramSticker::create()
        ->to('-100123')
        ->sticker('https://example.com/sticker.webp');

    $array = $message->toArray();

    expect($array['sticker'])->toBe('https://example.com/sticker.webp');
});

it('sets emoji', function () {
    $message = TelegramSticker::create()
        ->to('-100123')
        ->sticker('CAACAgIAAxkBAAI')
        ->emoji("\xF0\x9F\x98\x80");

    $array = $message->toArray();

    expect($array['emoji'])->toBe("\xF0\x9F\x98\x80");
});

it('does not include emoji when not set', function () {
    $message = TelegramSticker::create()
        ->to('-100123')
        ->sticker('CAACAgIAAxkBAAI');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('emoji');
});

it('sets keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->url('More Stickers', 'https://example.com');

    $message = TelegramSticker::create()
        ->to('-100123')
        ->sticker('CAACAgIAAxkBAAI')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray();
});

it('sets silent and protected', function () {
    $message = TelegramSticker::create()
        ->to('-100123')
        ->sticker('CAACAgIAAxkBAAI')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets topic', function () {
    $message = TelegramSticker::create()
        ->to('-100123')
        ->topic('42')
        ->sticker('CAACAgIAAxkBAAI');

    $array = $message->toArray();

    expect($array['message_thread_id'])->toBe('42');
});

it('sets bot', function () {
    $message = TelegramSticker::create()
        ->to('-100123')
        ->bot('secondary')
        ->sticker('CAACAgIAAxkBAAI');

    expect($message->getBot())->toBe('secondary');
});
