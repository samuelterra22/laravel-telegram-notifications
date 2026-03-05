<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramMediaGroup implements TelegramMessageInterface
{
    use HasSharedParams;

    /** @var array<int, array<string, mixed>> */
    private array $media = [];

    private bool $disableNotification = false;

    private bool $protectContent = false;

    public static function create(): self
    {
        return new self;
    }

    public function photo(string $media, ?string $caption = null, ?ParseMode $parseMode = null): static
    {
        return $this->addMedia('photo', $media, $caption, $parseMode);
    }

    public function video(string $media, ?string $caption = null, ?ParseMode $parseMode = null): static
    {
        return $this->addMedia('video', $media, $caption, $parseMode);
    }

    public function document(string $media, ?string $caption = null, ?ParseMode $parseMode = null): static
    {
        return $this->addMedia('document', $media, $caption, $parseMode);
    }

    public function audio(string $media, ?string $caption = null, ?ParseMode $parseMode = null): static
    {
        return $this->addMedia('audio', $media, $caption, $parseMode);
    }

    private function addMedia(string $type, string $media, ?string $caption, ?ParseMode $parseMode): static
    {
        $item = array_filter([
            'type' => $type,
            'media' => $media,
            'caption' => $caption,
            'parse_mode' => $caption !== null ? ($parseMode ?? ParseMode::HTML)->value : null,
        ]);
        $this->media[] = $item;

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
            'media' => $this->media,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'message_effect_id' => $this->messageEffectId,
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendMediaGroup';
    }
}
