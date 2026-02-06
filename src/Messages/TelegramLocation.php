<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramLocation implements TelegramMessageInterface
{
    use HasSharedParams;

    private float $latitude = 0.0;

    private float $longitude = 0.0;

    private ?float $horizontalAccuracy = null;

    private ?int $livePeriod = null;

    private ?int $heading = null;

    private ?int $proximityAlertRadius = null;

    private ?InlineKeyboard $keyboard = null;

    private bool $disableNotification = false;

    private bool $protectContent = false;

    public static function create(): self
    {
        return new self;
    }

    public function latitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function longitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function coordinates(float $latitude, float $longitude): static
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        return $this;
    }

    public function horizontalAccuracy(float $accuracy): static
    {
        $this->horizontalAccuracy = $accuracy;

        return $this;
    }

    public function livePeriod(int $seconds): static
    {
        $this->livePeriod = $seconds;

        return $this;
    }

    public function heading(int $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function proximityAlertRadius(int $radius): static
    {
        $this->proximityAlertRadius = $radius;

        return $this;
    }

    public function keyboard(InlineKeyboard $keyboard): static
    {
        $this->keyboard = $keyboard;

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

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'chat_id' => $this->chatId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'horizontal_accuracy' => $this->horizontalAccuracy,
            'live_period' => $this->livePeriod,
            'heading' => $this->heading,
            'proximity_alert_radius' => $this->proximityAlertRadius,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'reply_markup' => $this->keyboard?->toArray(),
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendLocation';
    }
}
