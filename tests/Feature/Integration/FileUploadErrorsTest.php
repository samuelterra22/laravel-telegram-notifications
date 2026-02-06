<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

beforeEach(function () {
    $this->api = new TelegramBotApi(
        token: 'test-token',
        baseUrl: 'https://api.telegram.org',
    );
});

it('throws when uploading a non-existent file', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $this->api->upload('sendDocument', ['chat_id' => '-100123'], 'document', '/nonexistent/path/file.pdf');
})->throws(\ErrorException::class);

it('throws when uploading with empty file path', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
    ]);

    $this->api->upload('sendDocument', ['chat_id' => '-100123'], 'document', '');
})->throws(ValueError::class, 'Path must not be empty');

it('throws TelegramApiException when upload API returns error', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: file is too big',
        ], 400),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'tg_test_');
    file_put_contents($tempFile, 'test content');

    try {
        $this->api->upload('sendDocument', ['chat_id' => '-100123'], 'document', $tempFile);
    } finally {
        unlink($tempFile);
    }
})->throws(TelegramApiException::class, 'file is too big');

it('succeeds when uploading a valid file', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 42],
        ]),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'tg_test_');
    file_put_contents($tempFile, 'test content');

    try {
        $result = $this->api->upload('sendDocument', ['chat_id' => '-100123'], 'document', $tempFile);
        expect($result['ok'])->toBeTrue()
            ->and($result['result']['message_id'])->toBe(42);
    } finally {
        unlink($tempFile);
    }
});

it('callSilent returns false when upload would fail', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: wrong file type',
        ], 400),
    ]);

    // callSilent only wraps call(), not upload(), so test the silent pattern indirectly
    $result = $this->api->callSilent('sendDocument', [
        'chat_id' => '-100123',
        'document' => 'invalid_file_id',
    ]);

    expect($result)->toBeFalse();
});
