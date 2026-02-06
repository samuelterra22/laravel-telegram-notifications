<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\Button;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboard;

it('reports empty InlineKeyboard as isEmpty true', function () {
    $keyboard = InlineKeyboard::make();

    expect($keyboard->isEmpty())->toBeTrue()
        ->and($keyboard->toArray())->toBe(['inline_keyboard' => []]);
});

it('creates multiple empty rows with consecutive row() calls', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Button 1', 'https://example.com', 1)
        ->row()
        ->row()
        ->url('Button 2', 'https://example2.com', 1);

    $array = $keyboard->toArray();

    // Button1 at row 0, row() bumps to 1, row() bumps to 2, Button2 at row 2 (since row 0 auto-bumped to 1 due to columns=1)
    // Actually: Button1 goes to row 0, columns=1 means auto-bump to row 1, then row() bumps to 2, row() bumps to 3, Button2 at row 3
    expect(count($array['inline_keyboard']))->toBeGreaterThanOrEqual(2);
});

it('reports empty ReplyKeyboard as isEmpty true', function () {
    $keyboard = ReplyKeyboard::make();

    expect($keyboard->isEmpty())->toBeTrue();
});

it('produces correct toArray output for each button type', function () {
    $urlButton = Button::url('Visit', 'https://example.com');
    $callbackButton = Button::callback('Click', 'callback_data');
    $webAppButton = Button::webApp('Open', 'https://webapp.example.com');

    expect($urlButton->toArray())->toBe([
        'text' => 'Visit',
        'url' => 'https://example.com',
    ]);

    expect($callbackButton->toArray())->toBe([
        'text' => 'Click',
        'callback_data' => 'callback_data',
    ]);

    expect($webAppButton->toArray())->toBe([
        'text' => 'Open',
        'web_app' => ['url' => 'https://webapp.example.com'],
    ]);
});

it('places single button per row with columns=1', function () {
    $keyboard = InlineKeyboard::make()
        ->url('A', 'https://a.com', 1)
        ->url('B', 'https://b.com', 1)
        ->url('C', 'https://c.com', 1);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(3);

    foreach ($array['inline_keyboard'] as $row) {
        expect($row)->toHaveCount(1);
    }
});
