<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramDice implements TelegramMessageInterface
{
    use HasSharedParams;

    private string $emoji = "\xF0\x9F\x8E\xB2";

    private ?InlineKeyboard $keyboard = null;

    private bool $disableNotification = false;

    private bool $protectContent = false;

    public static function create(): self
    {
        return new self;
    }

    public function dice(): static
    {
        $this->emoji = "\xF0\x9F\x8E\xB2";

        return $this;
    }

    public function darts(): static
    {
        $this->emoji = "\xF0\x9F\x8E\xAF";

        return $this;
    }

    public function basketball(): static
    {
        $this->emoji = "\xF0\x9F\x8F\x80";

        return $this;
    }

    public function football(): static
    {
        $this->emoji = "\xE2\x9A\xBD";

        return $this;
    }

    public function bowling(): static
    {
        $this->emoji = "\xF0\x9F\x8E\xB3";

        return $this;
    }

    public function slotMachine(): static
    {
        $this->emoji = "\xF0\x9F\x8E\xB0";

        return $this;
    }

    public function emoji(string $emoji): static
    {
        $this->emoji = $emoji;

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
            'emoji' => $this->emoji,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'reply_markup' => $this->keyboard?->toArray(),
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendDice';
    }
}
