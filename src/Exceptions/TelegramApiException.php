<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Exceptions;

use Illuminate\Http\Client\Response;

class TelegramApiException extends TelegramException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly string $apiMethod,
        private readonly ?string $telegramDescription = null,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(Response $response, string $method): self
    {
        $body = $response->json();
        $description = $body['description'] ?? 'Unknown error';
        $statusCode = $response->status();
        $retryAfter = null;

        if ($statusCode === 429) {
            $retryAfter = $body['parameters']['retry_after'] ?? null;
        }

        return new self(
            message: "Telegram API error [{$method}]: {$description}",
            statusCode: $statusCode,
            apiMethod: $method,
            telegramDescription: $description,
            retryAfter: $retryAfter,
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getApiMethod(): string
    {
        return $this->apiMethod;
    }

    public function getTelegramDescription(): ?string
    {
        return $this->telegramDescription;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    public function isRateLimited(): bool
    {
        return $this->statusCode === 429;
    }
}
