<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramAnimation;

it('creates an animation message', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif');

    expect($message->getApiMethod())->toBe('sendAnimation');

    $array = $message->toArray();
    expect($array['chat_id'])->toBe('-100123')
        ->and($array['animation'])->toBe('https://example.com/animation.gif');
});

it('sets caption', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif')
        ->caption('Funny animation');

    $array = $message->toArray();

    expect($array['caption'])->toBe('Funny animation')
        ->and($array['parse_mode'])->toBe('HTML');
});

it('does not include parse_mode without caption', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('sets parse mode', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif')
        ->caption('*Bold*')
        ->parseMode(ParseMode::MarkdownV2);

    $array = $message->toArray();

    expect($array['parse_mode'])->toBe('MarkdownV2');
});

it('sets duration', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif')
        ->duration(15);

    $array = $message->toArray();

    expect($array['duration'])->toBe(15);
});

it('sets width and height', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif')
        ->width(320)
        ->height(240);

    $array = $message->toArray();

    expect($array['width'])->toBe(320)
        ->and($array['height'])->toBe(240);
});

it('sets thumbnail', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif')
        ->thumbnail('https://example.com/thumb.jpg');

    $array = $message->toArray();

    expect($array['thumbnail'])->toBe('https://example.com/thumb.jpg');
});

it('sets spoiler', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif')
        ->spoiler();

    $array = $message->toArray();

    expect($array['has_spoiler'])->toBeTrue();
});

it('sets keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->url('View', 'https://example.com');

    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray();
});

it('sets silent and protected', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/animation.gif')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets topic', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->topic('42')
        ->animation('https://example.com/animation.gif');

    $array = $message->toArray();

    expect($array['message_thread_id'])->toBe('42');
});

it('sets bot', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->bot('secondary')
        ->animation('https://example.com/animation.gif');

    expect($message->getBot())->toBe('secondary');
});
