<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Keyboards;

use SamuelTerra22\TelegramNotifications\Contracts\ReplyMarkupInterface;

class ForceReply implements ReplyMarkupInterface
{
    private ?string $inputFieldPlaceholder = null;

    private bool $isSelective = false;

    public static function make(): self
    {
        return new self;
    }

    public function placeholder(string $placeholder): static
    {
        $this->inputFieldPlaceholder = $placeholder;

        return $this;
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
            'force_reply' => true,
            'input_field_placeholder' => $this->inputFieldPlaceholder,
            'selective' => $this->isSelective ?: null,
        ]);
    }
}
