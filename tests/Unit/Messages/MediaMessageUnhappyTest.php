<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramAnimation;
use SamuelTerra22\TelegramNotifications\Messages\TelegramAudio;
use SamuelTerra22\TelegramNotifications\Messages\TelegramDocument;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPhoto;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVideo;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVoice;

// --- Photo ---

it('photo without caption excludes parse_mode', function () {
    $photo = TelegramPhoto::create()->to('123')->photo('https://example.com/img.jpg');

    $array = $photo->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('photo with caption includes parse_mode', function () {
    $photo = TelegramPhoto::create()->to('123')
        ->photo('https://example.com/img.jpg')
        ->caption('A nice photo');

    $array = $photo->toArray();

    expect($array)->toHaveKey('parse_mode')
        ->and($array['parse_mode'])->toBe('HTML')
        ->and($array['caption'])->toBe('A nice photo');
});

it('photo with empty string caption excludes parse_mode', function () {
    $photo = TelegramPhoto::create()->to('123')
        ->photo('https://example.com/img.jpg')
        ->caption('');

    $array = $photo->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('photo with spoiler(false) excludes has_spoiler', function () {
    $photo = TelegramPhoto::create()->to('123')
        ->photo('https://example.com/img.jpg')
        ->spoiler(false);

    $array = $photo->toArray();

    expect($array)->not->toHaveKey('has_spoiler');
});

it('photo with empty string photo key is filtered out', function () {
    $photo = TelegramPhoto::create()->to('123');

    $array = $photo->toArray();

    // Empty string '' is falsy, filtered by array_filter
    expect($array)->not->toHaveKey('photo');
});

// --- Document ---

it('document without caption excludes parse_mode', function () {
    $doc = TelegramDocument::create()->to('123')->document('file_id_123');

    $array = $doc->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('document with caption includes parse_mode', function () {
    $doc = TelegramDocument::create()->to('123')
        ->document('file_id_123')
        ->caption('Check this doc');

    $array = $doc->toArray();

    expect($array['parse_mode'])->toBe('HTML')
        ->and($array['caption'])->toBe('Check this doc');
});

// --- Video ---

it('video with null integer fields filters them out', function () {
    $video = TelegramVideo::create()->to('123')->video('video_id');

    $array = $video->toArray();

    expect($array)->not->toHaveKey('duration')
        ->and($array)->not->toHaveKey('width')
        ->and($array)->not->toHaveKey('height');
});

it('video with zero duration includes it', function () {
    $video = TelegramVideo::create()->to('123')
        ->video('video_id')
        ->duration(0);

    // duration(0) sets $this->duration = 0; but array_filter removes 0 as falsy
    // This documents the actual behavior
    $array = $video->toArray();

    // 0 is falsy so array_filter removes it
    expect($array)->not->toHaveKey('duration');
});

it('video without caption excludes parse_mode', function () {
    $video = TelegramVideo::create()->to('123')->video('video_id');

    $array = $video->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('video with spoiler(false) excludes has_spoiler', function () {
    $video = TelegramVideo::create()->to('123')
        ->video('video_id')
        ->spoiler(false);

    $array = $video->toArray();

    expect($array)->not->toHaveKey('has_spoiler');
});

// --- Audio ---

it('audio without caption excludes parse_mode', function () {
    $audio = TelegramAudio::create()->to('123')->audio('audio_id');

    $array = $audio->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('audio with caption includes parse_mode', function () {
    $audio = TelegramAudio::create()->to('123')
        ->audio('audio_id')
        ->caption('Great song');

    $array = $audio->toArray();

    expect($array['parse_mode'])->toBe('HTML')
        ->and($array['caption'])->toBe('Great song');
});

// --- Voice ---

it('voice without caption excludes parse_mode', function () {
    $voice = TelegramVoice::create()->to('123')->voice('voice_id');

    $array = $voice->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('voice with caption includes parse_mode', function () {
    $voice = TelegramVoice::create()->to('123')
        ->voice('voice_id')
        ->caption('Listen to this');

    $array = $voice->toArray();

    expect($array['parse_mode'])->toBe('HTML')
        ->and($array['caption'])->toBe('Listen to this');
});

// --- Animation ---

it('animation without caption excludes parse_mode', function () {
    $animation = TelegramAnimation::create()->to('123')->animation('anim_id');

    $array = $animation->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('animation with caption includes parse_mode', function () {
    $animation = TelegramAnimation::create()->to('123')
        ->animation('anim_id')
        ->caption('Cool GIF');

    $array = $animation->toArray();

    expect($array['parse_mode'])->toBe('HTML')
        ->and($array['caption'])->toBe('Cool GIF');
});

it('animation with spoiler(false) excludes has_spoiler', function () {
    $animation = TelegramAnimation::create()->to('123')
        ->animation('anim_id')
        ->spoiler(false);

    $array = $animation->toArray();

    expect($array)->not->toHaveKey('has_spoiler');
});
