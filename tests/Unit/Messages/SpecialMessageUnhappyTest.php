<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramContact;
use SamuelTerra22\TelegramNotifications\Messages\TelegramDice;
use SamuelTerra22\TelegramNotifications\Messages\TelegramLocation;
use SamuelTerra22\TelegramNotifications\Messages\TelegramSticker;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVenue;

// --- Location ---

it('location with negative coordinates includes them', function () {
    $location = TelegramLocation::create()
        ->to('123')
        ->coordinates(-33.8688, -151.2093);

    $array = $location->toArray();

    expect($array['latitude'])->toBe(-33.8688)
        ->and($array['longitude'])->toBe(-151.2093);
});

it('location with all optional fields includes them all', function () {
    $location = TelegramLocation::create()
        ->to('123')
        ->coordinates(40.7128, -74.0060)
        ->horizontalAccuracy(10.5)
        ->livePeriod(3600)
        ->heading(90)
        ->proximityAlertRadius(500);

    $array = $location->toArray();

    expect($array['horizontal_accuracy'])->toBe(10.5)
        ->and($array['live_period'])->toBe(3600)
        ->and($array['heading'])->toBe(90)
        ->and($array['proximity_alert_radius'])->toBe(500);
});

it('location with keyboard includes reply_markup', function () {
    $keyboard = InlineKeyboard::make()->url('Map', 'https://maps.google.com');

    $location = TelegramLocation::create()
        ->to('123')
        ->coordinates(0.0, 0.0)
        ->keyboard($keyboard);

    $array = $location->toArray();

    expect($array)->toHaveKey('reply_markup')
        ->and($array['reply_markup'])->toHaveKey('inline_keyboard');
});

// --- Venue ---

it('venue with empty title and address filters them out', function () {
    $venue = TelegramVenue::create()
        ->to('123')
        ->coordinates(0.0, 0.0)
        ->title('')
        ->address('');

    $array = $venue->toArray();

    // Empty strings are falsy, filtered by array_filter
    expect($array)->not->toHaveKey('title')
        ->and($array)->not->toHaveKey('address');
});

// --- Contact ---

it('contact with null lastName and vcard filters them out', function () {
    $contact = TelegramContact::create()
        ->to('123')
        ->phoneNumber('+551199999')
        ->firstName('John');

    $array = $contact->toArray();

    expect($array)->not->toHaveKey('last_name')
        ->and($array)->not->toHaveKey('vcard');
});

it('contact with empty phoneNumber and firstName filters them out', function () {
    $contact = TelegramContact::create()->to('123');

    $array = $contact->toArray();

    // Default '' is falsy, filtered by array_filter
    expect($array)->not->toHaveKey('phone_number')
        ->and($array)->not->toHaveKey('first_name');
});

// --- Sticker ---

it('sticker with empty sticker string filters it out', function () {
    $sticker = TelegramSticker::create()->to('123');

    $array = $sticker->toArray();

    expect($array)->not->toHaveKey('sticker');
});

it('sticker with emoji set includes it', function () {
    $sticker = TelegramSticker::create()
        ->to('123')
        ->sticker('sticker_file_id')
        ->emoji("\xF0\x9F\x98\x80");

    $array = $sticker->toArray();

    expect($array['emoji'])->toBe("\xF0\x9F\x98\x80");
});

it('sticker without emoji excludes it', function () {
    $sticker = TelegramSticker::create()
        ->to('123')
        ->sticker('sticker_file_id');

    $array = $sticker->toArray();

    expect($array)->not->toHaveKey('emoji');
});

// --- Dice ---

it('dice with each emoji type returns correct value', function (string $method, string $expected) {
    $dice = TelegramDice::create()->to('123')->{$method}();

    $array = $dice->toArray();

    expect($array['emoji'])->toBe($expected);
})->with([
    ['dice', "\xF0\x9F\x8E\xB2"],
    ['darts', "\xF0\x9F\x8E\xAF"],
    ['basketball', "\xF0\x9F\x8F\x80"],
    ['football', "\xE2\x9A\xBD"],
    ['bowling', "\xF0\x9F\x8E\xB3"],
    ['slotMachine', "\xF0\x9F\x8E\xB0"],
]);
