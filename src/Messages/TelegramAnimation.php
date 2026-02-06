<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramAnimation implements TelegramMessageInterface
{
    use HasSharedParams;

    private string $animation = '';

    private string $caption = '';

    private ?int $duration = null;

    private ?int $width = null;

    private ?int $height = null;

    private ?string $thumbnail = null;

    private ParseMode $parseMode = ParseMode::HTML;

    private ?InlineKeyboard $keyboard = null;

    private bool $disableNotification = false;

    private bool $protectContent = false;

    private bool $hasSpoiler = false;

    public static function create(): self
    {
        return new self;
    }

    public function animation(string $animation): static
    {
        $this->animation = $animation;

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

    public function width(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;

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

    public function spoiler(bool $spoiler = true): static
    {
        $this->hasSpoiler = $spoiler;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'chat_id' => $this->chatId,
            'animation' => $this->animation,
            'caption' => $this->caption !== '' ? $this->caption : null,
            'parse_mode' => $this->caption !== '' ? $this->parseMode->value : null,
            'duration' => $this->duration,
            'width' => $this->width,
            'height' => $this->height,
            'thumbnail' => $this->thumbnail,
            'has_spoiler' => $this->hasSpoiler ?: null,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'reply_markup' => $this->keyboard?->toArray(),
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendAnimation';
    }
}
