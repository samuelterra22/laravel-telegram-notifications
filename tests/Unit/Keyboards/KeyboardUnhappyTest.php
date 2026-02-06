<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\Button;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboard;

// --- InlineKeyboard ---

it('inline keyboard with high column count and 1 button has 1 row with 1 button', function () {
    $keyboard = InlineKeyboard::make()->url('Click', 'https://example.com', 100);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(1)
        ->and($array['inline_keyboard'][0])->toHaveCount(1);
});

it('inline keyboard with 5 buttons and 3 columns produces 2 rows', function () {
    $keyboard = InlineKeyboard::make();

    for ($i = 1; $i <= 5; $i++) {
        $keyboard->url("Btn {$i}", "https://example.com/{$i}", 3);
    }

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(2)
        ->and($array['inline_keyboard'][0])->toHaveCount(3)
        ->and($array['inline_keyboard'][1])->toHaveCount(2);
});

it('multiple row() calls create empty rows between groups', function () {
    $keyboard = InlineKeyboard::make()
        ->url('A', 'https://a.com', 1)
        ->row()
        ->row()
        ->url('B', 'https://b.com', 1);

    $array = $keyboard->toArray();

    // row() increments currentRow, so skipping rows creates gaps
    // array_values in toArray() compacts them
    expect($array['inline_keyboard'])->toHaveCount(2)
        ->and($array['inline_keyboard'][0][0]['text'])->toBe('A')
        ->and($array['inline_keyboard'][1][0]['text'])->toBe('B');
});

it('mixing url and callback button types in same keyboard', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Visit', 'https://example.com', 1)
        ->callback('Press', 'action_1', 1);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(2)
        ->and($array['inline_keyboard'][0][0])->toHaveKey('url')
        ->and($array['inline_keyboard'][1][0])->toHaveKey('callback_data');
});

// --- ReplyKeyboard ---

it('reply keyboard resize(false) excludes resize_keyboard', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->resize(false);

    $array = $keyboard->toArray();

    expect($array)->not->toHaveKey('resize_keyboard');
});

it('reply keyboard resize(true) includes resize_keyboard', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->resize(true);

    $array = $keyboard->toArray();

    expect($array['resize_keyboard'])->toBeTrue();
});

it('reply keyboard oneTime(false) excludes one_time_keyboard', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->oneTime(false);

    $array = $keyboard->toArray();

    expect($array)->not->toHaveKey('one_time_keyboard');
});

it('reply keyboard oneTime(true) includes one_time_keyboard', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->oneTime(true);

    $array = $keyboard->toArray();

    expect($array['one_time_keyboard'])->toBeTrue();
});

it('reply keyboard selective(false) excludes selective', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->selective(false);

    $array = $keyboard->toArray();

    expect($array)->not->toHaveKey('selective');
});

it('reply keyboard selective(true) includes selective', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->selective(true);

    $array = $keyboard->toArray();

    expect($array['selective'])->toBeTrue();
});

it('reply keyboard persistent(false) excludes is_persistent', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->persistent(false);

    $array = $keyboard->toArray();

    expect($array)->not->toHaveKey('is_persistent');
});

it('reply keyboard persistent(true) includes is_persistent', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->persistent(true);

    $array = $keyboard->toArray();

    expect($array['is_persistent'])->toBeTrue();
});

it('reply keyboard with placeholder includes input_field_placeholder', function () {
    $keyboard = ReplyKeyboard::make()->button('A')->placeholder('Type here...');

    $array = $keyboard->toArray();

    expect($array['input_field_placeholder'])->toBe('Type here...');
});

// --- Button ---

it('button with null webAppUrl excludes web_app', function () {
    $button = Button::url('Click', 'https://example.com');

    $array = $button->toArray();

    expect($array)->not->toHaveKey('web_app');
});
