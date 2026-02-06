<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramDocument;

it('creates a document message', function () {
    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf');

    expect($message->getApiMethod())->toBe('sendDocument');

    $array = $message->toArray();
    expect($array['document'])->toBe('https://example.com/file.pdf');
});

it('sets caption', function () {
    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf')
        ->caption('Important document');

    $array = $message->toArray();

    expect($array['caption'])->toBe('Important document');
});

it('sets thumbnail', function () {
    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf')
        ->thumbnail('https://example.com/thumb.jpg');

    $array = $message->toArray();

    expect($array['thumbnail'])->toBe('https://example.com/thumb.jpg');
});

it('disables content type detection', function () {
    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf')
        ->disableContentTypeDetection();

    $array = $message->toArray();

    expect($array['disable_content_type_detection'])->toBeTrue();
});

it('sets silent and protected', function () {
    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets parse mode', function () {
    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf')
        ->caption('Formatted caption')
        ->parseMode(\SamuelTerra22\TelegramNotifications\Enums\ParseMode::MarkdownV2);

    $array = $message->toArray();

    expect($array['parse_mode'])->toBe('MarkdownV2')
        ->and($array['caption'])->toBe('Formatted caption');
});

it('does not include parse mode when caption is empty', function () {
    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf')
        ->parseMode(\SamuelTerra22\TelegramNotifications\Enums\ParseMode::MarkdownV2);

    $array = $message->toArray();

    expect($array)->not->toHaveKey('parse_mode')
        ->and($array)->not->toHaveKey('caption');
});

it('sets keyboard', function () {
    $keyboard = \SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard::make()
        ->url('Download', 'https://example.com/download');

    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray()
        ->and($array['reply_markup'])->toHaveKey('inline_keyboard');
});
