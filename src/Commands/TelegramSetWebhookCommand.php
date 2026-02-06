<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Commands;

use Illuminate\Console\Command;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;
use SamuelTerra22\TelegramNotifications\Telegram;

class TelegramSetWebhookCommand extends Command
{
    protected $signature = 'telegram:set-webhook
        {--url= : The webhook URL}
        {--delete : Delete the current webhook}
        {--bot= : The bot name to use}
        {--secret= : Secret token for webhook verification}
        {--drop-pending : Drop pending updates when deleting webhook}';

    protected $description = 'Set or delete a Telegram bot webhook';

    public function handle(Telegram $telegram): int
    {
        try {
            $botName = $this->option('bot');
            $bot = $telegram->bot($botName);

            if ($this->option('delete')) {
                $dropPending = (bool) $this->option('drop-pending');
                $result = $bot->call('deleteWebhook', [
                    'drop_pending_updates' => $dropPending,
                ]);

                if ($result['ok'] ?? false) {
                    $this->info('Webhook deleted successfully.');

                    return self::SUCCESS;
                }

                $this->error('Failed to delete webhook: '.($result['description'] ?? 'Unknown error'));

                return self::FAILURE;
            }

            $url = $this->option('url');

            if (! $url) {
                $this->error('You must provide a --url or use --delete.');

                return self::FAILURE;
            }

            $params = array_filter([
                'url' => $url,
                'secret_token' => $this->option('secret'),
            ]);

            $result = $bot->call('setWebhook', $params);

            if ($result['ok'] ?? false) {
                $this->info("Webhook set to: {$url}");

                return self::SUCCESS;
            }

            $this->error('Failed to set webhook: '.($result['description'] ?? 'Unknown error'));

            return self::FAILURE;
        } catch (TelegramApiException $e) {
            $this->error("Error: {$e->getTelegramDescription()}");

            return self::FAILURE;
        }
    }
}
