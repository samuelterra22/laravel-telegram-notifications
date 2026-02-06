<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Channels;

use Illuminate\Notifications\Notification;
use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;
use SamuelTerra22\TelegramNotifications\Telegram;

class TelegramChannel
{
    public function __construct(
        private readonly Telegram $telegram,
    ) {}

    /**
     * Send the given notification.
     *
     * @return array<string, mixed>|null
     */
    public function send(mixed $notifiable, Notification $notification): ?array
    {
        /** @var TelegramMessageInterface $message */
        $message = $notification->toTelegram($notifiable); // @phpstan-ignore method.notFound

        $chatId = $message->getChatId()
            ?? $notifiable->routeNotificationFor('telegram', $notification);

        if (! $chatId) {
            return null;
        }

        $params = $message->toArray();
        $params['chat_id'] = $chatId;

        $bot = $message->getBot();
        $apiMethod = $message->getApiMethod();

        if ($message instanceof TelegramMessage) {
            $chunks = $message->splitContent();

            if (count($chunks) > 1) {
                $lastResult = null;

                foreach ($chunks as $index => $chunk) {
                    $chunkParams = $params;
                    $chunkParams['text'] = $chunk;

                    if ($index > 0) {
                        unset($chunkParams['reply_markup']);
                    }

                    $lastResult = $this->telegram->bot($bot)->call($apiMethod, $chunkParams);
                }

                return $lastResult;
            }
        }

        return $this->telegram->bot($bot)->call($apiMethod, $params);
    }
}
