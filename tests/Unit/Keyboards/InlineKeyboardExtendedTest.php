<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Contracts\ReplyMarkupInterface;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;

it('implements ReplyMarkupInterface', function () {
    $keyboard = InlineKeyboard::make();

    expect($keyboard)->toBeInstanceOf(ReplyMarkupInterface::class);
});

it('adds login url button', function () {
    $keyboard = InlineKeyboard::make()
        ->loginUrl('Login', 'https://example.com/auth');

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'][0][0])->toBe([
        'text' => 'Login',
        'login_url' => ['url' => 'https://example.com/auth'],
    ]);
});

it('adds switch inline query button', function () {
    $keyboard = InlineKeyboard::make()
        ->switchInlineQuery('Search', 'query');

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'][0][0])->toBe([
        'text' => 'Search',
        'switch_inline_query' => 'query',
    ]);
});

it('adds switch inline query current chat button', function () {
    $keyboard = InlineKeyboard::make()
        ->switchInlineQueryCurrentChat('Here', 'local');

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'][0][0])->toBe([
        'text' => 'Here',
        'switch_inline_query_current_chat' => 'local',
    ]);
});

it('adds switch inline query chosen chat button', function () {
    $keyboard = InlineKeyboard::make()
        ->switchInlineQueryChosenChat('Pick', ['allow_user_chats' => true]);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'][0][0]['text'])->toBe('Pick')
        ->and($array['inline_keyboard'][0][0]['switch_inline_query_chosen_chat'])->toBe([
            'allow_user_chats' => true,
        ]);
});

it('adds copy text button', function () {
    $keyboard = InlineKeyboard::make()
        ->copyText('Copy', 'secret-code');

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'][0][0])->toBe([
        'text' => 'Copy',
        'copy_text' => ['text' => 'secret-code'],
    ]);
});

it('adds pay button', function () {
    $keyboard = InlineKeyboard::make()
        ->pay('Pay Now');

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'][0][0])->toBe([
        'text' => 'Pay Now',
        'pay' => true,
    ]);
});

it('chains new and existing button types', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Visit', 'https://example.com', 1)
        ->callback('Click', 'action', 1)
        ->loginUrl('Login', 'https://auth.example.com', 1)
        ->pay('Pay', 1);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(4);
});

it('respects column layout with new button types', function () {
    $keyboard = InlineKeyboard::make()
        ->copyText('Copy 1', 'code1', 2)
        ->copyText('Copy 2', 'code2', 2)
        ->copyText('Copy 3', 'code3', 2);

    $array = $keyboard->toArray();

    expect($array['inline_keyboard'])->toHaveCount(2)
        ->and($array['inline_keyboard'][0])->toHaveCount(2)
        ->and($array['inline_keyboard'][1])->toHaveCount(1);
});
