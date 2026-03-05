<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramChecklist implements TelegramMessageInterface
{
    use HasSharedParams;

    private string $title = '';

    /** @var array<int, array{text: string, checked: bool}> */
    private array $items = [];

    private bool $disableNotification = false;

    private bool $protectContent = false;

    public static function create(): self
    {
        return new self;
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function item(string $text, bool $checked = false): static
    {
        $this->items[] = ['text' => $text, 'checked' => $checked];

        return $this;
    }

    public function checkedItem(string $text): static
    {
        return $this->item($text, true);
    }

    public function uncheckedItem(string $text): static
    {
        return $this->item($text, false);
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

    public function effect(string $effectId): static
    {
        $this->messageEffectId = $effectId;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'chat_id' => $this->chatId,
            'title' => $this->title !== '' ? $this->title : null,
            'checklist' => ! empty($this->items) ? $this->items : null,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'message_effect_id' => $this->messageEffectId,
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendChecklist';
    }
}
