<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramLocation;

it('creates a location message', function () {
    $message = TelegramLocation::create()
        ->to('-100123')
        ->latitude(-23.5505)
        ->longitude(-46.6333);

    expect($message->getApiMethod())->toBe('sendLocation');

    $array = $message->toArray();
    expect($array['latitude'])->toBe(-23.5505)
        ->and($array['longitude'])->toBe(-46.6333);
});

it('sets coordinates via convenience method', function () {
    $message = TelegramLocation::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333);

    $array = $message->toArray();
    expect($array['latitude'])->toBe(-23.5505)
        ->and($array['longitude'])->toBe(-46.6333);
});

it('sets live period', function () {
    $message = TelegramLocation::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->livePeriod(3600);

    $array = $message->toArray();
    expect($array['live_period'])->toBe(3600);
});

it('sets horizontal accuracy and heading', function () {
    $message = TelegramLocation::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->horizontalAccuracy(10.5)
        ->heading(180)
        ->proximityAlertRadius(100);

    $array = $message->toArray();

    expect($array['horizontal_accuracy'])->toBe(10.5)
        ->and($array['heading'])->toBe(180)
        ->and($array['proximity_alert_radius'])->toBe(100);
});

it('sets silent and protected', function () {
    $message = TelegramLocation::create()
        ->to('-100123')
        ->coordinates(0.0, 0.0)
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets keyboard', function () {
    $keyboard = \SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard::make()
        ->url('Open in Maps', 'https://maps.google.com');

    $message = TelegramLocation::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray()
        ->and($array['reply_markup'])->toHaveKey('inline_keyboard');
});
