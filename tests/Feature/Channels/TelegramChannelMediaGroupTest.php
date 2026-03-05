<?php

declare(strict_types=1);

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Channels\TelegramChannel;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMediaGroup;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [['message_id' => 1], ['message_id' => 2]],
        ]),
    ]);
});

it('sends a media group via notification channel', function () {
    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '-1001234567890';
        }
    };

    $notification = new class extends Notification
    {
        public function toTelegram(mixed $notifiable): TelegramMediaGroup
        {
            return TelegramMediaGroup::create()
                ->photo('https://example.com/photo1.jpg', 'First photo')
                ->photo('https://example.com/photo2.jpg', 'Second photo');
        }
    };

    $result = $channel->send($notifiable, $notification);

    expect($result)->toBeArray()
        ->and($result['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        $data = $request->data();
        $body = (string) $request->body();

        return str_contains($request->url(), 'sendMediaGroup')
            && str_contains($body, '-1001234567890');
    });
});
