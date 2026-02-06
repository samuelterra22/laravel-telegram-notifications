<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Keyboards;

class ReplyKeyboard
{
    /** @var array<int, array<int, array<string, mixed>>> */
    private array $rows = [];

    private int $currentRow = 0;

    private bool $resizeKeyboard = true;

    private bool $oneTimeKeyboard = false;

    private ?string $inputFieldPlaceholder = null;

    private bool $selective = false;

    private bool $isPersistent = false;

    public static function make(): self
    {
        return new self;
    }

    public function button(string $text, int $columns = 2): static
    {
        if (! isset($this->rows[$this->currentRow])) {
            $this->rows[$this->currentRow] = [];
        }

        $this->rows[$this->currentRow][] = ['text' => $text];

        if (count($this->rows[$this->currentRow]) >= $columns) {
            $this->currentRow++;
        }

        return $this;
    }

    public function requestContact(string $text, int $columns = 2): static
    {
        if (! isset($this->rows[$this->currentRow])) {
            $this->rows[$this->currentRow] = [];
        }

        $this->rows[$this->currentRow][] = ['text' => $text, 'request_contact' => true];

        if (count($this->rows[$this->currentRow]) >= $columns) {
            $this->currentRow++;
        }

        return $this;
    }

    public function requestLocation(string $text, int $columns = 2): static
    {
        if (! isset($this->rows[$this->currentRow])) {
            $this->rows[$this->currentRow] = [];
        }

        $this->rows[$this->currentRow][] = ['text' => $text, 'request_location' => true];

        if (count($this->rows[$this->currentRow]) >= $columns) {
            $this->currentRow++;
        }

        return $this;
    }

    public function row(): static
    {
        $this->currentRow++;

        return $this;
    }

    public function resize(bool $resize = true): static
    {
        $this->resizeKeyboard = $resize;

        return $this;
    }

    public function oneTime(bool $oneTime = true): static
    {
        $this->oneTimeKeyboard = $oneTime;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->inputFieldPlaceholder = $placeholder;

        return $this;
    }

    public function selective(bool $selective = true): static
    {
        $this->selective = $selective;

        return $this;
    }

    public function persistent(bool $persistent = true): static
    {
        $this->isPersistent = $persistent;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'keyboard' => array_values($this->rows),
            'resize_keyboard' => $this->resizeKeyboard ?: null,
            'one_time_keyboard' => $this->oneTimeKeyboard ?: null,
            'input_field_placeholder' => $this->inputFieldPlaceholder,
            'selective' => $this->selective ?: null,
            'is_persistent' => $this->isPersistent ?: null,
        ]);
    }

    public function isEmpty(): bool
    {
        return empty($this->rows);
    }
}
