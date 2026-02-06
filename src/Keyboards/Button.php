<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Keyboards;

class Button
{
    /**
     * @param  array<string, mixed>  $data
     */
    private function __construct(
        private readonly string $text,
        private readonly array $data = [],
    ) {}

    public static function url(string $text, string $url): self
    {
        return new self($text, ['url' => $url]);
    }

    public static function callback(string $text, string $callbackData): self
    {
        return new self($text, ['callback_data' => $callbackData]);
    }

    public static function webApp(string $text, string $webAppUrl): self
    {
        return new self($text, ['web_app' => ['url' => $webAppUrl]]);
    }

    /**
     * @param  array<string, mixed>|string  $loginUrl
     */
    public static function loginUrl(string $text, array|string $loginUrl): self
    {
        $url = is_string($loginUrl) ? ['url' => $loginUrl] : $loginUrl;

        return new self($text, ['login_url' => $url]);
    }

    public static function switchInlineQuery(string $text, string $query = ''): self
    {
        return new self($text, ['switch_inline_query' => $query]);
    }

    public static function switchInlineQueryCurrentChat(string $text, string $query = ''): self
    {
        return new self($text, ['switch_inline_query_current_chat' => $query]);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public static function switchInlineQueryChosenChat(string $text, array $options = []): self
    {
        return new self($text, ['switch_inline_query_chosen_chat' => $options]);
    }

    public static function copyText(string $text, string $textToCopy): self
    {
        return new self($text, ['copy_text' => ['text' => $textToCopy]]);
    }

    public static function pay(string $text): self
    {
        return new self($text, ['pay' => true]);
    }

    public function getText(): string
    {
        return $this->text;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            ...$this->data,
        ], fn ($value) => $value !== null);
    }
}
