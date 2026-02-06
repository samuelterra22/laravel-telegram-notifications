<?php

declare(strict_types=1);

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Channels\TelegramChannel;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPhoto;

it('propagates TelegramApiException when API fails during send', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: chat not found',
        ], 400),
    ]);

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
            return TelegramMessage::create('Test');
        }
    };

    $channel->send($notifiable, $notification);
})->throws(TelegramApiException::class, 'chat not found');

it('throws InvalidArgumentException for invalid bot name in message', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

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
            return TelegramMessage::create('Test')->bot('nonexistent_bot');
        }
    };

    $channel->send($notifiable, $notification);
})->throws(InvalidArgumentException::class, 'Bot [nonexistent_bot] not configured.');

it('returns null when routeNotificationFor returns empty string', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '';
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
    Http::assertNothingSent();
});

it('returns null when routeNotificationFor returns 0', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): int
        {
            return 0;
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
    Http::assertNothingSent();
});

it('throws when second chunk of multi-chunk message fails', function () {
    $callCount = 0;
    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'ok' => true,
                'result' => ['message_id' => 1],
            ]);
        }

        return Http::response([
            'ok' => false,
            'description' => 'Bad Request: message is too long',
        ], 400);
    });

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
})->throws(TelegramApiException::class);

it('sends photo notification without split logic', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

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
                ->caption('A caption');
        }
    };

    $result = $channel->send($notifiable, $notification);

    expect($result['ok'])->toBeTrue();
    Http::assertSentCount(1);
});

it('returns null when both chat_id sources return null', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

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
            // No chatId set on message, routeNotificationFor returns null
            return TelegramMessage::create('Test');
        }
    };

    $result = $channel->send($notifiable, $notification);

    expect($result)->toBeNull();
    Http::assertNothingSent();
});

it('includes topic_id in params when set on message', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

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

it('non-TelegramMessage types skip splitContent logic', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

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
                ->photo('https://example.com/img.jpg')
                ->caption('A caption');
        }
    };

    $result = $channel->send($notifiable, $notification);

    expect($result['ok'])->toBeTrue();
    Http::assertSentCount(1);
});

it('chat ID from message takes precedence over notifiable', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

    $channel = app(TelegramChannel::class);

    $notifiable = new class
    {
        public function routeNotificationFor(string $driver): string
        {
            return '-100NOTIFIABLE';
        }
    };

    $notification = new class extends Notification
    {
        public function toTelegram(mixed $notifiable): TelegramMessage
        {
            return TelegramMessage::create('Test')->to('-100MESSAGE');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => $request['chat_id'] === '-100MESSAGE');
});

it('multi-chunk sends keyboard only on first chunk', function () {
    $sentRequests = [];

    Http::fake(function ($request) use (&$sentRequests) {
        $sentRequests[] = $request;

        return Http::response(['ok' => true, 'result' => ['message_id' => 1]]);
    });

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
            return TelegramMessage::create($this->text)
                ->button('Click', 'https://example.com');
        }
    };

    $channel->send($notifiable, $notification);

    expect($sentRequests)->toHaveCount(2);
    expect($sentRequests[0]->data())->toHaveKey('reply_markup');
    expect($sentRequests[1]->data())->not->toHaveKey('reply_markup');
});

it('single chunk message does not trigger split path', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
    ]);

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
            return TelegramMessage::create('Short message');
        }
    };

    $result = $channel->send($notifiable, $notification);

    expect($result['ok'])->toBeTrue();
    Http::assertSentCount(1);
});

it('throws TelegramApiException on single message API error', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => false,
            'description' => 'Forbidden: bot was blocked by the user',
        ], 403),
    ]);

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
            return TelegramMessage::create('Test');
        }
    };

    $channel->send($notifiable, $notification);
})->throws(TelegramApiException::class, 'Forbidden: bot was blocked by the user');
