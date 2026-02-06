<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramVideo;

it('creates a video message', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4');

    expect($message->getApiMethod())->toBe('sendVideo');

    $array = $message->toArray();
    expect($array['video'])->toBe('https://example.com/video.mp4');
});

it('sets duration, width and height', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->duration(120)
        ->dimensions(1920, 1080);

    $array = $message->toArray();

    expect($array['duration'])->toBe(120)
        ->and($array['width'])->toBe(1920)
        ->and($array['height'])->toBe(1080);
});

it('sets spoiler and streaming', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->spoiler()
        ->streaming();

    $array = $message->toArray();

    expect($array['has_spoiler'])->toBeTrue()
        ->and($array['supports_streaming'])->toBeTrue();
});

it('sets caption', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->caption('Cool video');

    $array = $message->toArray();

    expect($array['caption'])->toBe('Cool video');
});

it('sets width individually', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->width(1280);

    $array = $message->toArray();

    expect($array['width'])->toBe(1280)
        ->and($array)->not->toHaveKey('height');
});

it('sets height individually', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->height(720);

    $array = $message->toArray();

    expect($array['height'])->toBe(720)
        ->and($array)->not->toHaveKey('width');
});

it('sets thumbnail', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->thumbnail('https://example.com/thumb.jpg');

    $array = $message->toArray();

    expect($array['thumbnail'])->toBe('https://example.com/thumb.jpg');
});

it('sets parse mode', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->caption('Test')
        ->parseMode(\SamuelTerra22\TelegramNotifications\Enums\ParseMode::MarkdownV2);

    $array = $message->toArray();

    expect($array['parse_mode'])->toBe('MarkdownV2');
});

it('sets keyboard', function () {
    $keyboard = \SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard::make()
        ->url('Click', 'https://example.com');

    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray()
        ->and($array['reply_markup'])->toHaveKey('inline_keyboard');
});

it('sets silent notification', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->silent();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue();
});

it('sets protected content', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->protected();

    $array = $message->toArray();

    expect($array['protect_content'])->toBeTrue();
});
