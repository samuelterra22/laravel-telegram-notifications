<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\Button;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;

it('creates an empty keyboard', function () {
    $keyboard = InlineKeyboard::make();

    expect($keyboard->isEmpty())->toBeTrue()
        ->and($keyboard->toArray())->toBe(['inline_keyboard' => []]);
});

it('adds URL buttons', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Link 1', 'https://example.com/1')
        ->url('Link 2', 'https://example.com/2');

    $array = $keyboard->toArray();

    expect($keyboard->isEmpty())->toBeFalse()
        ->and($array['inline_keyboard'])->toHaveCount(1)
        ->and($array['inline_keyboard'][0])->toHaveCount(2);
});

it('adds callback buttons', function () {
    $keyboard = InlineKeyboard::make()
        ->callback('Action 1', 'action:1')
        ->callback('Action 2', 'action:2');

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(1)
        ->and($array['inline_keyboard'][0][0]['callback_data'])->toBe('action:1');
});

it('adds web app buttons', function () {
    $keyboard = InlineKeyboard::make()
        ->webApp('Open', 'https://app.example.com');

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'][0][0]['web_app'])->toBe(['url' => 'https://app.example.com']);
});

it('creates new rows automatically based on columns', function () {
    $keyboard = InlineKeyboard::make()
        ->url('1', 'https://1.com', 2)
        ->url('2', 'https://2.com', 2)
        ->url('3', 'https://3.com', 2);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(2)
        ->and($array['inline_keyboard'][0])->toHaveCount(2)
        ->and($array['inline_keyboard'][1])->toHaveCount(1);
});

it('supports manual row breaks', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Row 1', 'https://1.com')
        ->row()
        ->url('Row 2', 'https://2.com');

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(2)
        ->and($array['inline_keyboard'][0])->toHaveCount(1)
        ->and($array['inline_keyboard'][1])->toHaveCount(1);
});

it('supports single column layout', function () {
    $keyboard = InlineKeyboard::make()
        ->url('A', 'https://a.com', 1)
        ->url('B', 'https://b.com', 1)
        ->url('C', 'https://c.com', 1);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(3);
});

it('accepts custom Button instances', function () {
    $button = Button::url('Custom', 'https://example.com');
    $keyboard = InlineKeyboard::make()->button($button);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'][0][0]['text'])->toBe('Custom');
});
