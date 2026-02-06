<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Contracts\ReplyMarkupInterface;
use SamuelTerra22\TelegramNotifications\Keyboards\ForceReply;

it('implements ReplyMarkupInterface', function () {
    $reply = ForceReply::make();

    expect($reply)->toBeInstanceOf(ReplyMarkupInterface::class);
});

it('creates a basic force reply', function () {
    $reply = ForceReply::make();

    expect($reply->toArray())->toBe([
        'force_reply' => true,
    ]);
});

it('creates a force reply with placeholder', function () {
    $reply = ForceReply::make()
        ->placeholder('Type your answer...');

    expect($reply->toArray())->toBe([
        'force_reply' => true,
        'input_field_placeholder' => 'Type your answer...',
    ]);
});

it('creates a selective force reply', function () {
    $reply = ForceReply::make()
        ->selective();

    expect($reply->toArray())->toBe([
        'force_reply' => true,
        'selective' => true,
    ]);
});

it('chains placeholder and selective', function () {
    $reply = ForceReply::make()
        ->placeholder('Answer here')
        ->selective();

    expect($reply->toArray())->toBe([
        'force_reply' => true,
        'input_field_placeholder' => 'Answer here',
        'selective' => true,
    ]);
});

it('returns fluent self from all methods', function () {
    $reply = ForceReply::make();

    expect($reply->placeholder('test'))->toBe($reply)
        ->and($reply->selective())->toBe($reply);
});

it('supports disabling selective', function () {
    $reply = ForceReply::make()
        ->selective(true)
        ->selective(false);

    expect($reply->toArray())->toBe([
        'force_reply' => true,
    ]);
});
