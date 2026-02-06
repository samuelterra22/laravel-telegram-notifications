<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Keyboards;

use SamuelTerra22\TelegramNotifications\Contracts\ReplyMarkupInterface;

class ReplyKeyboardRemove implements ReplyMarkupInterface
{
    private bool $isSelective = false;

    public static function make(): self
    {
        return new self;
    }

    public function selective(bool $selective = true): static
    {
        $this->isSelective = $selective;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'remove_keyboard' => true,
            'selective' => $this->isSelective ?: null,
        ]);
    }
}
