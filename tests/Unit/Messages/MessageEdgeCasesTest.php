<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramContact;
use SamuelTerra22\TelegramNotifications\Messages\TelegramLocation;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPoll;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVenue;

// --- TelegramMessage edge cases ---

it('produces valid array with empty content', function () {
    $message = TelegramMessage::create();
    $array = $message->toArray();

    // Empty string is falsy so array_filter removes it
    expect($array)->not->toHaveKey('text');
    expect($array)->toHaveKey('parse_mode');
});

it('does not split content at exactly 4096 chars', function () {
    $content = str_repeat('A', 4096);
    $message = TelegramMessage::create($content);

    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(1)
        ->and(mb_strlen($chunks[0]))->toBe(4096);
});

it('splits content at 4097 chars into 2 chunks', function () {
    $content = str_repeat('A', 4097);
    $message = TelegramMessage::create($content);

    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(2)
        ->and(mb_strlen($chunks[0]))->toBe(4096)
        ->and(mb_strlen($chunks[1]))->toBe(1);
});

it('splits content with no newlines at 4096 boundary', function () {
    $content = str_repeat('B', 8192);
    $message = TelegramMessage::create($content);

    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(2);

    foreach ($chunks as $chunk) {
        expect(mb_strlen($chunk))->toBeLessThanOrEqual(4096);
    }
});

// --- TelegramPoll edge cases ---

it('includes empty options array in poll toArray', function () {
    $poll = TelegramPoll::create()
        ->question('What?');

    $array = $poll->toArray();

    // 'options' is an empty array, but array_filter with !== null keeps it
    expect($array)->toHaveKey('options')
        ->and($array['options'])->toBe([]);
});

it('includes correctOptionId=0 in quiz poll', function () {
    $poll = TelegramPoll::create()
        ->question('Quiz?')
        ->options(['A', 'B', 'C'])
        ->quiz(0);

    $array = $poll->toArray();

    // correctOptionId=0 should NOT be filtered out (filter is !== null)
    expect($array)->toHaveKey('correct_option_id')
        ->and($array['correct_option_id'])->toBe(0);
});

it('builds options incrementally with addOption', function () {
    $poll = TelegramPoll::create()
        ->question('Pick one')
        ->addOption('First')
        ->addOption('Second')
        ->addOption('Third');

    $array = $poll->toArray();

    expect($array['options'])->toHaveCount(3)
        ->and($array['options'][0]['text'])->toBe('First')
        ->and($array['options'][1]['text'])->toBe('Second')
        ->and($array['options'][2]['text'])->toBe('Third');
});

// --- TelegramLocation edge cases ---

it('filters out zero coordinates due to array_filter', function () {
    $location = TelegramLocation::create()
        ->coordinates(0.0, 0.0);

    $array = $location->toArray();

    // BUG: plain array_filter treats 0.0 as falsy, so coordinates are removed
    // This documents the current behavior
    expect($array)->not->toHaveKey('latitude')
        ->and($array)->not->toHaveKey('longitude');
});

// --- TelegramContact edge cases ---

it('filters out empty firstName due to array_filter', function () {
    $contact = TelegramContact::create()
        ->phoneNumber('+1234567890')
        ->firstName('');

    $array = $contact->toArray();

    // Empty string is falsy, so array_filter removes it
    expect($array)->not->toHaveKey('first_name')
        ->and($array)->toHaveKey('phone_number');
});

// --- TelegramVenue edge cases ---

it('includes all fields when fully configured', function () {
    $venue = TelegramVenue::create()
        ->coordinates(-23.5505, -46.6333)
        ->title('Paulista Avenue')
        ->address('Av. Paulista, São Paulo')
        ->foursquareId('4b5a1234f964a520')
        ->foursquareType('arts_entertainment')
        ->googlePlaceId('ChIJrTLr-GyuEmsR')
        ->googlePlaceType('point_of_interest');

    $array = $venue->toArray();

    expect($array)->toHaveKey('latitude')
        ->and($array)->toHaveKey('longitude')
        ->and($array)->toHaveKey('title')
        ->and($array)->toHaveKey('address')
        ->and($array)->toHaveKey('foursquare_id')
        ->and($array)->toHaveKey('foursquare_type')
        ->and($array)->toHaveKey('google_place_id')
        ->and($array)->toHaveKey('google_place_type');
});
