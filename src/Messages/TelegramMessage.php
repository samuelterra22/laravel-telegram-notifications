<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboard;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramMessage implements TelegramMessageInterface
{
    use HasSharedParams;

    private string $content = '';

    private ParseMode $parseMode = ParseMode::HTML;

    private ?InlineKeyboard $inlineKeyboard = null;

    private ?ReplyKeyboard $replyKeyboard = null;

    private bool $disableNotification = false;

    private bool $protectContent = false;

    private ?string $replyToMessageId = null;

    private bool $disableWebPagePreview = false;

    public static function create(string $content = ''): self
    {
        $instance = new self;

        if ($content !== '') {
            $instance->content($content);
        }

        return $instance;
    }

    public function content(string $text): static
    {
        $this->content = $text;

        return $this;
    }

    public function line(string $text): static
    {
        $this->content .= ($this->content !== '' ? "\n" : '').$text;

        return $this;
    }

    public function bold(string $text): static
    {
        return $this->line("<b>{$text}</b>");
    }

    public function italic(string $text): static
    {
        return $this->line("<i>{$text}</i>");
    }

    public function underline(string $text): static
    {
        return $this->line("<u>{$text}</u>");
    }

    public function strikethrough(string $text): static
    {
        return $this->line("<s>{$text}</s>");
    }

    public function code(string $text): static
    {
        return $this->line("<code>{$text}</code>");
    }

    public function pre(string $text, ?string $language = null): static
    {
        $lang = $language ? " class=\"language-{$language}\"" : '';

        return $this->line("<pre><code{$lang}>{$text}</code></pre>");
    }

    public function link(string $text, string $url): static
    {
        return $this->line("<a href=\"{$url}\">{$text}</a>");
    }

    public function spoiler(string $text): static
    {
        return $this->line("<tg-spoiler>{$text}</tg-spoiler>");
    }

    public function quote(string $text): static
    {
        return $this->line("<blockquote>{$text}</blockquote>");
    }

    public function button(string $text, string $url, int $columns = 2): static
    {
        $this->inlineKeyboard ??= InlineKeyboard::make();
        $this->inlineKeyboard->url($text, $url, $columns);

        return $this;
    }

    public function buttonWithCallback(string $text, string $callbackData, int $columns = 2): static
    {
        $this->inlineKeyboard ??= InlineKeyboard::make();
        $this->inlineKeyboard->callback($text, $callbackData, $columns);

        return $this;
    }

    public function keyboard(InlineKeyboard $keyboard): static
    {
        $this->inlineKeyboard = $keyboard;

        return $this;
    }

    public function replyKeyboard(ReplyKeyboard $keyboard): static
    {
        $this->replyKeyboard = $keyboard;

        return $this;
    }

    public function parseMode(ParseMode $mode): static
    {
        $this->parseMode = $mode;

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

    public function replyTo(string $messageId): static
    {
        $this->replyToMessageId = $messageId;

        return $this;
    }

    public function disableWebPagePreview(bool $disable = true): static
    {
        $this->disableWebPagePreview = $disable;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Split the message content into chunks respecting the 4096 char limit.
     *
     * @return array<int, string>
     */
    public function splitContent(): array
    {
        if (mb_strlen($this->content) <= 4096) {
            return [$this->content];
        }

        $chunks = [];
        $remaining = $this->content;

        while (mb_strlen($remaining) > 0) {
            if (mb_strlen($remaining) <= 4096) {
                $chunks[] = $remaining;
                break;
            }

            $chunk = mb_substr($remaining, 0, 4096);
            $lastNewline = mb_strrpos($chunk, "\n");

            if ($lastNewline !== false && $lastNewline > 2048) {
                $chunk = mb_substr($remaining, 0, $lastNewline);
                $remaining = mb_substr($remaining, $lastNewline + 1);
            } else {
                $remaining = mb_substr($remaining, 4096);
            }

            $chunks[] = $chunk;
        }

        return $chunks;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $replyMarkup = null;

        if ($this->inlineKeyboard !== null) {
            $replyMarkup = $this->inlineKeyboard->toArray();
        } elseif ($this->replyKeyboard !== null) {
            $replyMarkup = $this->replyKeyboard->toArray();
        }

        return array_filter([
            'chat_id' => $this->chatId,
            'text' => $this->content,
            'parse_mode' => $this->parseMode->value,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'disable_web_page_preview' => $this->disableWebPagePreview ?: null,
            'reply_parameters' => $this->replyToMessageId
                ? ['message_id' => $this->replyToMessageId]
                : null,
            'reply_markup' => $replyMarkup,
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendMessage';
    }
}
