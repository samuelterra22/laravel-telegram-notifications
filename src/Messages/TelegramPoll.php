<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Messages;

use SamuelTerra22\TelegramNotifications\Contracts\TelegramMessageInterface;
use SamuelTerra22\TelegramNotifications\Traits\HasSharedParams;

class TelegramPoll implements TelegramMessageInterface
{
    use HasSharedParams;

    private string $question = '';

    /** @var array<int, string> */
    private array $options = [];

    private bool $isAnonymous = true;

    private string $type = 'regular';

    private bool $allowsMultipleAnswers = false;

    private ?int $correctOptionId = null;

    private ?string $explanation = null;

    private ?int $openPeriod = null;

    private ?int $closeDate = null;

    private bool $isClosed = false;

    private bool $disableNotification = false;

    private bool $protectContent = false;

    public static function create(): self
    {
        return new self;
    }

    public function question(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @param  array<int, string>  $options
     */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function addOption(string $option): static
    {
        $this->options[] = $option;

        return $this;
    }

    public function anonymous(bool $anonymous = true): static
    {
        $this->isAnonymous = $anonymous;

        return $this;
    }

    public function quiz(int $correctOptionId, ?string $explanation = null): static
    {
        $this->type = 'quiz';
        $this->correctOptionId = $correctOptionId;
        $this->explanation = $explanation;

        return $this;
    }

    public function allowsMultipleAnswers(bool $allows = true): static
    {
        $this->allowsMultipleAnswers = $allows;

        return $this;
    }

    public function openPeriod(int $seconds): static
    {
        $this->openPeriod = $seconds;

        return $this;
    }

    public function closeDate(int $timestamp): static
    {
        $this->closeDate = $timestamp;

        return $this;
    }

    public function closed(bool $closed = true): static
    {
        $this->isClosed = $closed;

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
        $formattedOptions = array_map(
            fn (string $option) => ['text' => $option],
            $this->options,
        );

        return array_filter([
            'chat_id' => $this->chatId,
            'question' => $this->question,
            'options' => $formattedOptions,
            'is_anonymous' => $this->isAnonymous ? null : false,
            'type' => $this->type !== 'regular' ? $this->type : null,
            'allows_multiple_answers' => $this->allowsMultipleAnswers ?: null,
            'correct_option_id' => $this->correctOptionId,
            'explanation' => $this->explanation,
            'open_period' => $this->openPeriod,
            'close_date' => $this->closeDate,
            'is_closed' => $this->isClosed ?: null,
            'message_thread_id' => $this->topicId,
            'disable_notification' => $this->disableNotification ?: null,
            'protect_content' => $this->protectContent ?: null,
        ], fn ($value) => $value !== null);
    }

    public function getApiMethod(): string
    {
        return 'sendPoll';
    }
}
