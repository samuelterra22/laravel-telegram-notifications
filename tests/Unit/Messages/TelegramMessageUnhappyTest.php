<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;

it('line() on empty message does not prepend a newline', function () {
    $message = TelegramMessage::create()->line('First line');

    expect($message->getContent())->toBe('First line');
});

it('line() on non-empty message prepends a newline', function () {
    $message = TelegramMessage::create('Hello')->line('World');

    expect($message->getContent())->toBe("Hello\nWorld");
});

it('splitContent does not split when newline is at exactly position 2048', function () {
    // 2048 chars + newline + remaining chars up to exactly 4096
    $before = str_repeat('A', 2048);
    $after = str_repeat('B', 4096 - 2048 - 1); // 2047 chars
    $content = $before."\n".$after;

    expect(mb_strlen($content))->toBe(4096);

    $message = TelegramMessage::create($content);
    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toBe($content);
});

it('splitContent splits at newline beyond position 2048', function () {
    // Build content > 4096 with newline at position 2049
    $before = str_repeat('A', 2049);
    $after = str_repeat('B', 3000);
    $content = $before."\n".$after;

    expect(mb_strlen($content))->toBeGreaterThan(4096);

    $message = TelegramMessage::create($content);
    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0])->toBe($before);
});

it('splitContent produces 3 chunks for 12288 chars', function () {
    $content = str_repeat('A', 12288);

    $message = TelegramMessage::create($content);
    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(3);

    foreach ($chunks as $chunk) {
        expect(mb_strlen($chunk))->toBeLessThanOrEqual(4096);
    }
});

it('splitContent handles multibyte Unicode characters correctly', function () {
    // Each emoji is 1 mb_strlen character
    $content = str_repeat("\xF0\x9F\x98\x80", 5000); // 5000 emoji characters

    expect(mb_strlen($content))->toBe(5000);

    $message = TelegramMessage::create($content);
    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(2);

    foreach ($chunks as $chunk) {
        expect(mb_strlen($chunk))->toBeLessThanOrEqual(4096);
    }
});

it('toArray uses inline keyboard when both inline and reply keyboards are set', function () {
    $inlineKeyboard = InlineKeyboard::make()->url('Click', 'https://example.com');
    $replyKeyboard = ReplyKeyboard::make()->button('Option 1');

    $message = TelegramMessage::create('Test')
        ->to('123')
        ->keyboard($inlineKeyboard)
        ->replyKeyboard($replyKeyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toHaveKey('inline_keyboard')
        ->and($array['reply_markup'])->not->toHaveKey('keyboard');
});

it('toArray uses reply keyboard when only reply keyboard is set', function () {
    $replyKeyboard = ReplyKeyboard::make()->button('Option 1');

    $message = TelegramMessage::create('Test')
        ->to('123')
        ->replyKeyboard($replyKeyboard);

    $array = $message->toArray();

    expect($array['reply_markup'])->toHaveKey('keyboard')
        ->and($array['reply_markup'])->not->toHaveKey('inline_keyboard');
});

it('toArray excludes boolean fields when false', function () {
    $message = TelegramMessage::create('Test')->to('123');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('disable_notification')
        ->and($array)->not->toHaveKey('protect_content')
        ->and($array)->not->toHaveKey('disable_web_page_preview');
});

it('create with empty string leaves content empty', function () {
    $message = TelegramMessage::create('');

    expect($message->getContent())->toBe('');
});

it('pre without language has no class attribute', function () {
    $message = TelegramMessage::create()->pre('echo hello');

    expect($message->getContent())->toBe('<pre><code>echo hello</code></pre>')
        ->and($message->getContent())->not->toContain('class=');
});

it('pre with language includes class attribute', function () {
    $message = TelegramMessage::create()->pre('echo hello', 'bash');

    expect($message->getContent())->toBe('<pre><code class="language-bash">echo hello</code></pre>');
});
