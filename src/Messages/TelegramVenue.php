<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramVenue implements TelegramMessageInterface
{
    use HasSharedParams;

    private float $latitude = 0.0;

    private float $longitude = 0.0;

    private string $title = '';

    private string $address = '';

    private ?string $foursquareId = null;

    private ?string $foursquareType = null;

    private ?string $googlePlaceId = null;

    private ?string $googlePlaceType = null;

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

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function address(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function foursquareId(string $id): static
    {
        $this->foursquareId = $id;

        return $this;
    }

    public function foursquareType(string $type): static
    {
        $this->foursquareType = $type;

        return $this;
    }

    public function googlePlaceId(string $id): static
    {
        $this->googlePlaceId = $id;

        return $this;
    }

    public function googlePlaceType(string $type): static
    {
        $this->googlePlaceType = $type;

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
            'title' => $this->title,
            'address' => $this->address,
            'foursquare_id' => $this->foursquareId,
            'foursquare_type' => $this->foursquareType,
            'google_place_id' => $this->googlePlaceId,
            'google_place_type' => $this->googlePlaceType,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
            'reply_markup' => $this->keyboard?->toArray(),
        ]);
    }

    public function getApiMethod(): string
    {
        return 'sendVenue';
    }
}
