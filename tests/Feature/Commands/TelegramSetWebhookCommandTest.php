<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('sets a webhook URL', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => true,
            'description' => 'Webhook was set',
        ]),
    ]);

    $this->artisan('telegram:set-webhook', ['--url' => 'https://example.com/webhook'])
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook set to');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setWebhook')
        && $request['url'] === 'https://example.com/webhook'
    );
});

it('deletes a webhook', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => true,
        ]),
    ]);

    $this->artisan('telegram:set-webhook', ['--delete' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook deleted');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteWebhook'));
});

it('fails when no URL provided and not deleting', function () {
    $this->artisan('telegram:set-webhook')
        ->assertFailed()
        ->expectsOutputToContain('You must provide a --url or use --delete');
});

it('handles API error on set', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: bad webhook',
        ], 400),
    ]);

    $this->artisan('telegram:set-webhook', ['--url' => 'invalid'])
        ->assertFailed();
});

it('fails when deleteWebhook returns ok false', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Webhook deletion failed',
        ], 200),
    ]);

    $this->artisan('telegram:set-webhook', ['--delete' => true])
        ->assertFailed()
        ->expectsOutputToContain('Failed to delete webhook');
});

it('fails when setWebhook returns ok false', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Invalid URL provided',
        ], 200),
    ]);

    $this->artisan('telegram:set-webhook', ['--url' => 'https://example.com/webhook'])
        ->assertFailed()
        ->expectsOutputToContain('Failed to set webhook');
});

it('passes secret token', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => true,
        ]),
    ]);

    $this->artisan('telegram:set-webhook', [
        '--url' => 'https://example.com/webhook',
        '--secret' => 'my-secret-token',
    ])->assertSuccessful();

    Http::assertSent(fn ($request) => $request['secret_token'] === 'my-secret-token');
});
