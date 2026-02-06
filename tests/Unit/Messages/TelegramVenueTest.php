<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVenue;

it('creates a venue message', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->latitude(-23.5505)
        ->longitude(-46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578');

    expect($message->getApiMethod())->toBe('sendVenue');

    $array = $message->toArray();
    expect($array['chat_id'])->toBe('-100123')
        ->and($array['latitude'])->toBe(-23.5505)
        ->and($array['longitude'])->toBe(-46.6333)
        ->and($array['title'])->toBe('MASP')
        ->and($array['address'])->toBe('Av. Paulista, 1578');
});

it('sets coordinates via convenience method', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578');

    $array = $message->toArray();

    expect($array['latitude'])->toBe(-23.5505)
        ->and($array['longitude'])->toBe(-46.6333);
});

it('sets foursquare id and type', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578')
        ->foursquareId('4b7e56c0f964a520a0602fe3')
        ->foursquareType('arts_entertainment/museum');

    $array = $message->toArray();

    expect($array['foursquare_id'])->toBe('4b7e56c0f964a520a0602fe3')
        ->and($array['foursquare_type'])->toBe('arts_entertainment/museum');
});

it('does not include foursquare fields when not set', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('foursquare_id')
        ->and($array)->not->toHaveKey('foursquare_type');
});

it('sets google place id and type', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578')
        ->googlePlaceId('ChIJN1t_tDeuEmsRUsoyG83frY4')
        ->googlePlaceType('museum');

    $array = $message->toArray();

    expect($array['google_place_id'])->toBe('ChIJN1t_tDeuEmsRUsoyG83frY4')
        ->and($array['google_place_type'])->toBe('museum');
});

it('does not include google place fields when not set', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('google_place_id')
        ->and($array)->not->toHaveKey('google_place_type');
});

it('sets keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Directions', 'https://maps.google.com');

    $message = TelegramVenue::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray();
});

it('sets silent and protected', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets topic', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->topic('42')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578');

    $array = $message->toArray();

    expect($array['message_thread_id'])->toBe('42');
});

it('sets bot', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->bot('secondary')
        ->coordinates(-23.5505, -46.6333)
        ->title('MASP')
        ->address('Av. Paulista, 1578');

    expect($message->getBot())->toBe('secondary');
});
