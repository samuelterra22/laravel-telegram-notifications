<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMediaGroup;

it('creates a media group with photos', function () {
    $group = TelegramMediaGroup::create()
        ->to('123')
        ->photo('https://example.com/photo1.jpg', 'First photo')
        ->photo('https://example.com/photo2.jpg', 'Second photo');

    $array = $group->toArray();

    expect($array['media'])->toHaveCount(2)
        ->and($array['media'][0]['type'])->toBe('photo')
        ->and($array['media'][0]['media'])->toBe('https://example.com/photo1.jpg')
        ->and($array['media'][0]['caption'])->toBe('First photo')
        ->and($array['media'][1]['type'])->toBe('photo')
        ->and($array['media'][1]['caption'])->toBe('Second photo');
});

it('creates a mixed media group', function () {
    $group = TelegramMediaGroup::create()
        ->to('123')
        ->photo('https://example.com/photo.jpg', 'Photo')
        ->video('https://example.com/video.mp4', 'Video')
        ->document('https://example.com/doc.pdf', 'Document');

    $array = $group->toArray();

    expect($array['media'])->toHaveCount(3)
        ->and($array['media'][0]['type'])->toBe('photo')
        ->and($array['media'][1]['type'])->toBe('video')
        ->and($array['media'][2]['type'])->toBe('document');
});

it('supports silent and protected', function () {
    $group = TelegramMediaGroup::create()
        ->to('123')
        ->photo('https://example.com/photo.jpg')
        ->silent()
        ->protected();

    $array = $group->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('supports message effect', function () {
    $group = TelegramMediaGroup::create()
        ->to('123')
        ->photo('https://example.com/photo.jpg')
        ->effect('5104841245755180586');

    $array = $group->toArray();

    expect($array['message_effect_id'])->toBe('5104841245755180586');
});

it('supports topic', function () {
    $group = TelegramMediaGroup::create()
        ->to('123')
        ->topic('42')
        ->photo('https://example.com/photo.jpg');

    $array = $group->toArray();

    expect($array['message_thread_id'])->toBe('42');
});

it('does not include parse_mode without caption', function () {
    $group = TelegramMediaGroup::create()
        ->to('123')
        ->photo('https://example.com/photo.jpg');

    $array = $group->toArray();

    expect($array['media'][0])->not->toHaveKey('parse_mode')
        ->and($array['media'][0])->not->toHaveKey('caption');
});

it('returns sendMediaGroup as api method', function () {
    $group = TelegramMediaGroup::create();

    expect($group->getApiMethod())->toBe('sendMediaGroup');
});

it('handles media with different parse modes', function () {
    $group = TelegramMediaGroup::create()
        ->to('123')
        ->photo('https://example.com/photo1.jpg', 'HTML caption')
        ->photo('https://example.com/photo2.jpg', '*Bold*', ParseMode::MarkdownV2);

    $array = $group->toArray();

    expect($array['media'][0]['parse_mode'])->toBe('HTML')
        ->and($array['media'][1]['parse_mode'])->toBe('MarkdownV2');
});

it('supports audio media type', function () {
    $group = TelegramMediaGroup::create()
        ->to('123')
        ->audio('https://example.com/audio.mp3', 'Audio file');

    $array = $group->toArray();

    expect($array['media'][0]['type'])->toBe('audio')
        ->and($array['media'][0]['caption'])->toBe('Audio file');
});

it('creates via static factory', function () {
    $group = TelegramMediaGroup::create();

    expect($group)->toBeInstanceOf(TelegramMediaGroup::class);
});
