<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Fluent;

use SamuelTerra22\TelegramNotifications\Contracts\ReplyMarkupInterface;
use SamuelTerra22\TelegramNotifications\Responses\TelegramResponse;
use SamuelTerra22\TelegramNotifications\Telegram;

class PendingMessage
{
    private string $text = '';

    private string $parseMode = 'HTML';

    private ?string $topicId = null;

    private bool $disableNotification = false;

    private bool $protectContent = false;

    private bool $disableWebPagePreview = false;

    private int|string|null $replyToMessageId = null;

    private ?ReplyMarkupInterface $replyMarkup = null;

    public function __construct(
        private readonly Telegram $telegram,
        private readonly string $chatId,
    ) {}

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

    public function disablePreview(): static
    {
        $this->disableWebPagePreview = true;

        return $this;
    }

    public function keyboard(ReplyMarkupInterface $markup): static
    {
        $this->replyMarkup = $markup;

        return $this;
    }

    public function replyTo(int|string $messageId): static
    {
        $this->replyToMessageId = $messageId;

        return $this;
    }

    public function topic(string $topicId): static
    {
        $this->topicId = $topicId;

        return $this;
    }

    /**
     * Send only when condition is true, otherwise send to fallback chat.
     */
    public function sendWhen(bool $condition): static
    {
        if (! $condition) {
            $this->text = '';
        }

        return $this;
    }

    public function send(): TelegramResponse
    {
        if ($this->text === '') {
            return new TelegramResponse(['ok' => true, 'result' => []]);
        }

        $options = array_filter([
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'disable_web_page_preview' => $this->disableWebPagePreview ?: null,
            'reply_to_message_id' => $this->replyToMessageId,
            'reply_markup' => $this->replyMarkup,
        ]);

        $result = $this->telegram->sendMessage(
            chatId: $this->chatId,
            text: $this->text,
            parseMode: $this->parseMode,
            topicId: $this->topicId,
            options: $options,
        );

        return new TelegramResponse($result);
    }
}
