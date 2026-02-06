<?php

declare(strict_types=1);

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Channels\TelegramChannel;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPhoto;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ]),
    ]);
});

it('sends a notification via telegram channel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramMessage
        {
            return TelegramMessage::create('Test notification');
        }
    };

    $result = $channel->send($notifiable, $notification);

    expect($result)->toBeArray()
        ->and($result['ok'])->toBeTrue();

    Http::assertSent(fn ($request) => $request['text'] === 'Test notification'
        && $request['chat_id'] === '-1001234567890'
    );
});

it('uses chat_id from message if set', function () {
    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '-100fallback';
        }
    };

    $notification = new class extends Notification
    {
        public function toTelegram(mixed $notifiable): TelegramMessage
        {
            return TelegramMessage::create('Test')
                ->to('-100frommessage');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => $request['chat_id'] === '-100frommessage');
});

it('returns null when no chat_id is available', function () {
    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): ?string
        {
            return null;
        }
    };

    $notification = new class extends Notification
    {
        public function toTelegram(mixed $notifiable): TelegramMessage
        {
            return TelegramMessage::create('Test');
        }
    };

    $result = $channel->send($notifiable, $notification);

    expect($result)->toBeNull();
});

it('sends a photo notification', function () {
    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '-100123';
        }
    };

    $notification = new class extends Notification
    {
        public function toTelegram(mixed $notifiable): TelegramPhoto
        {
            return TelegramPhoto::create()
                ->photo('https://example.com/photo.jpg')
                ->caption('A photo');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPhoto')
        && $request['photo'] === 'https://example.com/photo.jpg'
    );
});

it('uses a specific bot for the notification', function () {
    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '-100123';
        }
    };

    $notification = new class extends Notification
    {
        public function toTelegram(mixed $notifiable): TelegramMessage
        {
            return TelegramMessage::create('Alert!')
                ->bot('alerts');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'botalerts-token-456'));
});

it('splits long messages automatically', function () {
    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '-100123';
        }
    };

    $longText = str_repeat('A', 5000);

    $notification = new class($longText) extends Notification
    {
        public function __construct(private string $text) {}

        public function toTelegram(mixed $notifiable): TelegramMessage
        {
            return TelegramMessage::create($this->text);
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSentCount(2);
});

it('sends notification with inline keyboard', function () {
    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '-100123';
        }
    };

    $notification = new class extends Notification
    {
        public function toTelegram(mixed $notifiable): TelegramMessage
        {
            return TelegramMessage::create('Choose:')
                ->button('Option A', 'https://a.com')
                ->button('Option B', 'https://b.com');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => isset($request['reply_markup']));
});

it('sends notification with topic', function () {
    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '-100123';
        }
    };

    $notification = new class extends Notification
    {
        public function toTelegram(mixed $notifiable): TelegramMessage
        {
            return TelegramMessage::create('Forum post')
                ->topic('42');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => $request['message_thread_id'] === '42');
});
