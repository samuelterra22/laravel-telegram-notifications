<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Contracts\ReplyMarkupInterface;
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboardRemove;

it('implements ReplyMarkupInterface', function () {
    $remove = ReplyKeyboardRemove::make();

    expect($remove)->toBeInstanceOf(ReplyMarkupInterface::class);
});

it('creates a basic remove keyboard', function () {
    $remove = ReplyKeyboardRemove::make();

    expect($remove->toArray())->toBe([
        'remove_keyboard' => true,
    ]);
});

it('creates a selective remove keyboard', function () {
    $remove = ReplyKeyboardRemove::make()
        ->selective();

    expect($remove->toArray())->toBe([
        'remove_keyboard' => true,
        'selective' => true,
    ]);
});

it('supports disabling selective', function () {
    $remove = ReplyKeyboardRemove::make()
        ->selective(true)
        ->selective(false);

    expect($remove->toArray())->toBe([
        'remove_keyboard' => true,
    ]);
});

it('returns fluent self from selective', function () {
    $remove = ReplyKeyboardRemove::make();
    $result = $remove->selective();

    expect($result)->toBe($remove);
});
