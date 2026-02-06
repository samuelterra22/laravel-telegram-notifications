<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Fluent;

use SamuelTerra22\TelegramNotifications\Contracts\ReplyMarkupInterface;
use SamuelTerra22\TelegramNotifications\Responses\TelegramResponse;
use SamuelTerra22\TelegramNotifications\Telegram;

class PendingBroadcast
{
    private string $text = '';

    private string $parseMode = 'HTML';

    private bool $disableNotification = false;

    private bool $protectContent = false;

    private ?ReplyMarkupInterface $replyMarkup = null;

    private int $rateLimitMs = 0;

    /** @var callable|null */
    private $onFailureCallback = null;

    /**
     * @param  array<int, string>  $chatIds
     */
    public function __construct(
        private readonly Telegram $telegram,
        private array $chatIds = [],
    ) {}

    public function to(string ...$chatIds): static
    {
        $this->chatIds = array_merge($this->chatIds, $chatIds);

        return $this;
    }

    public function text(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function html(string $text): static
    {
        $this->text = $text;
        $this->parseMode = 'HTML';

        return $this;
    }

    public function markdown(string $text): static
    {
        $this->text = $text;
        $this->parseMode = 'MarkdownV2';

        return $this;
    }

    public function silent(): static
    {
        $this->disableNotification = true;

        return $this;
    }

    public function protected(): static
    {
        $this->protectContent = true;

        return $this;
    }

    public function keyboard(ReplyMarkupInterface $markup): static
    {
        $this->replyMarkup = $markup;

        return $this;
    }

    public function rateLimit(int $milliseconds): static
    {
        $this->rateLimitMs = $milliseconds;

        return $this;
    }

    public function onFailure(callable $callback): static
    {
        $this->onFailureCallback = $callback;

        return $this;
    }

    /**
     * Send the message to all chat IDs.
     *
     * @return array<int, TelegramResponse>
     */
    public function send(): array
    {
        $responses = [];
        $options = array_filter([
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'reply_markup' => $this->replyMarkup,
        ]);

        foreach ($this->chatIds as $index => $chatId) {
            try {
                $result = $this->telegram->sendMessage(
                    chatId: $chatId,
                    text: $this->text,
                    parseMode: $this->parseMode,
                    options: $options,
                );
                $responses[] = new TelegramResponse($result);
            } catch (\Throwable $e) {
                if ($this->onFailureCallback !== null) {
                    ($this->onFailureCallback)($chatId, $e);
                }
                $responses[] = new TelegramResponse(['ok' => false, 'result' => []]);
            }

            if ($this->rateLimitMs > 0 && $index < count($this->chatIds) - 1) {
                usleep($this->rateLimitMs * 1000);
            }
        }

        return $responses;
    }
}
