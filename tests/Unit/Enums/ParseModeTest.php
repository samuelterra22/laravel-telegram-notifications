<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Enums\ParseMode;

it('has HTML case', function () {
    expect(ParseMode::HTML->value)->toBe('HTML');
});

it('has MarkdownV2 case', function () {
    expect(ParseMode::MarkdownV2->value)->toBe('MarkdownV2');
});

it('has legacy Markdown case', function () {
    expect(ParseMode::Markdown->value)->toBe('Markdown');
});

it('has exactly 3 cases', function () {
    expect(ParseMode::cases())->toHaveCount(3);
});

it('can be created from value', function () {
    expect(ParseMode::from('HTML'))->toBe(ParseMode::HTML)
        ->and(ParseMode::from('MarkdownV2'))->toBe(ParseMode::MarkdownV2);
});
