<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Keyboards;

class InlineKeyboard
{
    /** @var array<int, array<int, Button>> */
    private array $rows = [];

    private int $currentRow = 0;

    public static function make(): self
    {
        return new self;
    }

    public function url(string $text, string $url, int $columns = 2): static
    {
        return $this->addButton(Button::url($text, $url), $columns);
    }

    public function callback(string $text, string $data, int $columns = 2): static
    {
        return $this->addButton(Button::callback($text, $data), $columns);
    }

    public function webApp(string $text, string $webAppUrl, int $columns = 2): static
    {
        return $this->addButton(Button::webApp($text, $webAppUrl), $columns);
    }

    public function button(Button $button, int $columns = 2): static
    {
        return $this->addButton($button, $columns);
    }

    public function row(): static
    {
        $this->currentRow++;

        return $this;
    }

    /** @return array{inline_keyboard: array<int, array<int, array<string, mixed>>>} */
    public function toArray(): array
    {
        return [
            'inline_keyboard' => array_map(
                fn (array $row) => array_map(fn (Button $btn) => $btn->toArray(), $row),
                array_values($this->rows),
            ),
        ];
    }

    public function isEmpty(): bool
    {
        return empty($this->rows);
    }

    private function addButton(Button $button, int $columns): static
    {
        if (! isset($this->rows[$this->currentRow])) {
            $this->rows[$this->currentRow] = [];
        }

        $this->rows[$this->currentRow][] = $button;

        if (count($this->rows[$this->currentRow]) >= $columns) {
            $this->currentRow++;
        }

        return $this;
    }
}
