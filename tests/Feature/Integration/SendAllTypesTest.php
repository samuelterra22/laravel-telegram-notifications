<?php

declare(strict_types=1);

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Channels\TelegramChannel;
use SamuelTerra22\TelegramNotifications\Messages\TelegramAnimation;
use SamuelTerra22\TelegramNotifications\Messages\TelegramAudio;
use SamuelTerra22\TelegramNotifications\Messages\TelegramContact;
use SamuelTerra22\TelegramNotifications\Messages\TelegramDice;
use SamuelTerra22\TelegramNotifications\Messages\TelegramDocument;
use SamuelTerra22\TelegramNotifications\Messages\TelegramLocation;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPhoto;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPoll;
use SamuelTerra22\TelegramNotifications\Messages\TelegramSticker;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVenue;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVideo;
use SamuelTerra22\TelegramNotifications\Messages\TelegramVoice;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ]),
    ]);
});

// --- TelegramMessage ---

it('builds TelegramMessage toArray with all fields', function () {
    $message = TelegramMessage::create('Hello World')
        ->to('-100123')
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('text', 'Hello World')
        ->toHaveKey('parse_mode', 'HTML')
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendMessage');
});

it('sends TelegramMessage through TelegramChannel', function () {
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
            return TelegramMessage::create('Channel test');
        }
    };

    $result = $channel->send($notifiable, $notification);

    expect($result)->toBeArray()
        ->and($result['ok'])->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
        && $request['text'] === 'Channel test'
    );
});

// --- TelegramPhoto ---

it('builds TelegramPhoto toArray with all fields', function () {
    $message = TelegramPhoto::create()
        ->to('-100123')
        ->photo('https://example.com/photo.jpg')
        ->caption('A nice photo')
        ->spoiler()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('photo', 'https://example.com/photo.jpg')
        ->toHaveKey('caption', 'A nice photo')
        ->toHaveKey('parse_mode', 'HTML')
        ->toHaveKey('has_spoiler', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendPhoto');
});

it('sends TelegramPhoto through TelegramChannel', function () {
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
                ->photo('https://example.com/pic.jpg')
                ->caption('Photo caption');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPhoto')
        && $request['photo'] === 'https://example.com/pic.jpg'
        && $request['caption'] === 'Photo caption'
    );
});

// --- TelegramDocument ---

it('builds TelegramDocument toArray with all fields', function () {
    $message = TelegramDocument::create()
        ->to('-100123')
        ->document('https://example.com/file.pdf')
        ->caption('A document')
        ->thumbnail('https://example.com/thumb.jpg')
        ->disableContentTypeDetection()
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('document', 'https://example.com/file.pdf')
        ->toHaveKey('caption', 'A document')
        ->toHaveKey('parse_mode', 'HTML')
        ->toHaveKey('thumbnail', 'https://example.com/thumb.jpg')
        ->toHaveKey('disable_content_type_detection', true)
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendDocument');
});

it('sends TelegramDocument through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramDocument
        {
            return TelegramDocument::create()
                ->document('https://example.com/file.pdf')
                ->caption('Document');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendDocument')
        && $request['document'] === 'https://example.com/file.pdf'
    );
});

// --- TelegramVideo ---

it('builds TelegramVideo toArray with all fields', function () {
    $message = TelegramVideo::create()
        ->to('-100123')
        ->video('https://example.com/video.mp4')
        ->caption('A video')
        ->duration(120)
        ->dimensions(1920, 1080)
        ->thumbnail('https://example.com/thumb.jpg')
        ->spoiler()
        ->streaming()
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('video', 'https://example.com/video.mp4')
        ->toHaveKey('caption', 'A video')
        ->toHaveKey('parse_mode', 'HTML')
        ->toHaveKey('duration', 120)
        ->toHaveKey('width', 1920)
        ->toHaveKey('height', 1080)
        ->toHaveKey('thumbnail', 'https://example.com/thumb.jpg')
        ->toHaveKey('has_spoiler', true)
        ->toHaveKey('supports_streaming', true)
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendVideo');
});

it('sends TelegramVideo through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramVideo
        {
            return TelegramVideo::create()
                ->video('https://example.com/video.mp4')
                ->caption('Video');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendVideo')
        && $request['video'] === 'https://example.com/video.mp4'
    );
});

// --- TelegramAudio ---

it('builds TelegramAudio toArray with all fields', function () {
    $message = TelegramAudio::create()
        ->to('-100123')
        ->audio('https://example.com/audio.mp3')
        ->caption('A song')
        ->duration(300)
        ->performer('Artist')
        ->title('Song Title')
        ->thumbnail('https://example.com/cover.jpg')
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('audio', 'https://example.com/audio.mp3')
        ->toHaveKey('caption', 'A song')
        ->toHaveKey('parse_mode', 'HTML')
        ->toHaveKey('duration', 300)
        ->toHaveKey('performer', 'Artist')
        ->toHaveKey('title', 'Song Title')
        ->toHaveKey('thumbnail', 'https://example.com/cover.jpg')
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendAudio');
});

it('sends TelegramAudio through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramAudio
        {
            return TelegramAudio::create()
                ->audio('https://example.com/audio.mp3')
                ->caption('Audio');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendAudio')
        && $request['audio'] === 'https://example.com/audio.mp3'
    );
});

// --- TelegramVoice ---

it('builds TelegramVoice toArray with all fields', function () {
    $message = TelegramVoice::create()
        ->to('-100123')
        ->voice('https://example.com/voice.ogg')
        ->caption('Voice message')
        ->duration(30)
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('voice', 'https://example.com/voice.ogg')
        ->toHaveKey('caption', 'Voice message')
        ->toHaveKey('parse_mode', 'HTML')
        ->toHaveKey('duration', 30)
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendVoice');
});

it('sends TelegramVoice through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramVoice
        {
            return TelegramVoice::create()
                ->voice('https://example.com/voice.ogg')
                ->caption('Voice');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendVoice')
        && $request['voice'] === 'https://example.com/voice.ogg'
    );
});

// --- TelegramAnimation ---

it('builds TelegramAnimation toArray with all fields', function () {
    $message = TelegramAnimation::create()
        ->to('-100123')
        ->animation('https://example.com/anim.gif')
        ->caption('A GIF')
        ->duration(5)
        ->width(320)
        ->height(240)
        ->thumbnail('https://example.com/thumb.jpg')
        ->spoiler()
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('animation', 'https://example.com/anim.gif')
        ->toHaveKey('caption', 'A GIF')
        ->toHaveKey('parse_mode', 'HTML')
        ->toHaveKey('duration', 5)
        ->toHaveKey('width', 320)
        ->toHaveKey('height', 240)
        ->toHaveKey('thumbnail', 'https://example.com/thumb.jpg')
        ->toHaveKey('has_spoiler', true)
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendAnimation');
});

it('sends TelegramAnimation through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramAnimation
        {
            return TelegramAnimation::create()
                ->animation('https://example.com/anim.gif')
                ->caption('Animation');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendAnimation')
        && $request['animation'] === 'https://example.com/anim.gif'
    );
});

// --- TelegramLocation ---

it('builds TelegramLocation toArray with all fields', function () {
    $message = TelegramLocation::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->horizontalAccuracy(10.5)
        ->livePeriod(3600)
        ->heading(180)
        ->proximityAlertRadius(100)
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('latitude', -23.5505)
        ->toHaveKey('longitude', -46.6333)
        ->toHaveKey('horizontal_accuracy', 10.5)
        ->toHaveKey('live_period', 3600)
        ->toHaveKey('heading', 180)
        ->toHaveKey('proximity_alert_radius', 100)
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendLocation');
});

it('sends TelegramLocation through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramLocation
        {
            return TelegramLocation::create()
                ->coordinates(-23.5505, -46.6333);
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendLocation')
        && $request['latitude'] === -23.5505
    );
});

// --- TelegramVenue ---

it('builds TelegramVenue toArray with all fields', function () {
    $message = TelegramVenue::create()
        ->to('-100123')
        ->coordinates(-23.5505, -46.6333)
        ->title('My Venue')
        ->address('123 Main St')
        ->foursquareId('4bf58dd8d48988d1c4941735')
        ->foursquareType('food/restaurant')
        ->googlePlaceId('ChIJN1t_tDeuEmsRUsoyG83frY4')
        ->googlePlaceType('restaurant')
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('latitude', -23.5505)
        ->toHaveKey('longitude', -46.6333)
        ->toHaveKey('title', 'My Venue')
        ->toHaveKey('address', '123 Main St')
        ->toHaveKey('foursquare_id', '4bf58dd8d48988d1c4941735')
        ->toHaveKey('foursquare_type', 'food/restaurant')
        ->toHaveKey('google_place_id', 'ChIJN1t_tDeuEmsRUsoyG83frY4')
        ->toHaveKey('google_place_type', 'restaurant')
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendVenue');
});

it('sends TelegramVenue through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramVenue
        {
            return TelegramVenue::create()
                ->coordinates(-23.5505, -46.6333)
                ->title('Venue')
                ->address('Address');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendVenue')
        && $request['title'] === 'Venue'
    );
});

// --- TelegramContact ---

it('builds TelegramContact toArray with all fields', function () {
    $message = TelegramContact::create()
        ->to('-100123')
        ->phoneNumber('+5511999999999')
        ->firstName('Samuel')
        ->lastName('Terra')
        ->vcard('BEGIN:VCARD\nVERSION:3.0\nEND:VCARD')
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('phone_number', '+5511999999999')
        ->toHaveKey('first_name', 'Samuel')
        ->toHaveKey('last_name', 'Terra')
        ->toHaveKey('vcard', 'BEGIN:VCARD\nVERSION:3.0\nEND:VCARD')
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendContact');
});

it('sends TelegramContact through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramContact
        {
            return TelegramContact::create()
                ->phoneNumber('+5511999999999')
                ->firstName('Samuel');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendContact')
        && $request['phone_number'] === '+5511999999999'
    );
});

// --- TelegramPoll ---

it('builds TelegramPoll toArray with all fields', function () {
    $message = TelegramPoll::create()
        ->to('-100123')
        ->question('Favorite color?')
        ->options(['Red', 'Blue', 'Green'])
        ->anonymous(false)
        ->allowsMultipleAnswers()
        ->openPeriod(300)
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('question', 'Favorite color?')
        ->toHaveKey('options')
        ->toHaveKey('is_anonymous', false)
        ->toHaveKey('allows_multiple_answers', true)
        ->toHaveKey('open_period', 300)
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($array['options'])->toBe([
        ['text' => 'Red'],
        ['text' => 'Blue'],
        ['text' => 'Green'],
    ]);

    expect($message->getApiMethod())->toBe('sendPoll');
});

it('sends TelegramPoll through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramPoll
        {
            return TelegramPoll::create()
                ->question('Which?')
                ->options(['A', 'B']);
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendPoll')
        && $request['question'] === 'Which?'
    );
});

// --- TelegramSticker ---

it('builds TelegramSticker toArray with all fields', function () {
    $message = TelegramSticker::create()
        ->to('-100123')
        ->sticker('CAACAgIAAxkBAAI')
        ->emoji("\xF0\x9F\x98\x80")
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('sticker', 'CAACAgIAAxkBAAI')
        ->toHaveKey('emoji')
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendSticker');
});

it('sends TelegramSticker through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramSticker
        {
            return TelegramSticker::create()
                ->sticker('CAACAgIAAxkBAAI');
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendSticker')
        && $request['sticker'] === 'CAACAgIAAxkBAAI'
    );
});

// --- TelegramDice ---

it('builds TelegramDice toArray with all fields', function () {
    $message = TelegramDice::create()
        ->to('-100123')
        ->darts()
        ->silent()
        ->protected()
        ->topic('42');

    $array = $message->toArray();

    expect($array)
        ->toHaveKey('chat_id', '-100123')
        ->toHaveKey('emoji')
        ->toHaveKey('disable_notification', true)
        ->toHaveKey('protect_content', true)
        ->toHaveKey('message_thread_id', '42');

    expect($message->getApiMethod())->toBe('sendDice');
});

it('sends TelegramDice through TelegramChannel', function () {
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
        public function toTelegram(mixed $notifiable): TelegramDice
        {
            return TelegramDice::create()
                ->basketball();
        }
    };

    $channel->send($notifiable, $notification);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendDice'));
});
