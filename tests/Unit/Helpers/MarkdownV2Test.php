<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Helpers\MarkdownV2;

it('escapes all 18 special characters', function () {
    $input = '_*[]()~`>#+-=|{}.!';
    $expected = '\_\*\[\]\(\)\~\`\>\#\+\-\=\|\{\}\.\!';

    expect(MarkdownV2::escape($input))->toBe($expected);
});

it('handles empty string', function () {
    expect(MarkdownV2::escape(''))->toBe('');
});

it('handles string with no special chars', function () {
    expect(MarkdownV2::escape('Hello World'))->toBe('Hello World');
});

it('handles string with only special chars', function () {
    expect(MarkdownV2::escape('*_~'))->toBe('\*\_\~');
});

it('escapes special chars within normal text', function () {
    $input = 'Price: $10.00 (USD)';
    $expected = 'Price: $10\.00 \(USD\)';

    expect(MarkdownV2::escape($input))->toBe($expected);
});
