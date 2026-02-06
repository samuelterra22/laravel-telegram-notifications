# Laravel Telegram Notifications

[![Tests](https://github.com/samuelterra22/laravel-telegram-notifications/actions/workflows/run-tests.yml/badge.svg)](https://github.com/samuelterra22/laravel-telegram-notifications/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/samuelterra22/laravel-telegram-notifications/actions/workflows/phpstan.yml/badge.svg)](https://github.com/samuelterra22/laravel-telegram-notifications/actions/workflows/phpstan.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/samuelterra22/laravel-telegram-notifications.svg)](https://packagist.org/packages/samuelterra22/laravel-telegram-notifications)
[![Total Downloads](https://img.shields.io/packagist/dt/samuelterra22/laravel-telegram-notifications.svg)](https://packagist.org/packages/samuelterra22/laravel-telegram-notifications)
[![License](https://img.shields.io/packagist/l/samuelterra22/laravel-telegram-notifications.svg)](https://packagist.org/packages/samuelterra22/laravel-telegram-notifications)

A complete Laravel package for integrating the Telegram Bot API with Laravel applications. Send messages, notifications, log errors, use interactive keyboards, support multiple bots and forum topics.

## Features

- **14 message types**: text, photo, document, video, audio, voice, animation, location, venue, contact, poll, sticker, dice
- **Laravel Notifications channel**: use `toTelegram()` in your notification classes
- **Monolog log handler**: send error logs directly to Telegram with emoji, stack traces, and app context
- **Multi-bot support**: configure and use multiple bots simultaneously
- **Forum/Topics support**: send messages to specific forum topics via `message_thread_id`
- **Interactive keyboards**: inline keyboards and reply keyboards with fluent builders
- **Fluent API**: build messages with an expressive chainable interface
- **Auto message splitting**: messages exceeding 4096 characters are split automatically
- **Rate limiting**: built-in retry-after handling for HTTP 429 responses
- **Fully testable**: all HTTP calls via Laravel's `Http::` facade, easily mocked with `Http::fake()`
- **Zero external dependencies**: uses Laravel's built-in HTTP client (no Guzzle direct dependency)
- **99.8% test coverage**: 300 tests with 699 assertions

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require samuelterra22/laravel-telegram-notifications
```

Publish the config file:

```bash
php artisan vendor:publish --tag=telegram-notifications-config
```

## Configuration

Add to your `.env`:

```env
TELEGRAM_BOT_TOKEN=your-bot-token
TELEGRAM_CHAT_ID=-1001234567890
TELEGRAM_TOPIC_ID=42
```

The config file `config/telegram-notifications.php` supports multiple bots:

```php
'bots' => [
    'default' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'topic_id' => env('TELEGRAM_TOPIC_ID'),
    ],
    'alerts' => [
        'token' => env('TELEGRAM_ALERTS_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_ALERTS_CHAT_ID'),
    ],
],
```

## Usage

### Send Messages via Facade

```php
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

// Simple message
Telegram::sendMessage('-1001234567890', 'Hello from Laravel!');

// Message to a forum topic
Telegram::sendMessage('-1001234567890', 'Error report', topicId: '42');

// Using a specific bot
Telegram::bot('alerts')->call('sendMessage', [
    'chat_id' => '-1001234567890',
    'text' => 'ALERT!',
]);
```

### Laravel Notifications

```php
use Illuminate\Notifications\Notification;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;

class OrderShipped extends Notification
{
    public function via(mixed $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(mixed $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->bold('Order Shipped!')
            ->line('')
            ->line("Order #{$this->order->id}")
            ->line("Tracking: {$this->order->tracking_code}")
            ->button('Track Order', $this->order->tracking_url)
            ->button('View Order', route('orders.show', $this->order));
    }
}

// Send to a user
$user->notify(new OrderShipped($order));

// On-demand (without a model)
Notification::route('telegram', '-1001234567890')
    ->notify(new OrderShipped($order));
```

Add the `routeNotificationForTelegram` method to your notifiable model:

```php
public function routeNotificationForTelegram(): ?string
{
    return $this->telegram_chat_id;
}
```

### Message Types

#### Text Message

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;

TelegramMessage::create()
    ->to('-1001234567890')
    ->bold('Title')
    ->line('Regular text')
    ->italic('Italic text')
    ->code('inline_code()')
    ->pre('code block', 'php')
    ->link('Click here', 'https://example.com')
    ->spoiler('Hidden text')
    ->quote('A quote')
    ->silent()
    ->protected();
```

#### Photo

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramPhoto;

TelegramPhoto::create()
    ->to('-1001234567890')
    ->photo('https://example.com/image.jpg')
    ->caption('Photo caption')
    ->spoiler();
```

#### Document

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramDocument;

TelegramDocument::create()
    ->to('-1001234567890')
    ->document('https://example.com/report.pdf')
    ->caption('Monthly report')
    ->thumbnail('https://example.com/thumb.jpg')
    ->disableContentTypeDetection();
```

#### Video

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramVideo;

TelegramVideo::create()
    ->to('-1001234567890')
    ->video('https://example.com/video.mp4')
    ->caption('Check this out!')
    ->duration(120)
    ->width(1920)
    ->height(1080)
    ->supportsStreaming()
    ->spoiler();
```

#### Audio

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramAudio;

TelegramAudio::create()
    ->to('-1001234567890')
    ->audio('https://example.com/song.mp3')
    ->caption('Now playing')
    ->performer('Artist Name')
    ->title('Song Title')
    ->duration(240);
```

#### Voice

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramVoice;

TelegramVoice::create()
    ->to('-1001234567890')
    ->voice('https://example.com/voice.ogg')
    ->caption('Voice message')
    ->duration(30);
```

#### Animation (GIF)

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramAnimation;

TelegramAnimation::create()
    ->to('-1001234567890')
    ->animation('https://example.com/animation.gif')
    ->caption('Funny GIF')
    ->width(320)
    ->height(240)
    ->spoiler();
```

#### Location

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramLocation;

TelegramLocation::create()
    ->to('-1001234567890')
    ->coordinates(-23.5505, -46.6333)
    ->livePeriod(3600);
```

#### Venue

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramVenue;

TelegramVenue::create()
    ->to('-1001234567890')
    ->coordinates(-23.5505, -46.6333)
    ->title('Ibirapuera Park')
    ->address('Av. Pedro Alvares Cabral, Sao Paulo')
    ->foursquareId('4b5bc7eef964a520e22529e3');
```

#### Contact

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramContact;

TelegramContact::create()
    ->to('-1001234567890')
    ->phoneNumber('+5511999999999')
    ->firstName('Samuel')
    ->lastName('Terra')
    ->vcard('BEGIN:VCARD\nVERSION:3.0\nFN:Samuel Terra\nEND:VCARD');
```

#### Poll

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramPoll;

TelegramPoll::create()
    ->to('-1001234567890')
    ->question('What time works best?')
    ->options(['08:00', '10:00', '14:00', '16:00'])
    ->allowsMultipleAnswers();
```

#### Sticker

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramSticker;

TelegramSticker::create()
    ->to('-1001234567890')
    ->sticker('CAACAgIAAxkBAAI...')  // sticker file_id or URL
    ->emoji('😀');
```

#### Dice

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramDice;

TelegramDice::create()
    ->to('-1001234567890')
    ->dice();       // 🎲
    // ->darts()    // 🎯
    // ->basketball() // 🏀
    // ->football()   // ⚽
    // ->bowling()    // 🎳
    // ->slotMachine() // 🎰
```

### Interactive Keyboards

#### Inline Keyboard

```php
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;

$keyboard = InlineKeyboard::make()
    ->url('Open App', 'https://app.example.com')
    ->url('Docs', 'https://docs.example.com')
    ->row()
    ->callback('Confirm', 'action:confirm:123')
    ->callback('Cancel', 'action:cancel:123');

TelegramMessage::create()
    ->to('-1001234567890')
    ->content('Choose an option:')
    ->keyboard($keyboard);
```

#### Reply Keyboard

```php
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboard;

$keyboard = ReplyKeyboard::make()
    ->button('Option A')
    ->button('Option B')
    ->row()
    ->requestContact('Share Contact')
    ->requestLocation('Share Location')
    ->oneTime()
    ->resize()
    ->placeholder('Choose...');
```

### Edit, Delete and Forward Messages

```php
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

// Edit message text
Telegram::editMessageText($chatId, $messageId, 'Updated text');

// Edit caption
Telegram::editMessageCaption($chatId, $messageId, 'New caption');

// Edit reply markup (keyboard)
Telegram::editMessageReplyMarkup($chatId, $messageId, $inlineKeyboard->toArray());

// Delete a message
Telegram::deleteMessage($chatId, $messageId);

// Delete multiple messages
Telegram::deleteMessages($chatId, [$msgId1, $msgId2, $msgId3]);

// Forward a message
Telegram::forwardMessage($toChatId, $fromChatId, $messageId);

// Copy a message
Telegram::copyMessage($toChatId, $fromChatId, $messageId);
```

### Chat Actions

```php
use SamuelTerra22\TelegramNotifications\Enums\ChatAction;
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

Telegram::sendChatAction($chatId, ChatAction::Typing);
Telegram::sendChatAction($chatId, ChatAction::UploadDocument);
Telegram::sendChatAction($chatId, ChatAction::UploadPhoto);
Telegram::sendChatAction($chatId, ChatAction::RecordVideo);
```

### Chat Management

```php
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

// Get chat info
Telegram::getChat($chatId);

// Get chat member info
Telegram::getChatMember($chatId, $userId);

// Get member count
Telegram::getChatMemberCount($chatId);

// Pin/unpin messages
Telegram::pinChatMessage($chatId, $messageId);
Telegram::unpinChatMessage($chatId, $messageId);
Telegram::unpinAllChatMessages($chatId);
```

### Bot Management

```php
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

// Get bot info
Telegram::getMe();

// Set bot commands
Telegram::setMyCommands([
    ['command' => 'start', 'description' => 'Start the bot'],
    ['command' => 'help', 'description' => 'Show help'],
]);

// Get/delete bot commands
Telegram::getMyCommands();
Telegram::deleteMyCommands();

// Get file for download
Telegram::getFile($fileId);
```

### Webhook Management

```php
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

Telegram::setWebhook('https://example.com/webhook', secretToken: 'my-secret');
Telegram::getWebhookInfo();
Telegram::deleteWebhook(dropPendingUpdates: true);
```

### Log Handler (Errors to Telegram)

Add the Telegram channel to `config/logging.php`:

```php
'channels' => [
    // ...
    'telegram' => [
        'driver' => 'custom',
        'via' => \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger::class,
        'level' => env('LOG_TELEGRAM_LEVEL', 'error'),
    ],
],
```

Configure in `.env`:

```env
TELEGRAM_LOG_ENABLED=true
TELEGRAM_LOG_CHAT_ID=-1001234567890
TELEGRAM_LOG_TOPIC_ID=99
LOG_TELEGRAM_LEVEL=error
```

Use in your exception handler (`bootstrap/app.php`):

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->reportable(function (\Throwable $e) {
        try {
            if (config('telegram-notifications.logging.enabled')) {
                Log::channel('telegram')->error($e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        } catch (\Throwable) {
            // Never break the application
        }
    });
})
```

Log messages include: level emoji, app name, environment, message text, exception class, file/line, and truncated stack trace (max 4096 chars).

### Artisan Commands

```bash
# Set webhook
php artisan telegram:set-webhook --url=https://example.com/telegram/webhook

# Set webhook with secret token
php artisan telegram:set-webhook --url=https://example.com/webhook --secret=my-secret

# Delete webhook
php artisan telegram:set-webhook --delete

# Delete webhook and drop pending updates
php artisan telegram:set-webhook --delete --drop-pending

# Get bot information
php artisan telegram:get-me

# Use a specific bot
php artisan telegram:get-me --bot=alerts
```

## Testing

The package uses Laravel's `Http::fake()` for all API calls, making it trivial to test:

```php
use Illuminate\Support\Facades\Http;

// Test that a notification was sent
Http::fake([
    'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
]);

$user->notify(new OrderShipped($order));

Http::assertSent(function ($request) {
    return str_contains($request->url(), '/sendMessage')
        && str_contains($request['text'], 'Order Shipped');
});
```

## Development

```bash
# Build Docker image
make build

# Install dependencies
make install

# Run tests
make test

# Run tests with coverage
make test-coverage

# Format code (Laravel Pint)
make format

# Static analysis (PHPStan level 5)
make analyse
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please ensure tests pass and code follows the project style (Laravel Pint).

This project uses [conventional commits](https://www.conventionalcommits.org/) for automatic versioning:

- `fix: description` — patch release
- `feat: description` — minor release
- `feat!: description` — major release

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
