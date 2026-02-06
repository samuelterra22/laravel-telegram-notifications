# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel package (`samuelterra22/laravel-telegram-notifications`) for integrating the Telegram Bot API with Laravel applications. Supports sending all message types, Laravel Notifications channel, Monolog logging handler, multi-bot support, interactive keyboards, and forum/topic support.

- **Namespace**: `SamuelTerra22\TelegramNotifications`
- **Config file**: `telegram-notifications.php`
- **Facade**: `Telegram`
- **Compatibility**: PHP 8.1+, Laravel 10/11/12
- **Package tooling**: `spatie/laravel-package-tools`

The full specification is in `Plano_de_Desenvolvimento_do_Pacote_Telegram.md` (Portuguese). Refer to it for detailed implementation guidance, code examples, and API coverage plans.

## Development Commands

Docker-based development (PHP 8.4 CLI Alpine):

```bash
make build                          # Build Docker image
make install                        # Install Composer dependencies
make test                           # Run all tests
make test-coverage                  # Tests with HTML coverage report
make test-filter FILTER=BotApi      # Run filtered tests
make format                         # Format code with Laravel Pint
make analyse                        # Static analysis with PHPStan
make shell                          # Open shell in container
make clean                          # Remove containers and volumes
```

Without Docker:

```bash
composer test                       # vendor/bin/pest
composer test-coverage              # vendor/bin/pest --coverage --min=95
composer format                     # vendor/bin/pint
composer analyse                    # vendor/bin/phpstan
vendor/bin/pest --filter=ClassName  # Run a single test file
```

## Architecture

Three entry points funnel into a single HTTP client:

```
Notification->toTelegram()  ──> TelegramChannel ──┐
Log::channel('telegram')    ──> TelegramHandler ──┼──> TelegramBotApi ──> api.telegram.org
Telegram::sendMessage()     ──> Telegram service ─┘
```

**Key components**:

- `Api/TelegramBotApi` — Low-level HTTP client using Laravel's `Http::` facade. Methods: `call()` (throws on error), `callSilent()` (never throws, for logging), `upload()` (multipart). All HTTP is through `Http::` so tests use `Http::fake()`.
- `Telegram` — Main service. Manages multiple bot instances (lazy-loaded from config). Provides high-level convenience methods (`sendMessage`, `editMessageText`, `deleteMessage`, etc.).
- `Messages/*` — Fluent builder classes (TelegramMessage, TelegramPhoto, TelegramDocument, etc.) implementing `TelegramMessageInterface`. All use the `HasSharedParams` trait for `chatId`, `topicId`, and `bot`.
- `Keyboards/InlineKeyboard`, `ReplyKeyboard`, `Button` — Fluent builders for interactive keyboards.
- `Channels/TelegramChannel` — Laravel Notification channel. Calls `$notification->toTelegram($notifiable)` and resolves `chat_id` from message or `routeNotificationForTelegram()`.
- `Logging/TelegramHandler` — Monolog `AbstractProcessingHandler`. Formats logs with emoji/level/app/environment. Uses `callSilent()` (never crashes the app).
- `Logging/CreateTelegramLogger` — Factory for Laravel's `config/logging.php` custom driver.
- `TelegramServiceProvider` — Uses `spatie/laravel-package-tools`. Registers singletons, publishes config, registers Artisan commands.

## Design Principles

- **All HTTP via `Http::` facade** — mock with `Http::fake()` in tests, zero real API calls
- **Fluent builder pattern** on all message and keyboard classes
- **Never throw on send** for logging paths (`callSilent`); throw `TelegramApiException` on regular API calls
- **TDD with Pest** — write test first, then implement. Target 95%+ coverage.
- **`declare(strict_types=1)`** on every file

## Testing

- **Framework**: Pest 2/3 with Orchestra Testbench
- **Base class**: `tests/TestCase.php` extends `Orchestra\Testbench\TestCase`, loads the service provider and sets test config
- **Architecture tests**: `tests/ArchTest.php` — no debug functions, strict types, contracts are interfaces, enums are enums
- **Unit tests** (~80): API client, message builders, keyboards, enums, exceptions
- **Feature tests** (~50): Service provider, notification channel, Monolog handler, Artisan commands, integration flows

## CI Matrix

GitHub Actions runs tests across PHP 8.1–8.4 × Laravel 10–12 × prefer-lowest/prefer-stable (20 combinations). Coverage check (95% min) runs on PHP 8.4 + Laravel 12 + prefer-stable.

## Implementation Phases

1. **Foundation** — ServiceProvider, config, facade, TelegramBotApi, Telegram service, enums, exceptions
2. **Messages** — 14 message type builders with HasSharedParams trait, auto-splitting >4096 chars
3. **Keyboards** — InlineKeyboard, ReplyKeyboard, Button builders
4. **Laravel Integration** — TelegramChannel (notifications), TelegramHandler + CreateTelegramLogger (logging)
5. **Advanced** — Edit/delete/forward messages, Artisan commands (webhook, get-me), rate limiting (429 retry-after)
6. **Documentation & Release** — README, CHANGELOG, PHPStan level 5, Packagist publication
