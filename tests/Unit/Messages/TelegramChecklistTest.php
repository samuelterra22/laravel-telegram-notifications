<?php

declare(strict_types=1);

use SamuelTerra22\TelegramNotifications\Messages\TelegramChecklist;

it('creates checklist with items', function () {
    $message = TelegramChecklist::create()
        ->to('-100123')
        ->title('My Tasks')
        ->item('Task 1')
        ->item('Task 2', true);

    $array = $message->toArray();

    expect($array['chat_id'])->toBe('-100123')
        ->and($array['title'])->toBe('My Tasks')
        ->and($array['checklist'])->toBe([
            ['text' => 'Task 1', 'checked' => false],
            ['text' => 'Task 2', 'checked' => true],
        ]);
});

it('creates with checked and unchecked items', function () {
    $message = TelegramChecklist::create()
        ->to('-100123')
        ->title('Shopping List')
        ->checkedItem('Milk')
        ->uncheckedItem('Bread')
        ->checkedItem('Eggs');

    $array = $message->toArray();

    expect($array['checklist'])->toBe([
        ['text' => 'Milk', 'checked' => true],
        ['text' => 'Bread', 'checked' => false],
        ['text' => 'Eggs', 'checked' => true],
    ]);
});

it('supports silent and protected', function () {
    $message = TelegramChecklist::create()
        ->to('-100123')
        ->title('Tasks')
        ->item('Task 1')
        ->silent()
        ->protected();

    $array = $message->toArray();

    expect($array['disable_notification'])->toBeTrue()
        ->and($array['protect_content'])->toBeTrue();
});

it('supports message effect', function () {
    $message = TelegramChecklist::create()
        ->to('-100123')
        ->title('Tasks')
        ->item('Task 1')
        ->effect('5104841245755180586');

    $array = $message->toArray();

    expect($array['message_effect_id'])->toBe('5104841245755180586');
});

it('supports topic', function () {
    $message = TelegramChecklist::create()
        ->to('-100123')
        ->topic('42')
        ->title('Tasks')
        ->item('Task 1');

    $array = $message->toArray();

    expect($array['message_thread_id'])->toBe('42');
});

it('getApiMethod returns sendChecklist', function () {
    $message = TelegramChecklist::create();

    expect($message->getApiMethod())->toBe('sendChecklist');
});

it('handles empty items', function () {
    $message = TelegramChecklist::create()
        ->to('-100123')
        ->title('Empty List');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('checklist');
});

it('does not include title when empty', function () {
    $message = TelegramChecklist::create()
        ->to('-100123')
        ->item('Task 1');

    $array = $message->toArray();

    expect($array)->not->toHaveKey('title');
});
