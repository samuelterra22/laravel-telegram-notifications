<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramPoll;

it('creates a poll message', function () {
    $message = TelegramPoll::create()
        ->to('-100123')
        ->question('Favorite color?')
        ->options(['Red', 'Blue', 'Green']);

    expect($message->getApiMethod())->toBe('sendPoll');

    $array = $message->toArray();
    expect($array['question'])->toBe('Favorite color?')
        ->and($array['options'])->toBe([
            ['text' => 'Red'],
            ['text' => 'Blue'],
            ['text' => 'Green'],
        ]);
});

it('adds options individually', function () {
    $message = TelegramPoll::create()
        ->to('-100123')
        ->question('Pick one')
        ->addOption('A')
        ->addOption('B');

    $array = $message->toArray();

    expect($array['options'])->toBe([
        ['text' => 'A'],
        ['text' => 'B'],
    ]);
});

it('creates a quiz poll', function () {
    $message = TelegramPoll::create()
        ->to('-100123')
        ->question('What is 2+2?')
        ->options(['3', '4', '5'])
        ->quiz(1, 'Because math!');

    $array = $message->toArray();

    expect($array['type'])->toBe('quiz')
        ->and($array['correct_option_id'])->toBe(1)
        ->and($array['explanation'])->toBe('Because math!');
});

it('allows multiple answers', function () {
    $message = TelegramPoll::create()
        ->to('-100123')
        ->question('Select all that apply')
        ->options(['A', 'B', 'C'])
        ->allowsMultipleAnswers();

    $array = $message->toArray();

    expect($array['allows_multiple_answers'])->toBeTrue();
});

it('sets anonymous and open period', function () {
    $message = TelegramPoll::create()
        ->to('-100123')
        ->question('Test')
        ->options(['A', 'B'])
        ->anonymous(false)
        ->openPeriod(300);

    $array = $message->toArray();

    expect($array['is_anonymous'])->toBeFalse()
        ->and($array['open_period'])->toBe(300);
});

it('sets closed state', function () {
    $message = TelegramPoll::create()
        ->to('-100123')
        ->question('Test')
        ->options(['A', 'B'])
        ->closed();

    $array = $message->toArray();

    expect($array['is_closed'])->toBeTrue();
});

it('sets close date', function () {
    $timestamp = 1700000000;

    $message = TelegramPoll::create()
        ->to('-100123')
        ->question('Timed poll')
        ->options(['A', 'B'])
        ->closeDate($timestamp);

    $array = $message->toArray();

    expect($array['close_date'])->toBe($timestamp);
});
