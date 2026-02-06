<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Contracts;

interface ReplyMarkupInterface
{
    /** @return array<string, mixed> */
    public function toArray(): array;
}
