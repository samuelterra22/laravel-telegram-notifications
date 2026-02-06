<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Responses;

use Carbon\Carbon;

class TelegramResponse
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private readonly array $data,
    ) {}

    public function ok(): bool
    {
        return (bool) ($this->data['ok'] ?? false);
    }

    public function messageId(): ?int
    {
        return $this->result()['message_id'] ?? null;
    }

    public function date(): ?Carbon
    {
        $timestamp = $this->result()['date'] ?? null;

        return $timestamp !== null ? Carbon::createFromTimestamp($timestamp) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function chat(): ?array
    {
        return $this->result()['chat'] ?? null;
    }

    public function text(): ?string
    {
        return $this->result()['text'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function result(): array
    {
        $result = $this->data['result'] ?? [];

        return is_array($result) ? $result : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
