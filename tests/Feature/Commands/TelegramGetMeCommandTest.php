<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('displays bot information', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [
                'id' => 123456789,
                'is_bot' => true,
                'first_name' => 'TestBot',
                'username' => 'test_bot',
                'can_join_groups' => true,
                'can_read_all_group_messages' => false,
                'supports_inline_queries' => false,
            ],
        ]),
    ]);

    $this->artisan('telegram:get-me')
        ->assertSuccessful()
        ->expectsOutputToContain('TestBot');
});

it('handles API error gracefully', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Unauthorized',
        ], 401),
    ]);

    $this->artisan('telegram:get-me')
        ->assertFailed();
});

it('fails when API returns ok false with 200 status', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bot token invalid',
        ], 200),
    ]);

    $this->artisan('telegram:get-me')
        ->assertFailed()
        ->expectsOutputToContain('Failed to get bot info');
});

it('accepts bot option', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [
                'id' => 987654321,
                'is_bot' => true,
                'first_name' => 'AlertsBot',
                'username' => 'alerts_bot',
            ],
        ]),
    ]);

    $this->artisan('telegram:get-me', ['--bot' => 'alerts'])
        ->assertSuccessful();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'botalerts-token-456'));
});
