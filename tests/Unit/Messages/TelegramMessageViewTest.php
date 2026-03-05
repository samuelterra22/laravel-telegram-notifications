<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;
use SamuelTerra22\TelegramNotifications\Messages\TelegramMessage;

it('renders a blade view as content', function () {
    View::addLocation(__DIR__.'/views');

    file_put_contents(__DIR__.'/views/test-telegram.blade.php', '<b>Hello from Blade</b>');

    $message = TelegramMessage::create()
        ->view('test-telegram');

    expect($message->getContent())->toBe('<b>Hello from Blade</b>');

    @unlink(__DIR__.'/views/test-telegram.blade.php');
});

it('sets parse mode from view() parameter', function () {
    View::addLocation(__DIR__.'/views');

    file_put_contents(__DIR__.'/views/test-telegram-md.blade.php', '**Bold text**');

    $message = TelegramMessage::create()
        ->view('test-telegram-md', [], 'MarkdownV2');

    $array = $message->toArray();

    expect($array['parse_mode'])->toBe('MarkdownV2')
        ->and($array['text'])->toBe('**Bold text**');

    @unlink(__DIR__.'/views/test-telegram-md.blade.php');
});

it('throws for invalid view name', function () {
    TelegramMessage::create()
        ->view('non-existent-view-name-xyz');
})->throws(InvalidArgumentException::class);

it('works with data variables', function () {
    View::addLocation(__DIR__.'/views');

    file_put_contents(__DIR__.'/views/test-telegram-data.blade.php', '<b>Hello {{ $name }}</b>');

    $message = TelegramMessage::create()
        ->view('test-telegram-data', ['name' => 'World']);

    expect($message->getContent())->toBe('<b>Hello World</b>');

    @unlink(__DIR__.'/views/test-telegram-data.blade.php');
});

beforeAll(function () {
    if (! is_dir(__DIR__.'/views')) {
        mkdir(__DIR__.'/views', 0755, true);
    }
});

afterAll(function () {
    $viewsDir = __DIR__.'/views';
    if (is_dir($viewsDir)) {
        array_map('unlink', glob($viewsDir.'/*.php') ?: []);
        @rmdir($viewsDir);
    }
});
