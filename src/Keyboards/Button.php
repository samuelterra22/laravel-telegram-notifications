<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Keyboards;

class Button
{
    private function __construct(
        private readonly string $text,
        private readonly ?string $url = null,
        private readonly ?string $callbackData = null,
        private readonly ?string $webAppUrl = null,
    ) {}

    public static function url(string $text, string $url): self
    {
        return new self(text: $text, url: $url);
    }

    public static function callback(string $text, string $callbackData): self
    {
        return new self(text: $text, callbackData: $callbackData);
    }

    public static function webApp(string $text, string $webAppUrl): self
    {
        return new self(text: $text, webAppUrl: $webAppUrl);
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
            'url' => $this->url,
            'callback_data' => $this->callbackData,
            'web_app' => $this->webAppUrl ? ['url' => $this->webAppUrl] : null,
        ]);
    }
}
