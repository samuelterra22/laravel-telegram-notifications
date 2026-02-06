<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Contracts;

interface TelegramMessageInterface
{
    /** @return array<string, mixed> */
    public function toArray(): array;

    public function getApiMethod(): string;

    public function getChatId(): ?string;

    public function getTopicId(): ?string;

    public function getBot(): ?string;
}
