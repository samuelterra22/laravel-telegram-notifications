<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramPoll;

it('anonymous(false) includes is_anonymous as false', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->anonymous(false);

    $array = $poll->toArray();

    expect($array)->toHaveKey('is_anonymous')
        ->and($array['is_anonymous'])->toBeFalse();
});

it('anonymous(true) (default) does not include is_anonymous', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B']);

    $array = $poll->toArray();

    // isAnonymous=true maps to null, filtered by fn($v) => $v !== null
    expect($array)->not->toHaveKey('is_anonymous');
});

it('quiz type includes type field', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->quiz(0);

    $array = $poll->toArray();

    expect($array['type'])->toBe('quiz')
        ->and($array['correct_option_id'])->toBe(0);
});

it('regular type (default) does not include type field', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B']);

    $array = $poll->toArray();

    expect($array)->not->toHaveKey('type');
});

it('allowsMultipleAnswers(false) is filtered out', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->allowsMultipleAnswers(false);

    $array = $poll->toArray();

    // false ?: null => null, then filtered by !== null
    expect($array)->not->toHaveKey('allows_multiple_answers');
});

it('allowsMultipleAnswers(true) is included', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->allowsMultipleAnswers(true);

    $array = $poll->toArray();

    expect($array)->toHaveKey('allows_multiple_answers')
        ->and($array['allows_multiple_answers'])->toBeTrue();
});

it('closed(false) is filtered out', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->closed(false);

    $array = $poll->toArray();

    expect($array)->not->toHaveKey('is_closed');
});

it('closed(true) is included', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->closed(true);

    $array = $poll->toArray();

    expect($array['is_closed'])->toBeTrue();
});

it('quiz with explanation includes explanation', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->quiz(0, 'Because A is correct');

    $array = $poll->toArray();

    expect($array['explanation'])->toBe('Because A is correct');
});

it('quiz without explanation excludes explanation', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->quiz(0);

    $array = $poll->toArray();

    expect($array)->not->toHaveKey('explanation');
});

it('openPeriod and closeDate included when set', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B'])
        ->openPeriod(300)
        ->closeDate(1700000000);

    $array = $poll->toArray();

    expect($array['open_period'])->toBe(300)
        ->and($array['close_date'])->toBe(1700000000);
});

it('openPeriod and closeDate excluded when not set', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('Question?')
        ->options(['A', 'B']);

    $array = $poll->toArray();

    expect($array)->not->toHaveKey('open_period')
        ->and($array)->not->toHaveKey('close_date');
});

it('empty question string is included (not null)', function () {
    $poll = TelegramPoll::create()
        ->to('123')
        ->question('')
        ->options(['A', 'B']);

    $array = $poll->toArray();

    // Empty string survives the fn($v) => $v !== null filter
    // but fails array_filter's default behavior... wait:
    // The poll uses fn ($value) => $value !== null as the filter
    // '' !== null is true, so it survives
    expect($array)->toHaveKey('question')
        ->and($array['question'])->toBe('');
});
