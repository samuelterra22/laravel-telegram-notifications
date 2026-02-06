<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\Button;

it('creates a login url button with string url', function () {
    $button = Button::loginUrl('Login', 'https://example.com/auth');

    expect($button->getText())->toBe('Login')
        ->and($button->toArray())->toBe([
            'text' => 'Login',
            'login_url' => ['url' => 'https://example.com/auth'],
        ]);
});

it('creates a login url button with array config', function () {
    $button = Button::loginUrl('Login', [
        'url' => 'https://example.com/auth',
        'forward_text' => 'Authorize',
        'bot_username' => 'mybot',
    ]);

    expect($button->toArray())->toBe([
        'text' => 'Login',
        'login_url' => [
            'url' => 'https://example.com/auth',
            'forward_text' => 'Authorize',
            'bot_username' => 'mybot',
        ],
    ]);
});

it('creates a switch inline query button', function () {
    $button = Button::switchInlineQuery('Search', 'query text');

    expect($button->getText())->toBe('Search')
        ->and($button->toArray())->toBe([
            'text' => 'Search',
            'switch_inline_query' => 'query text',
        ]);
});

it('creates a switch inline query button with default empty query', function () {
    $button = Button::switchInlineQuery('Search');

    expect($button->toArray())->toHaveKey('switch_inline_query');
});

it('creates a switch inline query current chat button', function () {
    $button = Button::switchInlineQueryCurrentChat('Search Here', 'local query');

    expect($button->getText())->toBe('Search Here')
        ->and($button->toArray())->toBe([
            'text' => 'Search Here',
            'switch_inline_query_current_chat' => 'local query',
        ]);
});

it('creates a switch inline query chosen chat button', function () {
    $button = Button::switchInlineQueryChosenChat('Pick Chat', [
        'query' => 'test',
        'allow_user_chats' => true,
        'allow_group_chats' => true,
    ]);

    $array = $button->toArray();

    expect($array['text'])->toBe('Pick Chat')
        ->and($array['switch_inline_query_chosen_chat'])->toBe([
            'query' => 'test',
            'allow_user_chats' => true,
            'allow_group_chats' => true,
        ]);
});

it('creates a switch inline query chosen chat with empty options', function () {
    $button = Button::switchInlineQueryChosenChat('Pick Chat');

    expect($button->toArray())->toHaveKey('text');
});

it('creates a copy text button', function () {
    $button = Button::copyText('Copy Code', 'ABC123');

    expect($button->getText())->toBe('Copy Code')
        ->and($button->toArray())->toBe([
            'text' => 'Copy Code',
            'copy_text' => ['text' => 'ABC123'],
        ]);
});

it('creates a pay button', function () {
    $button = Button::pay('Pay $10');

    expect($button->getText())->toBe('Pay $10')
        ->and($button->toArray())->toBe([
            'text' => 'Pay $10',
            'pay' => true,
        ]);
});

// --- Backward compatibility ---

it('url button still works identically', function () {
    $button = Button::url('Visit', 'https://example.com');

    expect($button->toArray())->toBe([
        'text' => 'Visit',
        'url' => 'https://example.com',
    ]);
});

it('callback button still works identically', function () {
    $button = Button::callback('Click', 'action_1');

    expect($button->toArray())->toBe([
        'text' => 'Click',
        'callback_data' => 'action_1',
    ]);
});

it('webApp button still works identically', function () {
    $button = Button::webApp('Open App', 'https://app.example.com');

    expect($button->toArray())->toBe([
        'text' => 'Open App',
        'web_app' => ['url' => 'https://app.example.com'],
    ]);
});
