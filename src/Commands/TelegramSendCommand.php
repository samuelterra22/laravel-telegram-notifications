<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Commands;

use Illuminate\Console\Command;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;
use SamuelTerra22\TelegramNotifications\Telegram;

class TelegramSendCommand extends Command
{
    protected $signature = 'telegram:send
        {message : The message text to send}
        {--chat= : Target chat ID (overrides config default)}
        {--bot= : Bot name to use}
        {--topic= : Forum topic ID}
        {--silent : Disable notification sound}';

    protected $description = 'Send a Telegram message from the command line';

    public function handle(Telegram $telegram): int
    {
        try {
            $botName = $this->option('bot');
            $chatId = $this->option('chat')
                ?? config("telegram-notifications.bots.{$botName}.chat_id")
                ?? config('telegram-notifications.bots.'.config('telegram-notifications.default').'.chat_id');

            if (! $chatId) {
                $this->error('No chat ID provided. Use --chat or configure a default chat_id.');

                return self::FAILURE;
            }

            $options = array_filter([
                'message_thread_id' => $this->option('topic'),
                'disable_notification' => $this->option('silent') ?: null,
            ]);

            $params = array_merge(array_filter([
                'chat_id' => $chatId,
                'text' => $this->argument('message'),
                'parse_mode' => 'HTML',
            ]), $options);

            $result = $telegram->bot($botName)->call('sendMessage', $params);

            $messageId = $result['result']['message_id'] ?? 'unknown';
            $this->info("Message sent successfully (ID: {$messageId}).");

            return self::SUCCESS;
        } catch (TelegramApiException $e) {
            $this->error("Failed: {$e->getTelegramDescription()}");

            return self::FAILURE;
        }
    }
}
