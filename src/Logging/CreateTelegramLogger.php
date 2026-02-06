<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Logging;

use Monolog\Level;
use Monolog\Logger;
use SamuelTerra22\TelegramNotifications\Telegram;

class CreateTelegramLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __invoke(array $config): Logger
    {
        /** @var Telegram $telegram */
        $telegram = app(Telegram::class);

        $loggingConfig = config('telegram-notifications.logging');

        $botName = $loggingConfig['bot'] ?? 'default';
        $chatId = $config['chat_id'] ?? $loggingConfig['chat_id'] ?? '';
        $topicId = $config['topic_id'] ?? $loggingConfig['topic_id'] ?? null;
        $level = $config['level'] ?? 'error';

        $handler = new TelegramHandler(
            api: $telegram->bot($botName),
            chatId: (string) $chatId,
            topicId: $topicId ? (string) $topicId : null,
            level: Level::fromName($level),
        );

        return new Logger('telegram', [$handler]);
    }
}
