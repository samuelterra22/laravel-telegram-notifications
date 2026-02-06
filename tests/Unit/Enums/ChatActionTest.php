<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Enums\ChatAction;

it('has typing action', function () {
    expect(ChatAction::Typing->value)->toBe('typing');
});

it('has upload_photo action', function () {
    expect(ChatAction::UploadPhoto->value)->toBe('upload_photo');
});

it('has record_video action', function () {
    expect(ChatAction::RecordVideo->value)->toBe('record_video');
});

it('has upload_video action', function () {
    expect(ChatAction::UploadVideo->value)->toBe('upload_video');
});

it('has record_voice action', function () {
    expect(ChatAction::RecordVoice->value)->toBe('record_voice');
});

it('has upload_voice action', function () {
    expect(ChatAction::UploadVoice->value)->toBe('upload_voice');
});

it('has upload_document action', function () {
    expect(ChatAction::UploadDocument->value)->toBe('upload_document');
});

it('has choose_sticker action', function () {
    expect(ChatAction::ChooseSticker->value)->toBe('choose_sticker');
});

it('has find_location action', function () {
    expect(ChatAction::FindLocation->value)->toBe('find_location');
});

it('has all 11 actions', function () {
    expect(ChatAction::cases())->toHaveCount(11);
});

it('can be created from value', function () {
    expect(ChatAction::from('typing'))->toBe(ChatAction::Typing);
});
