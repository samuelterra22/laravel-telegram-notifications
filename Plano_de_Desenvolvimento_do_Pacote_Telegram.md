# Plano de Desenvolvimento: `samuelterra22/laravel-telegram-notifications`

> Pacote Laravel completo para integrar o Telegram Bot API com aplicacoes Laravel.
> Suporta envio de mensagens, notificacoes, log de erros, teclados interativos e muito mais.

---

## Sumario

1. [Visao Geral](#1-visao-geral)
2. [Matriz de Compatibilidade](#2-matriz-de-compatibilidade)
3. [Arquitetura do Pacote](#3-arquitetura-do-pacote)
4. [Estrutura de Diretorios](#4-estrutura-de-diretorios)
5. [Componentes Principais](#5-componentes-principais)
6. [Configuracao](#6-configuracao)
7. [API Publica e Exemplos de Uso](#7-api-publica-e-exemplos-de-uso)
8. [Cobertura da Telegram Bot API](#8-cobertura-da-telegram-bot-api)
9. [Estrategia de Testes (TDD)](#9-estrategia-de-testes-tdd)
10. [Ambiente de Desenvolvimento Local (Docker)](#10-ambiente-de-desenvolvimento-local-docker)
11. [Pipeline CI/CD (GitHub Actions)](#11-pipeline-cicd-github-actions)
12. [Publicacao no Packagist](#12-publicacao-no-packagist)
13. [Fases de Implementacao](#13-fases-de-implementacao)
14. [Migracao do Projeto Basal](#14-migracao-do-projeto-basal)

---

## 1. Visao Geral

### Objetivo

Criar um pacote Laravel robusto, bem testado e documentado que simplifique a integracao com o Telegram Bot API. O pacote deve ser:

- **Completo**: Cobrir as principais funcionalidades da Telegram Bot API (134 metodos disponiveis)
- **Flexivel**: Suportar multiplos bots, canais, grupos, topicos (forum)
- **Integrado**: Funcionar nativamente com Laravel Notifications, Monolog (logging) e o container de servicos
- **Testavel**: 95%+ de cobertura, totalmente testavel com `Http::fake()`
- **Bem documentado**: README completo, exemplos praticos, docblocks em todo o codigo

### Diferenciais em Relacao a Pacotes Existentes

| Aspecto | `laravel-notification-channels/telegram` | **Nosso pacote** |
|---------|------------------------------------------|------------------|
| Monolog log handler | Nao | Sim (built-in) |
| Multi-bot support | Nao | Sim |
| Forum/Topics nativo | Parcial | Completo |
| HTTP client | Guzzle direto | Laravel `Http::` (testavel com `Http::fake()`) |
| Message splitting | Manual (`chunk()`) | Automatico (>4096 chars) |
| Laravel 12 + Filament 4 | Compativel | Otimizado |
| Rate limiting built-in | Nao | Sim (retry-after) |
| Suporte a Keyboards | Basico | Completo (Inline + Reply) |
| Pest tests | PHPUnit | Pest 2/3 nativo |

### Dados do Pacote

| Campo | Valor |
|-------|-------|
| **Nome Packagist** | `samuelterra22/laravel-telegram-notifications` |
| **Namespace** | `SamuelTerra22\TelegramNotifications` |
| **Repositorio** | `github.com/samuelterra22/laravel-telegram-notifications` |
| **Config file** | `telegram-notifications.php` |
| **Facade** | `Telegram` |
| **Licenca** | MIT |

---

## 2. Matriz de Compatibilidade

### PHP e Laravel

| Laravel | PHP Minimo | PHP Maximo | Testbench | Status |
|---------|-----------|-----------|-----------|--------|
| 10.x | 8.1 | 8.3 | 8.x | EOL (suporte mantido) |
| 11.x | 8.2 | 8.4 | 9.x | Ativo |
| 12.x | 8.2 | 8.4+ | 10.x | Ativo |

### Constraint do `composer.json`

```json
{
    "require": {
        "php": "^8.1",
        "illuminate/contracts": "^10.0||^11.0||^12.0",
        "illuminate/http": "^10.0||^11.0||^12.0",
        "illuminate/notifications": "^10.0||^11.0||^12.0",
        "illuminate/support": "^10.0||^11.0||^12.0",
        "monolog/monolog": "^3.0",
        "spatie/laravel-package-tools": "^1.16"
    },
    "require-dev": {
        "laravel/pint": "^1.14",
        "larastan/larastan": "^2.0||^3.0",
        "nunomaduro/collision": "^7.0||^8.0",
        "orchestra/testbench": "^8.0||^9.0||^10.0",
        "pestphp/pest": "^2.34||^3.0",
        "pestphp/pest-plugin-arch": "^2.0||^3.0",
        "pestphp/pest-plugin-laravel": "^2.0||^3.0",
        "phpstan/extension-installer": "^1.4",
        "phpstan/phpstan-deprecation-rules": "^1.0||^2.0",
        "phpstan/phpstan-phpunit": "^1.0||^2.0"
    }
}
```

### Matriz CI (GitHub Actions)

| PHP | Laravel 10.x | Laravel 11.x | Laravel 12.x |
|-----|:---:|:---:|:---:|
| 8.1 | Sim | - | - |
| 8.2 | Sim | Sim | Sim |
| 8.3 | Sim | Sim | Sim |
| 8.4 | - | Sim | Sim |

Cada combinacao e testada com `prefer-lowest` e `prefer-stable` = **20 combinacoes**.

---

## 3. Arquitetura do Pacote

### Diagrama de Componentes

```
+------------------------------------------------------------------+
|                    Laravel Application                            |
+------------------------------------------------------------------+
|                                                                    |
|  +-----------------+    +---------------------+    +-------------+ |
|  | Notification    |    | Log::channel        |    | Facade      | |
|  | ->toTelegram()  |    | ('telegram')        |    | Telegram::  | |
|  +-------+---------+    +----------+----------+    +------+------+ |
|          |                         |                       |       |
|  +-------v---------+    +---------v-----------+    +------v------+ |
|  | TelegramChannel |    | TelegramHandler     |    | Telegram    | |
|  | (Notification)  |    | (Monolog Handler)   |    | (Service)   | |
|  +-------+---------+    +---------+-----------+    +------+------+ |
|          |                         |                       |       |
|          +-------------------------+-----------------------+       |
|                                    |                               |
|                          +---------v-----------+                   |
|                          |   TelegramBotApi    |                   |
|                          |  (HTTP Client)      |                   |
|                          +---------+-----------+                   |
|                                    |                               |
+------------------------------------+-------------------------------+
                                     |
                          +----------v----------+
                          |  Telegram Bot API   |
                          |  api.telegram.org   |
                          +---------------------+
```

### Principios de Design

1. **Single Responsibility**: Cada classe tem uma unica responsabilidade
2. **Dependency Injection**: Tudo via container, testavel com mocks
3. **Fluent API**: Builders com interface fluente para mensagens e teclados
4. **Never Throws on Send**: Operacoes de envio nunca quebram a aplicacao (retornam `false` ou disparam eventos)
5. **Laravel Http::**: Todas as chamadas HTTP via facade do Laravel (testavel com `Http::fake()`)
6. **Config-driven**: Toda configuracao via `config()` e `.env`

---

## 4. Estrutura de Diretorios

```
laravel-telegram-notifications/
+-- .editorconfig
+-- .gitattributes
+-- .gitignore
+-- .github/
|   +-- workflows/
|       +-- run-tests.yml
|       +-- fix-php-code-style-issues.yml
|       +-- phpstan.yml
|       +-- update-changelog.yml
|       +-- dependabot-auto-merge.yml
+-- config/
|   +-- telegram-notifications.php
+-- docker/
|   +-- Dockerfile
+-- src/
|   +-- Api/
|   |   +-- TelegramBotApi.php              # Cliente HTTP de baixo nivel
|   +-- Channels/
|   |   +-- TelegramChannel.php             # Canal de notificacao Laravel
|   +-- Commands/
|   |   +-- TelegramSetWebhookCommand.php   # Artisan: telegram:set-webhook
|   |   +-- TelegramGetMeCommand.php        # Artisan: telegram:get-me
|   +-- Contracts/
|   |   +-- TelegramMessageInterface.php    # Interface para mensagens
|   +-- Enums/
|   |   +-- ParseMode.php                   # HTML, MarkdownV2
|   |   +-- ChatAction.php                  # typing, upload_photo, etc.
|   +-- Exceptions/
|   |   +-- TelegramException.php           # Excecao base
|   |   +-- TelegramApiException.php        # Erros da API (com status code)
|   +-- Facades/
|   |   +-- Telegram.php                    # Facade
|   +-- Keyboards/
|   |   +-- InlineKeyboard.php              # Teclado inline (botoes na mensagem)
|   |   +-- ReplyKeyboard.php               # Teclado customizado (abaixo do input)
|   |   +-- Button.php                      # Botao individual (URL, callback, etc.)
|   +-- Logging/
|   |   +-- TelegramHandler.php             # Monolog AbstractProcessingHandler
|   |   +-- CreateTelegramLogger.php        # Factory para canal de log Laravel
|   +-- Messages/
|   |   +-- TelegramMessage.php             # Mensagem de texto (principal)
|   |   +-- TelegramPhoto.php               # Envio de foto
|   |   +-- TelegramDocument.php            # Envio de documento
|   |   +-- TelegramVideo.php               # Envio de video
|   |   +-- TelegramAudio.php               # Envio de audio
|   |   +-- TelegramVoice.php               # Envio de mensagem de voz
|   |   +-- TelegramAnimation.php           # Envio de GIF
|   |   +-- TelegramLocation.php            # Envio de localizacao
|   |   +-- TelegramVenue.php               # Envio de local (venue)
|   |   +-- TelegramContact.php             # Envio de contato
|   |   +-- TelegramPoll.php                # Envio de enquete
|   |   +-- TelegramSticker.php             # Envio de sticker
|   |   +-- TelegramDice.php                # Envio de dado/emoji animado
|   +-- Traits/
|   |   +-- HasSharedParams.php             # Params compartilhados (chat_id, reply_markup, etc.)
|   +-- Telegram.php                        # Servico principal (resolve bots, envia)
|   +-- TelegramServiceProvider.php         # Service Provider (spatie/package-tools)
+-- tests/
|   +-- ArchTest.php                        # Testes de arquitetura (Pest)
|   +-- Pest.php                            # Configuracao Pest
|   +-- TestCase.php                        # TestCase base (Orchestra Testbench)
|   +-- Unit/
|   |   +-- Api/
|   |   |   +-- TelegramBotApiTest.php
|   |   +-- Enums/
|   |   |   +-- ParseModeTest.php
|   |   |   +-- ChatActionTest.php
|   |   +-- Keyboards/
|   |   |   +-- InlineKeyboardTest.php
|   |   |   +-- ReplyKeyboardTest.php
|   |   |   +-- ButtonTest.php
|   |   +-- Messages/
|   |   |   +-- TelegramMessageTest.php
|   |   |   +-- TelegramPhotoTest.php
|   |   |   +-- TelegramDocumentTest.php
|   |   |   +-- TelegramVideoTest.php
|   |   |   +-- TelegramLocationTest.php
|   |   |   +-- TelegramContactTest.php
|   |   |   +-- TelegramPollTest.php
|   |   +-- TelegramTest.php               # Testes do servico principal
|   +-- Feature/
|   |   +-- Channels/
|   |   |   +-- TelegramChannelTest.php
|   |   +-- Commands/
|   |   |   +-- TelegramSetWebhookCommandTest.php
|   |   |   +-- TelegramGetMeCommandTest.php
|   |   +-- Logging/
|   |   |   +-- TelegramHandlerTest.php
|   |   |   +-- CreateTelegramLoggerTest.php
|   |   +-- TelegramServiceProviderTest.php
+-- CHANGELOG.md
+-- LICENSE.md
+-- README.md
+-- Makefile
+-- composer.json
+-- docker-compose.yml
+-- phpstan-baseline.neon
+-- phpstan.neon.dist
+-- phpunit.xml.dist
```

---

## 5. Componentes Principais

### 5.1 `TelegramBotApi` -- Cliente HTTP

O nucleo do pacote. Encapsula todas as chamadas HTTP para a Telegram Bot API usando o facade `Http::` do Laravel.

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Api;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

class TelegramBotApi
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl = 'https://api.telegram.org',
        private readonly int $timeout = 10,
    ) {}

    /**
     * Faz uma chamada POST para a Telegram Bot API.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws TelegramApiException
     */
    public function call(string $method, array $params = []): array
    {
        $url = "{$this->baseUrl}/bot{$this->token}/{$method}";

        $response = Http::timeout($this->timeout)->post($url, $params);

        if (! $response->successful()) {
            throw TelegramApiException::fromResponse($response, $method);
        }

        return $response->json();
    }

    /**
     * Chamada silenciosa -- nunca lanca excecao (para logging).
     *
     * @param  array<string, mixed>  $params
     */
    public function callSilent(string $method, array $params = []): bool
    {
        try {
            $this->call($method, $params);
            return true;
        } catch (\Throwable $e) {
            error_log("[TelegramBotApi] {$method} failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Upload de arquivo via multipart/form-data.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function upload(string $method, array $params, string $fileField, string $filePath): array
    {
        $url = "{$this->baseUrl}/bot{$this->token}/{$method}";

        $response = Http::timeout($this->timeout)
            ->attach($fileField, file_get_contents($filePath), basename($filePath))
            ->post($url, $params);

        if (! $response->successful()) {
            throw TelegramApiException::fromResponse($response, $method);
        }

        return $response->json();
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
```

### 5.2 `Telegram` -- Servico Principal

Gerencia multiplos bots e fornece a API de alto nivel.

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications;

use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;

class Telegram
{
    /** @var array<string, TelegramBotApi> */
    private array $bots = [];

    public function __construct(
        /** @var array<string, array{token: string}> */
        private readonly array $botsConfig,
        private readonly string $defaultBot,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    /** Obter instancia da API para um bot especifico. */
    public function bot(?string $name = null): TelegramBotApi
    {
        $name ??= $this->defaultBot;

        if (! isset($this->bots[$name])) {
            $config = $this->botsConfig[$name]
                ?? throw new \InvalidArgumentException("Bot [{$name}] not configured.");

            $this->bots[$name] = new TelegramBotApi(
                token: $config['token'],
                baseUrl: $this->baseUrl,
                timeout: $this->timeout,
            );
        }

        return $this->bots[$name];
    }

    /** Enviar mensagem de texto (atalho). */
    public function sendMessage(
        string $chatId,
        string $text,
        ?string $parseMode = 'HTML',
        ?string $topicId = null,
    ): array {
        $params = array_filter([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'message_thread_id' => $topicId,
        ]);

        return $this->bot()->call('sendMessage', $params);
    }

    // ... metodos de alto nivel para cada tipo de mensagem
}
```

### 5.3 `TelegramMessage` -- Builder Fluente

A classe principal que os usuarios vao interagir para construir mensagens.

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramMessage implements TelegramMessageInterface
{
    use HasSharedParams;

    private string $content = '';
    private ParseMode $parseMode = ParseMode::HTML;
    private ?InlineKeyboard $keyboard = null;
    private bool $disableNotification = false;
    private bool $protectContent = false;
    private ?string $replyToMessageId = null;

    public static function create(string $content = ''): static
    {
        return (new static)->content($content);
    }

    public function content(string $text): static
    {
        $this->content = $text;
        return $this;
    }

    public function line(string $text): static
    {
        $this->content .= ($this->content !== '' ? "\n" : '') . $text;
        return $this;
    }

    public function bold(string $text): static
    {
        return $this->line("<b>{$text}</b>");
    }

    public function italic(string $text): static
    {
        return $this->line("<i>{$text}</i>");
    }

    public function code(string $text): static
    {
        return $this->line("<code>{$text}</code>");
    }

    public function pre(string $text, ?string $language = null): static
    {
        $lang = $language ? " class=\"language-{$language}\"" : '';
        return $this->line("<pre><code{$lang}>{$text}</code></pre>");
    }

    public function link(string $text, string $url): static
    {
        return $this->line("<a href=\"{$url}\">{$text}</a>");
    }

    public function spoiler(string $text): static
    {
        return $this->line("<tg-spoiler>{$text}</tg-spoiler>");
    }

    public function quote(string $text): static
    {
        return $this->line("<blockquote>{$text}</blockquote>");
    }

    public function button(string $text, string $url, int $columns = 2): static
    {
        $this->keyboard ??= InlineKeyboard::make();
        $this->keyboard->url($text, $url, $columns);
        return $this;
    }

    public function buttonWithCallback(string $text, string $callbackData, int $columns = 2): static
    {
        $this->keyboard ??= InlineKeyboard::make();
        $this->keyboard->callback($text, $callbackData, $columns);
        return $this;
    }

    public function keyboard(InlineKeyboard $keyboard): static
    {
        $this->keyboard = $keyboard;
        return $this;
    }

    public function parseMode(ParseMode $mode): static
    {
        $this->parseMode = $mode;
        return $this;
    }

    public function silent(): static
    {
        $this->disableNotification = true;
        return $this;
    }

    public function protected(): static
    {
        $this->protectContent = true;
        return $this;
    }

    public function replyTo(string $messageId): static
    {
        $this->replyToMessageId = $messageId;
        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'chat_id' => $this->chatId,
            'text' => $this->content,
            'parse_mode' => $this->parseMode->value,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'reply_parameters' => $this->replyToMessageId
                ? ['message_id' => $this->replyToMessageId]
                : null,
            'reply_markup' => $this->keyboard?->toArray(),
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendMessage';
    }
}
```

### 5.4 `TelegramChannel` -- Canal de Notificacao

Integra com o sistema de notificacoes do Laravel.

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Channels;

use Illuminate\Notifications\Notification;
use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Telegram;

class TelegramChannel
{
    public function __construct(
        private readonly Telegram $telegram,
    ) {}

    public function send(mixed $notifiable, Notification $notification): ?array
    {
        /** @var TelegramMessageInterface $message */
        $message = $notification->toTelegram($notifiable);

        $chatId = $message->getChatId()
            ?? $notifiable->routeNotificationFor('telegram', $notification);

        if (! $chatId) {
            return null;
        }

        $params = $message->toArray();
        $params['chat_id'] = $chatId;

        $bot = $message->getBot();

        return $this->telegram->bot($bot)->call($message->getApiMethod(), $params);
    }
}
```

### 5.5 `TelegramHandler` -- Monolog Handler

Para logging de erros diretamente no Telegram (evolucao do que foi criado no Basal).

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;

class TelegramHandler extends AbstractProcessingHandler
{
    private const LEVEL_EMOJIS = [
        'DEBUG'     => "\xF0\x9F\x94\x8D",  // Magnifying glass
        'INFO'      => "\xE2\x84\xB9\xEF\xB8\x8F",  // Info
        'NOTICE'    => "\xF0\x9F\x93\x8B",  // Clipboard
        'WARNING'   => "\xE2\x9A\xA0\xEF\xB8\x8F",  // Warning
        'ERROR'     => "\xF0\x9F\x94\xB4",  // Red circle
        'CRITICAL'  => "\xF0\x9F\x94\xA5",  // Fire
        'ALERT'     => "\xF0\x9F\x9A\xA8",  // Rotating light
        'EMERGENCY' => "\xE2\x9B\x94",      // No entry
    ];

    public function __construct(
        private readonly TelegramBotApi $api,
        private readonly string $chatId,
        private readonly ?string $topicId = null,
        int|string|Level $level = Level::Error,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $message = $this->formatMessage($record);
        $this->api->callSilent('sendMessage', array_filter([
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'message_thread_id' => $this->topicId,
        ]));
    }

    // ... formatacao (emoji, app name, environment, exception, stack trace)
}
```

### 5.6 `InlineKeyboard` -- Builder de Teclado

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Keyboards;

class InlineKeyboard
{
    /** @var array<int, array<int, Button>> */
    private array $rows = [];
    private int $currentRow = 0;

    public static function make(): static
    {
        return new static;
    }

    public function url(string $text, string $url, int $columns = 2): static
    {
        return $this->addButton(Button::url($text, $url), $columns);
    }

    public function callback(string $text, string $data, int $columns = 2): static
    {
        return $this->addButton(Button::callback($text, $data), $columns);
    }

    public function row(): static
    {
        $this->currentRow++;
        return $this;
    }

    /** @return array{inline_keyboard: array<int, array<int, array<string, string>>>} */
    public function toArray(): array
    {
        return [
            'inline_keyboard' => array_map(
                fn (array $row) => array_map(fn (Button $btn) => $btn->toArray(), $row),
                array_values($this->rows),
            ),
        ];
    }

    private function addButton(Button $button, int $columns): static
    {
        if (! isset($this->rows[$this->currentRow])) {
            $this->rows[$this->currentRow] = [];
        }

        $this->rows[$this->currentRow][] = $button;

        if (count($this->rows[$this->currentRow]) >= $columns) {
            $this->currentRow++;
        }

        return $this;
    }
}
```

### 5.7 Enums

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Enums;

enum ParseMode: string
{
    case HTML = 'HTML';
    case MarkdownV2 = 'MarkdownV2';
    case Markdown = 'Markdown';  // Legacy, nao recomendado
}

enum ChatAction: string
{
    case Typing = 'typing';
    case UploadPhoto = 'upload_photo';
    case RecordVideo = 'record_video';
    case UploadVideo = 'upload_video';
    case RecordVoice = 'record_voice';
    case UploadVoice = 'upload_voice';
    case UploadDocument = 'upload_document';
    case ChooseSticker = 'choose_sticker';
    case FindLocation = 'find_location';
    case RecordVideoNote = 'record_video_note';
    case UploadVideoNote = 'upload_video_note';
}
```

### 5.8 Trait `HasSharedParams`

Parametros compartilhados por todas as mensagens:

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Traits;

trait HasSharedParams
{
    private ?string $chatId = null;
    private ?string $topicId = null;
    private ?string $bot = null;

    public function to(string $chatId): static
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function topic(string $topicId): static
    {
        $this->topicId = $topicId;
        return $this;
    }

    public function bot(string $bot): static
    {
        $this->bot = $bot;
        return $this;
    }

    public function getChatId(): ?string { return $this->chatId; }
    public function getTopicId(): ?string { return $this->topicId; }
    public function getBot(): ?string { return $this->bot; }
}
```

### 5.9 `TelegramServiceProvider`

Usando `spatie/laravel-package-tools`:

```php
<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Channels\TelegramChannel;
use SamuelTerra22\TelegramNotifications\Commands\TelegramGetMeCommand;
use SamuelTerra22\TelegramNotifications\Commands\TelegramSetWebhookCommand;

class TelegramServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('telegram-notifications')
            ->hasConfigFile()
            ->hasCommands([
                TelegramSetWebhookCommand::class,
                TelegramGetMeCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Telegram::class, function ($app) {
            $config = $app['config']['telegram-notifications'];

            return new Telegram(
                botsConfig: $config['bots'] ?? [],
                defaultBot: $config['default'] ?? 'default',
                baseUrl: $config['api_base_url'] ?? 'https://api.telegram.org',
                timeout: $config['timeout'] ?? 10,
            );
        });

        $this->app->singleton(TelegramChannel::class, function ($app) {
            return new TelegramChannel($app->make(Telegram::class));
        });
    }
}
```

---

## 6. Configuracao

### `config/telegram-notifications.php`

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Bot
    |--------------------------------------------------------------------------
    |
    | The default bot to use when sending messages. Must match a key in the
    | 'bots' array below.
    |
    */

    'default' => env('TELEGRAM_BOT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Bots
    |--------------------------------------------------------------------------
    |
    | Configure one or more Telegram bots. Each bot needs at minimum a token.
    | The chat_id and topic_id are optional defaults for that bot.
    |
    */

    'bots' => [
        'default' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'topic_id' => env('TELEGRAM_TOPIC_ID'),
        ],
        // 'alerts' => [
        //     'token' => env('TELEGRAM_ALERTS_BOT_TOKEN'),
        //     'chat_id' => env('TELEGRAM_ALERTS_CHAT_ID'),
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Telegram Bot API. Override for testing or when
    | using a local Bot API server (https://core.telegram.org/bots/api#using-a-local-bot-api-server).
    |
    */

    'api_base_url' => env('TELEGRAM_API_BASE_URL', 'https://api.telegram.org'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for HTTP requests to the Telegram API.
    |
    */

    'timeout' => (int) env('TELEGRAM_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the Monolog Telegram handler. Enable this to send error
    | logs directly to a Telegram chat.
    |
    */

    'logging' => [
        'enabled' => (bool) env('TELEGRAM_LOG_ENABLED', false),
        'bot' => env('TELEGRAM_LOG_BOT', 'default'),
        'chat_id' => env('TELEGRAM_LOG_CHAT_ID'),
        'topic_id' => env('TELEGRAM_LOG_TOPIC_ID'),
    ],

];
```

### Integracao com `config/logging.php`

```php
// Adicionar ao array 'channels':
'telegram' => [
    'driver' => 'custom',
    'via' => \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger::class,
    'level' => env('LOG_TELEGRAM_LEVEL', 'error'),
],
```

### Variaveis de Ambiente (`.env`)

```env
# Bot principal
TELEGRAM_BOT_TOKEN=123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
TELEGRAM_CHAT_ID=-1001234567890
TELEGRAM_TOPIC_ID=42

# Logging
TELEGRAM_LOG_ENABLED=true
TELEGRAM_LOG_CHAT_ID=-1001234567890
TELEGRAM_LOG_TOPIC_ID=99
LOG_TELEGRAM_LEVEL=error
```

---

## 7. API Publica e Exemplos de Uso

### 7.1 Envio Direto via Facade

```php
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

// Mensagem simples
Telegram::sendMessage('-1001234567890', 'Hello from Laravel!');

// Mensagem com topico (forum)
Telegram::sendMessage('-1001234567890', 'Error report', topicId: '42');

// Usando bot especifico
Telegram::bot('alerts')->sendMessage('-1001234567890', 'ALERT!');
```

### 7.2 Laravel Notifications

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;

class AppointmentReminder extends Notification
{
    public function __construct(
        private readonly Appointment $appointment,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(mixed $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->bold('Lembrete de Consulta')
            ->line('')
            ->line("Paciente: {$this->appointment->patient->name}")
            ->line("Data: {$this->appointment->date->format('d/m/Y H:i')}")
            ->line("Servico: {$this->appointment->service->name}")
            ->line('')
            ->button('Ver Detalhes', route('appointments.show', $this->appointment))
            ->button('Confirmar', route('appointments.confirm', $this->appointment));
    }
}

// Uso:
$user->notify(new AppointmentReminder($appointment));

// On-demand (sem modelo):
Notification::route('telegram', '-1001234567890')
    ->notify(new AppointmentReminder($appointment));
```

### 7.3 Envio de Midia

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramPhoto;
use SamuelTerra22\TelegramNotifications\Messages\TelegramDocument;

// Foto
TelegramPhoto::create()
    ->to('-1001234567890')
    ->photo('https://example.com/chart.png')
    ->caption('Relatorio mensal de consultas')
    ->send();

// Documento
TelegramDocument::create()
    ->to('-1001234567890')
    ->document(storage_path('app/reports/monthly.pdf'))
    ->caption('Relatorio financeiro - Janeiro 2026')
    ->send();

// Em notificacao
public function toTelegram(mixed $notifiable): TelegramDocument
{
    return TelegramDocument::create()
        ->document($this->invoice->pdf_path)
        ->caption("Fatura #{$this->invoice->number}");
}
```

### 7.4 Teclados Interativos

```php
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;

$keyboard = InlineKeyboard::make()
    ->url('Abrir App', 'https://app.basal.app.br')
    ->url('Documentacao', 'https://docs.basal.app.br')
    ->row()
    ->callback('Confirmar', 'action:confirm:123')
    ->callback('Cancelar', 'action:cancel:123');

TelegramMessage::create()
    ->to('-1001234567890')
    ->content('Escolha uma opcao:')
    ->keyboard($keyboard)
    ->send();
```

### 7.5 Log Handler (Erros para Telegram)

```php
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->reportable(function (\Throwable $e) {
        try {
            if (config('telegram-notifications.logging.enabled')) {
                Log::channel('telegram')->error($e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        } catch (\Throwable) {
            // Nunca quebra a aplicacao
        }
    });
})
```

### 7.6 Localizacao, Contato e Enquete

```php
use SamuelTerra22\TelegramNotifications\Messages\TelegramLocation;
use SamuelTerra22\TelegramNotifications\Messages\TelegramContact;
use SamuelTerra22\TelegramNotifications\Messages\TelegramPoll;

// Localizacao
TelegramLocation::create()
    ->to($chatId)
    ->latitude(-23.5505)
    ->longitude(-46.6333)
    ->send();

// Contato
TelegramContact::create()
    ->to($chatId)
    ->phoneNumber('+5511999999999')
    ->firstName('Samuel')
    ->lastName('Terra')
    ->send();

// Enquete
TelegramPoll::create()
    ->to($chatId)
    ->question('Qual horario prefere?')
    ->options(['08:00', '10:00', '14:00', '16:00'])
    ->allowsMultipleAnswers()
    ->send();
```

### 7.7 Chat Actions

```php
use SamuelTerra22\TelegramNotifications\Enums\ChatAction;
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

// Mostrar "typing..." enquanto processa
Telegram::sendChatAction($chatId, ChatAction::Typing);

// Mostrar "uploading document..." antes de enviar arquivo
Telegram::sendChatAction($chatId, ChatAction::UploadDocument);
```

### 7.8 Edicao e Exclusao de Mensagens

```php
use SamuelTerra22\TelegramNotifications\Facades\Telegram;

// Editar mensagem
Telegram::editMessageText($chatId, $messageId, 'Texto atualizado');

// Deletar mensagem
Telegram::deleteMessage($chatId, $messageId);

// Deletar varias mensagens
Telegram::deleteMessages($chatId, [$msgId1, $msgId2, $msgId3]);
```

### 7.9 Webhook Management (Artisan)

```bash
# Configurar webhook
php artisan telegram:set-webhook --url=https://app.basal.app.br/telegram/webhook

# Verificar informacoes do bot
php artisan telegram:get-me

# Usar bot especifico
php artisan telegram:get-me --bot=alerts
```

---

## 8. Cobertura da Telegram Bot API

### v1.0 -- Core (30 metodos)

| Categoria | Metodos | Prioridade |
|-----------|---------|------------|
| **Mensagens** | `sendMessage`, `sendPhoto`, `sendDocument`, `sendVideo`, `sendAudio`, `sendVoice`, `sendAnimation`, `sendSticker`, `sendMediaGroup`, `sendLocation`, `sendVenue`, `sendContact`, `sendPoll`, `sendDice` | Alta |
| **Acoes** | `sendChatAction` | Alta |
| **Edicao** | `editMessageText`, `editMessageCaption`, `editMessageMedia`, `editMessageReplyMarkup` | Alta |
| **Exclusao** | `deleteMessage`, `deleteMessages` | Alta |
| **Forward/Copy** | `forwardMessage`, `copyMessage` | Media |
| **Bot Info** | `getMe` | Alta |
| **Webhook** | `setWebhook`, `deleteWebhook`, `getWebhookInfo` | Media |
| **Chat Info** | `getChat`, `getChatMember`, `getChatMemberCount` | Media |

### v1.1 -- Extended (15 metodos)

| Categoria | Metodos |
|-----------|---------|
| **Pin** | `pinChatMessage`, `unpinChatMessage`, `unpinAllChatMessages` |
| **Forum** | `createForumTopic`, `editForumTopic`, `closeForumTopic`, `reopenForumTopic`, `deleteForumTopic` |
| **Bot Commands** | `setMyCommands`, `deleteMyCommands`, `getMyCommands` |
| **Chat Admins** | `getChatAdministrators` |
| **File** | `getFile` |
| **Bot Description** | `setMyDescription`, `getMyDescription` |

### v2.0 -- Advanced (20 metodos)

| Categoria | Metodos |
|-----------|---------|
| **Invite Links** | `createChatInviteLink`, `editChatInviteLink`, `revokeChatInviteLink` |
| **Member Mgmt** | `banChatMember`, `unbanChatMember`, `restrictChatMember`, `promoteChatMember` |
| **Chat Config** | `setChatTitle`, `setChatDescription`, `setChatPhoto`, `deleteChatPhoto`, `setChatPermissions` |
| **Live Location** | `editMessageLiveLocation`, `stopMessageLiveLocation` |
| **Join Requests** | `approveChatJoinRequest`, `declineChatJoinRequest` |
| **Polls** | `stopPoll` |

### v3.0+ -- Specialized

Inline mode, Payments (Telegram Stars), Games, Sticker management, Telegram Passport, Business features.

---

## 9. Estrategia de Testes (TDD)

### Principios

1. **Red-Green-Refactor**: Escrever o teste primeiro, implementar, refatorar
2. **95%+ Coverage**: Meta minima de cobertura
3. **Http::fake()**: Todas as chamadas HTTP mockadas (zero chamadas reais)
4. **Orchestra Testbench**: Ambiente Laravel completo para testes de integracao
5. **Pest**: Framework de testes com sintaxe expressiva

### TestCase Base

```php
<?php

namespace SamuelTerra22\TelegramNotifications\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use SamuelTerra22\TelegramNotifications\TelegramServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'SamuelTerra22\\TelegramNotifications\\Database\\Factories\\'
                . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [TelegramServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('telegram-notifications.default', 'default');
        config()->set('telegram-notifications.bots.default.token', 'test-token-123');
        config()->set('telegram-notifications.bots.default.chat_id', '-1001234567890');
        config()->set('telegram-notifications.api_base_url', 'https://api.telegram.org');
        config()->set('telegram-notifications.timeout', 10);
    }
}
```

### Plano de Testes por Componente

#### Unit Tests (~80 testes)

| Componente | Testes | Qtd |
|------------|--------|-----|
| `TelegramBotApi` | `call()` success/error, `callSilent()` never throws, `upload()`, timeout, base URL | 10 |
| `TelegramMessage` | Builder methods (content, line, bold, italic, code, pre, link, spoiler, quote), toArray, parseMode, silent, protected, replyTo | 15 |
| `TelegramPhoto` | photo URL/file, caption, toArray | 5 |
| `TelegramDocument` | document path, filename, caption | 5 |
| `TelegramVideo` | video, duration, dimensions | 4 |
| `TelegramAudio` | audio, performer, title | 4 |
| `TelegramLocation` | latitude, longitude, livePeriod | 4 |
| `TelegramContact` | phoneNumber, firstName, lastName, vCard | 4 |
| `TelegramPoll` | question, options, type (quiz/regular), multipleAnswers | 6 |
| `InlineKeyboard` | url button, callback button, rows, columns, toArray | 8 |
| `ReplyKeyboard` | buttons, resize, oneTime, placeholder, toArray | 6 |
| `Button` | url, callback, webApp, toArray | 4 |
| `ParseMode` enum | values, cases | 2 |
| `ChatAction` enum | values, cases | 2 |
| `TelegramException` | fromResponse, message, code | 3 |
| `Telegram` (service) | bot(), sendMessage(), multi-bot | 5 |

#### Feature Tests (~50 testes)

| Componente | Testes | Qtd |
|------------|--------|-----|
| `TelegramServiceProvider` | config published, singleton registered, facade works, commands registered | 6 |
| `TelegramChannel` | send via notification, routeNotificationForTelegram, chatId from message vs notifiable, null chatId | 8 |
| `TelegramHandler` | formatMessage (all levels), emoji mapping, exception formatting, context truncation, HTML escaping, app name/env | 12 |
| `CreateTelegramLogger` | creates Logger, handler level, uses config | 4 |
| `TelegramSetWebhookCommand` | set URL, delete, invalid URL | 4 |
| `TelegramGetMeCommand` | outputs bot info, error handling | 3 |
| Integration: send all types | sendMessage, sendPhoto, sendDocument, sendLocation, sendContact, sendPoll | 8 |
| Integration: edit/delete | editMessageText, deleteMessage, deleteMessages | 3 |
| Integration: error resilience | API error, timeout, connection refused, rate limit (429) | 5 |

#### Architecture Tests

```php
arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('all classes have strict types')
    ->expect('SamuelTerra22\TelegramNotifications')
    ->toUseStrictTypes();

arch('contracts are interfaces')
    ->expect('SamuelTerra22\TelegramNotifications\Contracts')
    ->toBeInterfaces();

arch('enums are enums')
    ->expect('SamuelTerra22\TelegramNotifications\Enums')
    ->toBeEnums();

arch('exceptions extend base exception')
    ->expect('SamuelTerra22\TelegramNotifications\Exceptions')
    ->toExtend(\Exception::class);
```

### Executando os Testes

```bash
# Via Docker (ambiente local)
make test                   # Roda todos os testes
make test-coverage          # Testes com cobertura (HTML)
make test-filter FILTER=Bot # Filtra por nome

# Via Composer
composer test
composer test-coverage

# Diretamente
vendor/bin/pest
vendor/bin/pest --coverage --min=95
vendor/bin/pest --filter=TelegramBotApi
```

---

## 10. Ambiente de Desenvolvimento Local (Docker)

### `docker-compose.yml`

```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/Dockerfile
    volumes:
      - .:/app
      - composer-cache:/root/.composer/cache
    working_dir: /app
    environment:
      - COMPOSER_MEMORY_LIMIT=-1
    tty: true

volumes:
  composer-cache:
```

### `docker/Dockerfile`

```dockerfile
FROM php:8.4-cli-alpine

# Instalar dependencias do sistema
RUN apk add --no-cache \
    git \
    zip \
    unzip \
    curl-dev \
    libzip-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install zip pcntl \
    && pecl install pcov \
    && docker-php-ext-enable pcov

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar PHP para testes com cobertura
RUN echo "pcov.enabled=1" >> /usr/local/etc/php/conf.d/pcov.ini

WORKDIR /app
```

### `Makefile`

```makefile
.PHONY: help install update test test-coverage test-filter format analyse shell build clean

# Cores
GREEN  := \033[0;32m
YELLOW := \033[0;33m
RESET  := \033[0m

help: ## Mostra esta ajuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-20s$(RESET) %s\n", $$1, $$2}'

build: ## Build da imagem Docker
	docker compose build

install: ## Instala dependencias do Composer
	docker compose run --rm app composer install

update: ## Atualiza dependencias do Composer
	docker compose run --rm app composer update

test: ## Roda todos os testes
	docker compose run --rm app composer test

test-coverage: ## Roda testes com relatorio de cobertura
	docker compose run --rm app composer test-coverage

test-filter: ## Roda testes filtrados (ex: make test-filter FILTER=BotApi)
	docker compose run --rm app vendor/bin/pest --filter=$(FILTER)

format: ## Formata o codigo com Pint
	docker compose run --rm app composer format

analyse: ## Roda analise estatica com PHPStan
	docker compose run --rm app composer analyse

shell: ## Abre um shell no container
	docker compose run --rm app sh

clean: ## Remove containers e volumes
	docker compose down -v --remove-orphans
```

### Comandos de Uso

```bash
# Primeira vez: build + install
make build && make install

# Desenvolvimento diario
make test                          # Rodar testes
make test-filter FILTER=Message    # Filtrar por nome
make test-coverage                 # Ver cobertura
make format                        # Formatar codigo
make analyse                       # Analise estatica
make shell                         # Entrar no container

# Testar com versao especifica do Laravel
docker compose run --rm app composer require "laravel/framework:11.*" "orchestra/testbench:9.*" --no-interaction --no-update
docker compose run --rm app composer update
docker compose run --rm app composer test
```

---

## 11. Pipeline CI/CD (GitHub Actions)

### `.github/workflows/run-tests.yml`

```yaml
name: Tests

on:
  push:
    branches: [main]
    paths:
      - '**.php'
      - '.github/workflows/run-tests.yml'
      - 'phpunit.xml.dist'
      - 'composer.json'
      - 'composer.lock'
  pull_request:
    branches: [main]

concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true

jobs:
  test:
    runs-on: ubuntu-latest
    timeout-minutes: 10

    strategy:
      fail-fast: false
      matrix:
        php: ['8.1', '8.2', '8.3', '8.4']
        laravel: ['10.*', '11.*', '12.*']
        stability: [prefer-lowest, prefer-stable]
        include:
          - laravel: '10.*'
            testbench: '8.*'
          - laravel: '11.*'
            testbench: '9.*'
          - laravel: '12.*'
            testbench: '10.*'
        exclude:
          # Laravel 11/12 requerem PHP 8.2+
          - php: '8.1'
            laravel: '11.*'
          - php: '8.1'
            laravel: '12.*'

    name: PHP ${{ matrix.php }} - Laravel ${{ matrix.laravel }} - ${{ matrix.stability }}

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite
          coverage: pcov

      - name: Install dependencies
        run: |
          composer require "laravel/framework:${{ matrix.laravel }}" "orchestra/testbench:${{ matrix.testbench }}" --no-interaction --no-update
          composer update --${{ matrix.stability }} --prefer-dist --no-interaction

      - name: Execute tests
        run: vendor/bin/pest --ci

      - name: Check coverage
        if: matrix.php == '8.4' && matrix.laravel == '12.*' && matrix.stability == 'prefer-stable'
        run: vendor/bin/pest --coverage --min=95
```

### `.github/workflows/phpstan.yml`

```yaml
name: PHPStan

on:
  push:
    branches: [main]
    paths: ['**.php', 'phpstan.neon.dist']
  pull_request:
    branches: [main]

jobs:
  phpstan:
    runs-on: ubuntu-latest
    timeout-minutes: 5

    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          coverage: none
      - run: composer install --prefer-dist --no-interaction
      - run: ./vendor/bin/phpstan --error-format=github
```

### `.github/workflows/fix-php-code-style-issues.yml`

```yaml
name: Fix PHP Code Style

on:
  push:
    paths: ['**.php']

permissions:
  contents: write

jobs:
  php-code-styling:
    runs-on: ubuntu-latest
    timeout-minutes: 5

    steps:
      - uses: actions/checkout@v4
        with:
          ref: ${{ github.head_ref }}
      - uses: aglipanci/laravel-pint-action@2.6
      - uses: stefanzweifel/git-auto-commit-action@v5
        with:
          commit_message: 'style: fix code formatting'
```

### `.github/workflows/update-changelog.yml`

```yaml
name: Update Changelog

on:
  release:
    types: [released]

permissions:
  contents: write

jobs:
  update:
    runs-on: ubuntu-latest
    timeout-minutes: 5

    steps:
      - uses: actions/checkout@v4
        with:
          ref: main
      - uses: stefanzweifel/changelog-updater-action@v1
        with:
          latest-version: ${{ github.event.release.name }}
          release-notes: ${{ github.event.release.body }}
      - uses: stefanzweifel/git-auto-commit-action@v5
        with:
          branch: main
          commit_message: 'docs: update CHANGELOG'
          file_pattern: CHANGELOG.md
```

---

## 12. Publicacao no Packagist

### Passo a Passo

1. **Criar repositorio no GitHub**: `samuelterra22/laravel-telegram-notifications`
2. **Push do codigo**: `git push origin main`
3. **Registrar no Packagist**:
   - Acessar https://packagist.org/packages/submit
   - Informar a URL do repositorio: `https://github.com/samuelterra22/laravel-telegram-notifications`
   - Clicar em "Submit"
4. **Configurar auto-update**:
   - Packagist usa GitHub webhooks para detectar novos commits/tags
   - O webhook e configurado automaticamente ao submeter
5. **Criar primeira release**:
   - `git tag v1.0.0 && git push --tags`
   - Criar release no GitHub com notas de versao
6. **Verificar no Packagist**: `composer require samuelterra22/laravel-telegram-notifications`

### Versionamento (SemVer)

| Versao | Descricao |
|--------|-----------|
| `0.1.0` | Alpha -- funcionalidades core (sendMessage, notification channel) |
| `0.5.0` | Beta -- todas as mensagens, keyboards, logging |
| `1.0.0` | Stable -- v1.0 completa, 95%+ coverage, docs |
| `1.1.0` | Forum/topics avancado, bot commands, file download |
| `2.0.0` | Member management, invite links, advanced features |

### `composer.json` Extras (Auto-discovery)

```json
{
    "extra": {
        "laravel": {
            "providers": [
                "SamuelTerra22\\TelegramNotifications\\TelegramServiceProvider"
            ],
            "aliases": {
                "Telegram": "SamuelTerra22\\TelegramNotifications\\Facades\\Telegram"
            }
        }
    }
}
```

### `.gitattributes` (Excluir do download)

```gitattributes
/.github          export-ignore
/.gitattributes   export-ignore
/.gitignore       export-ignore
/.editorconfig    export-ignore
/docker           export-ignore
/tests            export-ignore
/Makefile         export-ignore
/docker-compose.yml export-ignore
/phpunit.xml.dist export-ignore
/phpstan.neon.dist export-ignore
/phpstan-baseline.neon export-ignore
```

---

## 13. Fases de Implementacao

### Fase 1: Fundacao (3-4 dias)

**Objetivo**: Skeleton do pacote + envio basico de mensagens.

| # | Tarefa | Arquivos |
|---|--------|----------|
| 1.1 | Criar repositorio + skeleton (baseado em spatie/package-skeleton-laravel) | Todos os arquivos base |
| 1.2 | Configurar Docker + Makefile | `docker-compose.yml`, `docker/Dockerfile`, `Makefile` |
| 1.3 | Configurar CI/CD (GitHub Actions) | `.github/workflows/*` |
| 1.4 | **TDD**: `TelegramBotApi` -- testes + implementacao | `src/Api/TelegramBotApi.php`, `tests/Unit/Api/TelegramBotApiTest.php` |
| 1.5 | **TDD**: `Telegram` service -- testes + implementacao | `src/Telegram.php`, `tests/Unit/TelegramTest.php` |
| 1.6 | **TDD**: `TelegramServiceProvider` -- testes + implementacao | `src/TelegramServiceProvider.php`, `tests/Feature/TelegramServiceProviderTest.php` |
| 1.7 | Config file + Facade | `config/telegram-notifications.php`, `src/Facades/Telegram.php` |
| 1.8 | Enums (`ParseMode`, `ChatAction`) | `src/Enums/*.php`, `tests/Unit/Enums/*` |
| 1.9 | Exceptions | `src/Exceptions/*.php` |

**Criterio de aceite**: `Telegram::sendMessage()` funciona com `Http::fake()`.

### Fase 2: Mensagens (3-4 dias)

**Objetivo**: Todos os tipos de mensagem com builder fluente.

| # | Tarefa | Arquivos |
|---|--------|----------|
| 2.1 | **TDD**: Trait `HasSharedParams` | `src/Traits/HasSharedParams.php` |
| 2.2 | **TDD**: Interface `TelegramMessageInterface` | `src/Contracts/TelegramMessageInterface.php` |
| 2.3 | **TDD**: `TelegramMessage` (texto) -- builder completo | `src/Messages/TelegramMessage.php`, `tests/Unit/Messages/TelegramMessageTest.php` |
| 2.4 | **TDD**: `TelegramPhoto` | `src/Messages/TelegramPhoto.php`, `tests/Unit/Messages/TelegramPhotoTest.php` |
| 2.5 | **TDD**: `TelegramDocument` | `src/Messages/TelegramDocument.php`, `tests/Unit/Messages/TelegramDocumentTest.php` |
| 2.6 | **TDD**: `TelegramVideo`, `TelegramAudio`, `TelegramVoice`, `TelegramAnimation` | `src/Messages/*.php`, `tests/Unit/Messages/*` |
| 2.7 | **TDD**: `TelegramLocation`, `TelegramVenue`, `TelegramContact` | `src/Messages/*.php`, `tests/Unit/Messages/*` |
| 2.8 | **TDD**: `TelegramPoll`, `TelegramSticker`, `TelegramDice` | `src/Messages/*.php`, `tests/Unit/Messages/*` |
| 2.9 | Message splitting automatico (>4096 chars) | Dentro de `TelegramMessage` |

**Criterio de aceite**: Todos os 14 tipos de mensagem funcionando com testes.

### Fase 3: Teclados e Interatividade (2 dias)

**Objetivo**: Teclados inline e reply com builder fluente.

| # | Tarefa | Arquivos |
|---|--------|----------|
| 3.1 | **TDD**: `Button` (URL, callback, webApp) | `src/Keyboards/Button.php`, `tests/Unit/Keyboards/ButtonTest.php` |
| 3.2 | **TDD**: `InlineKeyboard` builder | `src/Keyboards/InlineKeyboard.php`, `tests/Unit/Keyboards/InlineKeyboardTest.php` |
| 3.3 | **TDD**: `ReplyKeyboard` builder | `src/Keyboards/ReplyKeyboard.php`, `tests/Unit/Keyboards/ReplyKeyboardTest.php` |
| 3.4 | Integracao keyboard + mensagens | Testes de integracao |

**Criterio de aceite**: Teclados inline e reply funcionando em todos os tipos de mensagem.

### Fase 4: Integracoes Laravel (2-3 dias)

**Objetivo**: Notification channel + Monolog handler.

| # | Tarefa | Arquivos |
|---|--------|----------|
| 4.1 | **TDD**: `TelegramChannel` (Laravel Notifications) | `src/Channels/TelegramChannel.php`, `tests/Feature/Channels/TelegramChannelTest.php` |
| 4.2 | **TDD**: `routeNotificationForTelegram()` | Testes com modelo mock |
| 4.3 | **TDD**: `TelegramHandler` (Monolog) | `src/Logging/TelegramHandler.php`, `tests/Feature/Logging/TelegramHandlerTest.php` |
| 4.4 | **TDD**: `CreateTelegramLogger` | `src/Logging/CreateTelegramLogger.php`, `tests/Feature/Logging/CreateTelegramLoggerTest.php` |
| 4.5 | Documentacao de integracao com `config/logging.php` | README |

**Criterio de aceite**: Notificacoes e log handler funcionando end-to-end.

### Fase 5: Funcionalidades Avancadas (2 dias)

**Objetivo**: Edicao, exclusao, forward, webhook, artisan commands.

| # | Tarefa | Arquivos |
|---|--------|----------|
| 5.1 | **TDD**: `editMessageText`, `editMessageCaption`, `editMessageMedia` | Dentro de `Telegram.php` |
| 5.2 | **TDD**: `deleteMessage`, `deleteMessages` | Dentro de `Telegram.php` |
| 5.3 | **TDD**: `forwardMessage`, `copyMessage` | Dentro de `Telegram.php` |
| 5.4 | **TDD**: `TelegramSetWebhookCommand` | `src/Commands/TelegramSetWebhookCommand.php` |
| 5.5 | **TDD**: `TelegramGetMeCommand` | `src/Commands/TelegramGetMeCommand.php` |
| 5.6 | Rate limiting com retry-after (HTTP 429) | Dentro de `TelegramBotApi` |

**Criterio de aceite**: Todas as operacoes CRUD de mensagens + artisan commands.

### Fase 6: Documentacao e Publicacao (1-2 dias)

**Objetivo**: README completo, CHANGELOG, publicacao no Packagist.

| # | Tarefa |
|---|--------|
| 6.1 | README.md completo (instalacao, config, exemplos, API reference) |
| 6.2 | CHANGELOG.md |
| 6.3 | LICENSE.md (MIT) |
| 6.4 | PHPStan level 5 passando |
| 6.5 | Pint sem erros |
| 6.6 | Cobertura >= 95% |
| 6.7 | Criar repositorio GitHub |
| 6.8 | Push + tag v1.0.0 |
| 6.9 | Publicar no Packagist |
| 6.10 | Verificar instalacao em projeto limpo |

**Criterio de aceite**: Pacote instalavel via `composer require`, 95%+ coverage, docs completos.

### Timeline Estimada

```
Fase 1: Fundacao           ████████░░░░░░░░░░░░░░░░ (3-4 dias)
Fase 2: Mensagens          ░░░░░░░░████████░░░░░░░░ (3-4 dias)
Fase 3: Teclados           ░░░░░░░░░░░░░░░░████░░░░ (2 dias)
Fase 4: Laravel Integration░░░░░░░░░░░░░░░░░░░░███░ (2-3 dias)
Fase 5: Avancado           ░░░░░░░░░░░░░░░░░░░░░░██ (2 dias)
Fase 6: Docs + Publicacao  ░░░░░░░░░░░░░░░░░░░░░░░█ (1-2 dias)
                           ─────────────────────────
                           Total: 13-17 dias uteis
```

---

## 14. Migracao do Projeto Basal

Apos o pacote estar publicado, substituir a implementacao custom no Basal:

### Passo 1: Instalar o pacote

```bash
sail composer require samuelterra22/laravel-telegram-notifications
```

### Passo 2: Publicar config

```bash
sail artisan vendor:publish --tag=telegram-notifications-config
```

### Passo 3: Atualizar `.env`

```env
# Antes (custom)
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=xxx
TELEGRAM_CHAT_ID=-123
TELEGRAM_TOPIC_ID=42

# Depois (pacote)
TELEGRAM_BOT_TOKEN=xxx
TELEGRAM_CHAT_ID=-123
TELEGRAM_TOPIC_ID=42
TELEGRAM_LOG_ENABLED=true
TELEGRAM_LOG_CHAT_ID=-123
TELEGRAM_LOG_TOPIC_ID=42
```

### Passo 4: Atualizar `config/logging.php`

```php
// Antes
'telegram' => [
    'driver' => 'custom',
    'via' => App\Logging\CreateTelegramLogger::class,
    'level' => env('LOG_TELEGRAM_LEVEL', 'error'),
],

// Depois
'telegram' => [
    'driver' => 'custom',
    'via' => \SamuelTerra22\TelegramNotifications\Logging\CreateTelegramLogger::class,
    'level' => env('LOG_TELEGRAM_LEVEL', 'error'),
],
```

### Passo 5: Atualizar `bootstrap/app.php`

```php
// Antes
use App\Services\TelegramService;

// Depois
use SamuelTerra22\TelegramNotifications\Telegram;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->reportable(function (\Throwable $e) {
        try {
            if (config('telegram-notifications.logging.enabled')) {
                Log::channel('telegram')->error($e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        } catch (\Throwable) {}
    });
})
```

### Passo 6: Remover arquivos custom

```bash
rm app/Services/TelegramService.php
rm app/Logging/TelegramHandler.php
rm app/Logging/CreateTelegramLogger.php
rm config/telegram.php
```

### Passo 7: Atualizar `AppServiceProvider.php`

Remover o `TelegramService` do array `$singletons`.

### Passo 8: Atualizar testes

Substituir referencias a `App\Services\TelegramService` por classes do pacote.

---

## Apendice A: Limites e Restricoes da Telegram Bot API

| Recurso | Limite |
|---------|--------|
| Texto da mensagem | 1-4096 caracteres |
| Caption (midia) | 0-1024 caracteres |
| Callback data | 1-64 bytes |
| Pergunta da enquete | 1-300 caracteres |
| Opcoes da enquete | Ate 12 opcoes |
| Upload de foto | 10 MB max |
| Upload de arquivo (cloud) | 50 MB max |
| Upload de arquivo (local API) | 2 GB max |
| Download via getFile | 20 MB max |
| Rate limit (chat individual) | ~1 msg/segundo |
| Rate limit (grupo) | ~20 msgs/minuto |
| Rate limit (broadcast) | ~30 msgs/segundo |
| Resultados inline | Max 50 por resposta |
| deleteMessages | 1-100 por chamada |
| Webhook ports | 443, 80, 88, 8443 |
| Retencao de updates | 24 horas |

## Apendice B: Formatos de Mensagem Suportados

### HTML (Recomendado)

```html
<b>negrito</b>
<i>italico</i>
<u>sublinhado</u>
<s>tachado</s>
<tg-spoiler>spoiler</tg-spoiler>
<code>codigo inline</code>
<pre>bloco de codigo</pre>
<pre><code class="language-python">print("hello")</code></pre>
<a href="https://example.com">link</a>
<a href="tg://user?id=123456789">mencao</a>
<blockquote>citacao</blockquote>
<blockquote expandable>citacao expansivel</blockquote>
```

### MarkdownV2

```
*negrito*
_italico_
__sublinhado__
~tachado~
||spoiler||
`codigo inline`
```python
bloco de codigo
```
[link](https://example.com)
[mencao](tg://user?id=123456789)
>citacao
```

**Caracteres especiais que devem ser escapados no MarkdownV2**:
`_`, `*`, `[`, `]`, `(`, `)`, `~`, `` ` ``, `>`, `#`, `+`, `-`, `=`, `|`, `{`, `}`, `.`, `!`
