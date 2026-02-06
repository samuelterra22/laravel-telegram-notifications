<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\Button;

it('creates a URL button', function () {
    $button = Button::url('Click me', 'https://example.com');

    $array = $button->toArray();

    expect($button->getText())->toBe('Click me')
        ->and($array['text'])->toBe('Click me')
        ->and($array['url'])->toBe('https://example.com')
        ->and($array)->not->toHaveKey('callback_data')
        ->and($array)->not->toHaveKey('web_app');
});

it('creates a callback button', function () {
    $button = Button::callback('Action', 'do_something');

    $array = $button->toArray();

    expect($array['text'])->toBe('Action')
        ->and($array['callback_data'])->toBe('do_something')
        ->and($array)->not->toHaveKey('url');
});

it('creates a web app button', function () {
    $button = Button::webApp('Open App', 'https://app.example.com');

    $array = $button->toArray();

    expect($array['text'])->toBe('Open App')
        ->and($array['web_app'])->toBe(['url' => 'https://app.example.com'])
        ->and($array)->not->toHaveKey('url')
        ->and($array)->not->toHaveKey('callback_data');
});

it('returns correct text via getText', function () {
    $button = Button::url('Test', 'https://example.com');

    expect($button->getText())->toBe('Test');
});
