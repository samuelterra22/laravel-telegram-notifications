<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Enums;

enum ParseMode: string
{
    case HTML = 'HTML';
    case MarkdownV2 = 'MarkdownV2';
    case Markdown = 'Markdown';
}
