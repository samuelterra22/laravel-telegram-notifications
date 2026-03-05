<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramAnimation;
use SamuelTerra22\TelegramNotifications\Messages\TelegramAudio;
use SamuelTerra22\TelegramNotifications\Messages\TelegramContact;
use SamuelTerra22\TelegramNotifications\Messages\TelegramDice;
use SamuelTerra22\TelegramNotifications\Messages\TelegramDocument;
use SamuelTerra22\TelegramNotifications\Messages\TelegramLocation;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPhoto;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPoll;
use SamuelTerra22\TelegramNotifications\Messages\TelegramSticker;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVenue;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVideo;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVoice;

dataset('message_types_with_effect', function () {
    return [
        'TelegramMessage' => [fn () => TelegramMessage::create('Hello')->to('123')],
        'TelegramPhoto' => [fn () => TelegramPhoto::create()->photo('https://example.com/photo.jpg')->to('123')],
        'TelegramDocument' => [fn () => TelegramDocument::create()->document('https://example.com/doc.pdf')->to('123')],
        'TelegramVideo' => [fn () => TelegramVideo::create()->video('https://example.com/video.mp4')->to('123')],
        'TelegramAudio' => [fn () => TelegramAudio::create()->audio('https://example.com/audio.mp3')->to('123')],
        'TelegramVoice' => [fn () => TelegramVoice::create()->voice('https://example.com/voice.ogg')->to('123')],
        'TelegramAnimation' => [fn () => TelegramAnimation::create()->animation('https://example.com/anim.gif')->to('123')],
        'TelegramSticker' => [fn () => TelegramSticker::create()->sticker('sticker_id')->to('123')],
        'TelegramLocation' => [fn () => TelegramLocation::create()->coordinates(40.0, -74.0)->to('123')],
        'TelegramContact' => [fn () => TelegramContact::create()->phoneNumber('+1234567890')->firstName('John')->to('123')],
        'TelegramVenue' => [fn () => TelegramVenue::create()->coordinates(40.0, -74.0)->title('Place')->address('123 St')->to('123')],
        'TelegramPoll' => [fn () => TelegramPoll::create()->question('Q?')->options(['A', 'B'])->to('123')],
        'TelegramDice' => [fn () => TelegramDice::create()->to('123')],
    ];
});

it('sets message_effect_id in toArray when effect() is called', function (Closure $factory) {
    $message = $factory();
    $message->effect('5104841245755180586');

    $array = $message->toArray();

    expect($array)->toHaveKey('message_effect_id', '5104841245755180586');
})->with('message_types_with_effect');

it('does not include message_effect_id in toArray when not set', function (Closure $factory) {
    $message = $factory();

    $array = $message->toArray();

    expect($array)->not->toHaveKey('message_effect_id');
})->with('message_types_with_effect');

it('returns the effect id from getMessageEffectId()', function () {
    $message = TelegramMessage::create('Hello');
    expect($message->getMessageEffectId())->toBeNull();

    $message->effect('5104841245755180586');
    expect($message->getMessageEffectId())->toBe('5104841245755180586');
});

it('works with other params like photo + effect + caption', function () {
    $photo = TelegramPhoto::create()
        ->photo('https://example.com/photo.jpg')
        ->caption('A nice photo')
        ->effect('5104841245755180586')
        ->to('123');

    $array = $photo->toArray();

    expect($array)
        ->toHaveKey('photo', 'https://example.com/photo.jpg')
        ->toHaveKey('caption', 'A nice photo')
        ->toHaveKey('message_effect_id', '5104841245755180586')
        ->toHaveKey('chat_id', '123');
});
