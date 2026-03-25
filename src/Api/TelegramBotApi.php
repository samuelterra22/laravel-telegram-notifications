<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Api;

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Exceptions\TelegramApiException;

class TelegramBotApi
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl = 'https://api.telegram.org',
        private readonly int $timeout = 10,
        private readonly int $maxRetries = 3,
        private readonly int $baseDelayMs = 1000,
        private readonly bool $useJitter = true,
        private readonly ?string $username = null,
    ) {}

    /**
     * Make a POST call to the Telegram Bot API.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws TelegramApiException
     */
    public function call(string $method, array $params = []): array
    {
        $url = "{$this->baseUrl}/bot{$this->token}/{$method}";

        $response = Http::timeout($this->timeout)->post($url, $params);

        if (! $response->successful()) {
            $exception = TelegramApiException::fromResponse($response, $method);

            if ($exception->isRateLimited()) {
                return $this->retryWithBackoff($url, $params, $method, $exception);
            }

            throw $exception;
        }

        return $response->json();
    }

    /**
     * Retry a failed request with exponential backoff.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws TelegramApiException
     */
    private function retryWithBackoff(string $url, array $params, string $method, TelegramApiException $lastException): array
    {
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            $delayMs = $lastException->getRetryAfter() !== null
                ? $lastException->getRetryAfter() * 1000
                : $this->baseDelayMs * (2 ** ($attempt - 1));

            if ($this->useJitter && $delayMs > 0) {
                $delayMs += random_int(0, (int) ($delayMs * 0.5));
            }

            usleep($delayMs * 1000);

            $response = Http::timeout($this->timeout)->post($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            $lastException = TelegramApiException::fromResponse($response, $method);

            if (! $lastException->isRateLimited()) {
                throw $lastException;
            }
        }

        throw $lastException;
    }

    /**
     * Silent call -- never throws exceptions (for logging).
     *
     * @param  array<string, mixed>  $params
     */
    public function callSilent(string $method, array $params = []): bool
    {
        try {
            $this->call($method, $params);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Upload a file via multipart/form-data.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws TelegramApiException
     */
    public function upload(string $method, array $params, string $fileField, string $filePath): array
    {
        $url = "{$this->baseUrl}/bot{$this->token}/{$method}";

        $response = Http::timeout($this->timeout)
            ->attach($fileField, file_get_contents($filePath), basename($filePath))
            ->post($url, $params);

        if (! $response->successful()) {
            throw TelegramApiException::fromResponse($response, $method);
        }

        return $response->json();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    public function getBaseDelayMs(): int
    {
        return $this->baseDelayMs;
    }

    public function getUseJitter(): bool
    {
        return $this->useJitter;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
