<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Helpers;

class MarkdownV2
{
    private const SPECIAL_CHARS = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];

    public static function escape(string $text): string
    {
        return str_replace(
            self::SPECIAL_CHARS,
            array_map(fn (string $char) => "\\{$char}", self::SPECIAL_CHARS),
            $text,
        );
    }
}
