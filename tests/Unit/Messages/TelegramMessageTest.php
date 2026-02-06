<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Enums\ParseMode;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboard;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;

it('creates a message with content', function () {
    $message = TelegramMessage::create('Hello World');

    expect($message->getContent())->toBe('Hello World')
        ->and($message->getApiMethod())->toBe('sendMessage');
});

it('builds message with fluent API', function () {
    $message = TelegramMessage::create()
        ->to('-1001234567890')
        ->topic('42')
        ->bot('alerts')
        ->content('Test message');

    expect($message->getChatId())->toBe('-1001234567890')
        ->and($message->getTopicId())->toBe('42')
        ->and($message->getBot())->toBe('alerts')
        ->and($message->getContent())->toBe('Test message');
});

it('appends lines correctly', function () {
    $message = TelegramMessage::create('Line 1')
        ->line('Line 2')
        ->line('Line 3');

    expect($message->getContent())->toBe("Line 1\nLine 2\nLine 3");
});

it('formats bold text', function () {
    $message = TelegramMessage::create()
        ->bold('Important');

    expect($message->getContent())->toBe('<b>Important</b>');
});

it('formats italic text', function () {
    $message = TelegramMessage::create()
        ->italic('Emphasis');

    expect($message->getContent())->toBe('<i>Emphasis</i>');
});

it('formats underline text', function () {
    $message = TelegramMessage::create()
        ->underline('Underlined');

    expect($message->getContent())->toBe('<u>Underlined</u>');
});

it('formats strikethrough text', function () {
    $message = TelegramMessage::create()
        ->strikethrough('Deleted');

    expect($message->getContent())->toBe('<s>Deleted</s>');
});

it('formats code text', function () {
    $message = TelegramMessage::create()
        ->code('var_dump()');

    expect($message->getContent())->toBe('<code>var_dump()</code>');
});

it('formats pre text without language', function () {
    $message = TelegramMessage::create()
        ->pre('echo "hello"');

    expect($message->getContent())->toBe('<pre><code>echo "hello"</code></pre>');
});

it('formats pre text with language', function () {
    $message = TelegramMessage::create()
        ->pre('echo "hello"', 'php');

    expect($message->getContent())->toBe('<pre><code class="language-php">echo "hello"</code></pre>');
});

it('formats links', function () {
    $message = TelegramMessage::create()
        ->link('Click here', 'https://example.com');

    expect($message->getContent())->toBe('<a href="https://example.com">Click here</a>');
});

it('formats spoiler text', function () {
    $message = TelegramMessage::create()
        ->spoiler('Hidden text');

    expect($message->getContent())->toBe('<tg-spoiler>Hidden text</tg-spoiler>');
});

it('formats quote text', function () {
    $message = TelegramMessage::create()
        ->quote('A wise saying');

    expect($message->getContent())->toBe('<blockquote>A wise saying</blockquote>');
});

it('sets parse mode', function () {
    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->parseMode(ParseMode::MarkdownV2);

    $array = $message->toArray();

    expect($array['parse_mode'])->toBe('MarkdownV2');
});

it('sets silent notification', function () {
    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->silent();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue();
});

it('sets protected content', function () {
    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->protected();

    $array = $message->toArray();

    expect($array['protect_content'])->toBeTrue();
});

it('sets reply to message', function () {
    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->replyTo('456');

    $array = $message->toArray();

    expect($array['reply_parameters'])->toBe(['message_id' => '456']);
});

it('disables web page preview', function () {
    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->disableWebPagePreview();

    $array = $message->toArray();

    expect($array['disable_web_page_preview'])->toBeTrue();
});

it('adds URL buttons', function () {
    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->button('Click', 'https://example.com');

    $array = $message->toArray();

    expect($array['reply_markup'])->toBeArray()
        ->and($array['reply_markup']['inline_keyboard'])->toHaveCount(1);
});

it('adds callback buttons', function () {
    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->buttonWithCallback('Action', 'do_something');

    $array = $message->toArray();

    expect($array['reply_markup']['inline_keyboard'])->toHaveCount(1);
});

it('accepts an InlineKeyboard instance', function () {
    $keyboard = InlineKeyboard::make()
        ->url('Link', 'https://example.com')
        ->callback('Action', 'data');

    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->keyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup']['inline_keyboard'])->toHaveCount(1);
});

it('accepts a ReplyKeyboard instance', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('Option A')
        ->button('Option B');

    $message = TelegramMessage::create('Test')
        ->to('-100123')
        ->replyKeyboard($keyboard);

    $array = $message->toArray();

    expect($array['reply_markup']['keyboard'])->toHaveCount(1);
});

it('produces correct toArray output', function () {
    $message = TelegramMessage::create('Hello')
        ->to('-1001234567890')
        ->topic('42');

    $array = $message->toArray();

    expect($array)->toBe([
        'chat_id' => '-1001234567890',
        'text' => 'Hello',
        'parse_mode' => 'HTML',
        'message_thread_id' => '42',
    ]);
});

it('splits content exceeding 4096 characters', function () {
    $longText = str_repeat('A', 5000);
    $message = TelegramMessage::create($longText);

    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(2)
        ->and(mb_strlen($chunks[0]))->toBeLessThanOrEqual(4096)
        ->and(implode('', $chunks))->toHaveLength(5000);
});

it('does not split content under 4096 characters', function () {
    $text = str_repeat('A', 4000);
    $message = TelegramMessage::create($text);

    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(1);
});

it('splits at newline when possible', function () {
    $text = str_repeat('A', 3000)."\n".str_repeat('B', 2000);
    $message = TelegramMessage::create($text);

    $chunks = $message->splitContent();

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0])->toBe(str_repeat('A', 3000))
        ->and($chunks[1])->toBe(str_repeat('B', 2000));
});

it('creates empty message via static factory', function () {
    $message = TelegramMessage::create();

    expect($message->getContent())->toBe('')
        ->and($message->getChatId())->toBeNull()
        ->and($message->getBot())->toBeNull();
});
