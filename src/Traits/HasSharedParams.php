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

    public function getChatId(): ?string
    {
        return $this->chatId;
    }

    public function getTopicId(): ?string
    {
        return $this->topicId;
    }

    public function getBot(): ?string
    {
        return $this->bot;
    }
}
