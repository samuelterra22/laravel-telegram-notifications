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

            if ($exception->isRateLimited() && $exception->getRetryAfter() !== null) {
                sleep($exception->getRetryAfter());
                $response = Http::timeout($this->timeout)->post($url, $params);

                if (! $response->successful()) {
                    throw TelegramApiException::fromResponse($response, $method);
                }

                return $response->json();
            }

            throw $exception;
        }

        return $response->json();
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
}
