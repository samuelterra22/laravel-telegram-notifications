<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Commands;

use Illuminate\Console\Command;
use SamuelTerra22\TelegramNotifications\Telegram;

class TelegramGetMeCommand extends Command
{
    protected $signature = 'telegram:get-me
        {--bot= : The bot name to use}';

    protected $description = 'Get information about the Telegram bot';

    public function handle(Telegram $telegram): int
    {
        try {
            $botName = $this->option('bot');
            $result = $telegram->bot($botName)->call('getMe');

            if (! ($result['ok'] ?? false)) {
                $this->error('Failed to get bot info: '.($result['description'] ?? 'Unknown error'));

                return self::FAILURE;
            }

            $botInfo = $result['result'];

            $this->info('Bot Information:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', (string) ($botInfo['id'] ?? 'N/A')],
                    ['Name', $botInfo['first_name'] ?? 'N/A'],
                    ['Username', isset($botInfo['username']) ? "@{$botInfo['username']}" : 'N/A'],
                    ['Is Bot', ($botInfo['is_bot'] ?? false) ? 'Yes' : 'No'],
                    ['Can Join Groups', ($botInfo['can_join_groups'] ?? false) ? 'Yes' : 'No'],
                    ['Can Read Messages', ($botInfo['can_read_all_group_messages'] ?? false) ? 'Yes' : 'No'],
                    ['Supports Inline', ($botInfo['supports_inline_queries'] ?? false) ? 'Yes' : 'No'],
                ],
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
