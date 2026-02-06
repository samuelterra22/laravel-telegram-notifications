<?php

declare(strict_types=1);

use Carbon\Carbon;
use SamuelTerra22\TelegramNotifications\Responses\TelegramResponse;

it('reports ok status', function () {
    $response = new TelegramResponse(['ok' => true, 'result' => []]);

    expect($response->ok())->toBeTrue();
});

it('reports not ok status', function () {
    $response = new TelegramResponse(['ok' => false, 'result' => []]);

    expect($response->ok())->toBeFalse();
});

it('reports not ok when key missing', function () {
    $response = new TelegramResponse([]);

    expect($response->ok())->toBeFalse();
});

it('extracts message id', function () {
    $response = new TelegramResponse([
        'ok' => true,
        'result' => ['message_id' => 42],
    ]);

    expect($response->messageId())->toBe(42);
});

it('returns null message id when missing', function () {
    $response = new TelegramResponse(['ok' => true, 'result' => []]);

    expect($response->messageId())->toBeNull();
});

it('extracts date as Carbon instance', function () {
    $response = new TelegramResponse([
        'ok' => true,
        'result' => ['date' => 1700000000],
    ]);

    $date = $response->date();

    expect($date)->toBeInstanceOf(Carbon::class)
        ->and($date->timestamp)->toBe(1700000000);
});

it('returns null date when missing', function () {
    $response = new TelegramResponse(['ok' => true, 'result' => []]);

    expect($response->date())->toBeNull();
});

it('extracts chat info', function () {
    $chat = ['id' => -100123, 'type' => 'group', 'title' => 'Test Group'];
    $response = new TelegramResponse([
        'ok' => true,
        'result' => ['chat' => $chat],
    ]);

    expect($response->chat())->toBe($chat);
});

it('returns null chat when missing', function () {
    $response = new TelegramResponse(['ok' => true, 'result' => []]);

    expect($response->chat())->toBeNull();
});

it('extracts text', function () {
    $response = new TelegramResponse([
        'ok' => true,
        'result' => ['text' => 'Hello World'],
    ]);

    expect($response->text())->toBe('Hello World');
});

it('returns null text when missing', function () {
    $response = new TelegramResponse(['ok' => true, 'result' => []]);

    expect($response->text())->toBeNull();
});

it('returns full result array', function () {
    $result = ['message_id' => 42, 'text' => 'Hello'];
    $response = new TelegramResponse(['ok' => true, 'result' => $result]);

    expect($response->result())->toBe($result);
});

it('returns empty result when missing', function () {
    $response = new TelegramResponse(['ok' => true]);

    expect($response->result())->toBe([]);
});

it('returns raw data via toArray', function () {
    $data = ['ok' => true, 'result' => ['message_id' => 1]];
    $response = new TelegramResponse($data);

    expect($response->toArray())->toBe($data);
});

it('handles result being true (boolean) for simple API responses', function () {
    $response = new TelegramResponse(['ok' => true, 'result' => true]);

    expect($response->result())->toBe([])
        ->and($response->messageId())->toBeNull();
});
