<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboard;

it('creates an empty keyboard', function () {
    $keyboard = ReplyKeyboard::make();

    expect($keyboard->isEmpty())->toBeTrue();
});

it('adds text buttons', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('Option A')
        ->button('Option B');

    $array = $keyboard->toArray();

    expect($keyboard->isEmpty())->toBeFalse()
        ->and($array['keyboard'])->toHaveCount(1)
        ->and($array['keyboard'][0])->toHaveCount(2)
        ->and($array['keyboard'][0][0]['text'])->toBe('Option A');
});

it('creates rows based on columns', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('A', 2)
        ->button('B', 2)
        ->button('C', 2);

    $array = $keyboard->toArray();

    expect($array['keyboard'])->toHaveCount(2)
        ->and($array['keyboard'][0])->toHaveCount(2);
});

it('supports manual row breaks', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('Row 1')
        ->row()
        ->button('Row 2');

    $array = $keyboard->toArray();

    expect($array['keyboard'])->toHaveCount(2);
});

it('sets resize keyboard', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('A')
        ->resize();

    $array = $keyboard->toArray();

    expect($array['resize_keyboard'])->toBeTrue();
});

it('sets one time keyboard', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('A')
        ->oneTime();

    $array = $keyboard->toArray();

    expect($array['one_time_keyboard'])->toBeTrue();
});

it('sets placeholder', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('A')
        ->placeholder('Choose an option...');

    $array = $keyboard->toArray();

    expect($array['input_field_placeholder'])->toBe('Choose an option...');
});

it('sets selective', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('A')
        ->selective();

    $array = $keyboard->toArray();

    expect($array['selective'])->toBeTrue();
});

it('sets persistent', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('A')
        ->persistent();

    $array = $keyboard->toArray();

    expect($array['is_persistent'])->toBeTrue();
});

it('adds request contact button', function () {
    $keyboard = ReplyKeyboard::make()
        ->requestContact('Share Contact');

    $array = $keyboard->toArray();

    expect($array['keyboard'][0][0]['text'])->toBe('Share Contact')
        ->and($array['keyboard'][0][0]['request_contact'])->toBeTrue();
});

it('auto advances row for request contact when columns filled', function () {
    $keyboard = ReplyKeyboard::make()
        ->requestContact('Contact 1', 1)
        ->requestContact('Contact 2', 1);

    $array = $keyboard->toArray();

    expect($array['keyboard'])->toHaveCount(2)
        ->and($array['keyboard'][0][0]['text'])->toBe('Contact 1')
        ->and($array['keyboard'][1][0]['text'])->toBe('Contact 2');
});

it('adds request location button', function () {
    $keyboard = ReplyKeyboard::make()
        ->requestLocation('Share Location');

    $array = $keyboard->toArray();

    expect($array['keyboard'][0][0]['text'])->toBe('Share Location')
        ->and($array['keyboard'][0][0]['request_location'])->toBeTrue();
});

it('auto advances row for request location when columns filled', function () {
    $keyboard = ReplyKeyboard::make()
        ->requestLocation('Location 1', 1)
        ->requestLocation('Location 2', 1);

    $array = $keyboard->toArray();

    expect($array['keyboard'])->toHaveCount(2)
        ->and($array['keyboard'][0][0]['text'])->toBe('Location 1')
        ->and($array['keyboard'][1][0]['text'])->toBe('Location 2');
});
