<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SamuelTerra22\TelegramNotifications\Telegram;

class SendTelegramBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $chatIds
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public readonly string $text,
        public readonly array $chatIds,
        public readonly string $parseMode = 'HTML',
        public readonly array $options = [],
        public readonly int $rateLimitMs = 50,
    ) {}

    public function handle(Telegram $telegram): void
    {
        foreach ($this->chatIds as $index => $chatId) {
            try {
                $telegram->sendMessage(
                    chatId: $chatId,
                    text: $this->text,
                    parseMode: $this->parseMode,
                    options: $this->options,
                );
            } catch (\Throwable) {
                // Individual failures don't stop the broadcast
            }

            if ($this->rateLimitMs > 0 && $index < count($this->chatIds) - 1) {
                usleep($this->rateLimitMs * 1000);
            }
        }
    }
}
