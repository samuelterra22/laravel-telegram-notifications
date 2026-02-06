<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramContact;

it('creates a contact message', function () {
    $message = TelegramContact::create()
        ->to('-100123')
        ->phoneNumber('+5511999999999')
        ->firstName('Samuel');

    expect($message->getApiMethod())->toBe('sendContact');

    $array = $message->toArray();
    expect($array['phone_number'])->toBe('+5511999999999')
        ->and($array['first_name'])->toBe('Samuel');
});

it('sets last name', function () {
    $message = TelegramContact::create()
        ->to('-100123')
        ->phoneNumber('+5511999999999')
        ->firstName('Samuel')
        ->lastName('Terra');

    $array = $message->toArray();

    expect($array['last_name'])->toBe('Terra');
});

it('sets vcard', function () {
    $message = TelegramContact::create()
        ->to('-100123')
        ->phoneNumber('+5511999999999')
        ->firstName('Samuel')
        ->vcard('BEGIN:VCARD\nVERSION:3.0\nEND:VCARD');

    $array = $message->toArray();

    expect($array['vcard'])->toBe('BEGIN:VCARD\nVERSION:3.0\nEND:VCARD');
});

it('sets silent and protected', function () {
    $message = TelegramContact::create()
        ->to('-100123')
        ->phoneNumber('+5511999999999')
        ->firstName('Test')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('sets keyboard', function () {
    $keyboard = \SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard::make()
        ->url('Call', 'tel:+5511999999999');

    $message = TelegramContact::create()
        ->to('-100123')
        ->phoneNumber('+5511999999999')
        ->firstName('Samuel')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray()
        ->and($array['reply_markup'])->toHaveKey('inline_keyboard');
});
