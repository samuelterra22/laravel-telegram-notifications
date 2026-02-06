<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramAudio implements TelegramMessageInterface
{
    use HasSharedParams;

    private string $audio = '';

    private string $caption = '';

    private ?int $duration = null;

    private ?string $performer = null;

    private ?string $title = null;

    private ?string $thumbnail = null;

    private ParseMode $parseMode = ParseMode::HTML;

    private ?InlineKeyboard $keyboard = null;

    private bool $disableNotification = false;

    private bool $protectContent = false;

    public static function create(): self
    {
        return new self;
    }

    public function audio(string $audio): static
    {
        $this->audio = $audio;

        return $this;
    }

    public function caption(string $caption): static
    {
        $this->caption = $caption;

        return $this;
    }

    public function duration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function performer(string $performer): static
    {
        $this->performer = $performer;

        return $this;
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function thumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function parseMode(ParseMode $mode): static
    {
        $this->parseMode = $mode;

        return $this;
    }

    public function keyboard(InlineKeyboard $keyboard): static
    {
        $this->keyboard = $keyboard;

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

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'chat_id' => $this->chatId,
            'audio' => $this->audio,
            'caption' => $this->caption !== '' ? $this->caption : null,
            'parse_mode' => $this->caption !== '' ? $this->parseMode->value : null,
            'duration' => $this->duration,
            'performer' => $this->performer,
            'title' => $this->title,
            'thumbnail' => $this->thumbnail,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'reply_markup' => $this->keyboard?->toArray(),
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendAudio';
    }
}
