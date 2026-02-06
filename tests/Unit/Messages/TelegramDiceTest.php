<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramDice;

it('creates a dice message with default emoji', function () {
    $message = TelegramDice::create()
        ->to('-100123');

    expect($message->getApiMethod())->toBe('sendDice');

    $array = $message->toArray();
    expect($array['chat_id'])->toBe('-100123')
        ->and($array['emoji'])->toBe("\xF0\x9F\x8E\xB2");
});

it('sets dice emoji', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->dice();

    $array = $message->toArray();

    expect($array['emoji'])->toBe("\xF0\x9F\x8E\xB2");
});

it('sets darts emoji', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->darts();

    $array = $message->toArray();

    expect($array['emoji'])->toBe("\xF0\x9F\x8E\xAF");
});

it('sets basketball emoji', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->basketball();

    $array = $message->toArray();

    expect($array['emoji'])->toBe("\xF0\x9F\x8F\x80");
});

it('sets football emoji', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->football();

    $array = $message->toArray();

    expect($array['emoji'])->toBe("\xE2\x9A\xBD");
});

it('sets bowling emoji', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->bowling();

    $array = $message->toArray();

    expect($array['emoji'])->toBe("\xF0\x9F\x8E\xB3");
});

it('sets slot machine emoji', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->slotMachine();

    $array = $message->toArray();

    expect($array['emoji'])->toBe("\xF0\x9F\x8E\xB0");
});

it('sets custom emoji', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->emoji("\xF0\x9F\x8E\xAF");

    $array = $message->toArray();

    expect($array['emoji'])->toBe("\xF0\x9F\x8E\xAF");
});

it('sets keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Play Again', 'https://example.com');

    $message = TelegramDice::create()
        ->to('-100123')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray();
});

it('sets silent and protected', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets topic', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->topic('42');

    $array = $message->toArray();

    expect($array['message_thread_id'])->toBe('42');
});

it('sets bot', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->bot('secondary');

    expect($message->getBot())->toBe('secondary');
});
