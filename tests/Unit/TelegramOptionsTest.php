<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Keyboards\InlineKeyboard;
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboard;
use SamuelTerra22\TelegramNotifications\Keyboards\ReplyKeyboardRemove;
use SamuelTerra22\TelegramNotifications\Telegram;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ]),
    ]);

    $this->telegram = new Telegram(
        botsConfig: ['default' => ['token' => 'test-token']],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

// --- $options on existing methods ---

it('passes options to sendMessage', function () {
    $this->telegram->sendMessage('-100123', 'Hello', options: [
        'disable_notification' => true,
        'protect_content' => true,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
        && $request['disable_notification'] === true
        && $request['protect_content'] === true
    );
});

it('passes options to sendChatAction', function () {
    $this->telegram->sendChatAction('-100123', \SamuelTerra22\TelegramNotifications\Enums\ChatAction::Typing, options: [
        'message_thread_id' => '42',
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChatAction')
        && $request['message_thread_id'] === '42'
    );
});

it('passes options to editMessageText', function () {
    $this->telegram->editMessageText('-100123', 456, 'Updated', options: [
        'disable_web_page_preview' => true,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageText')
        && $request['disable_web_page_preview'] === true
    );
});

it('passes options to editMessageCaption', function () {
    $this->telegram->editMessageCaption('-100123', 456, 'Caption', options: [
        'show_caption_above_media' => true,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageCaption')
        && $request['show_caption_above_media'] === true
    );
});

it('passes options to editMessageReplyMarkup', function () {
    $this->telegram->editMessageReplyMarkup('-100123', 456, ['inline_keyboard' => []], options: [
        'business_connection_id' => 'abc',
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageReplyMarkup')
        && $request['business_connection_id'] === 'abc'
    );
});

it('passes options to deleteMessage', function () {
    $this->telegram->deleteMessage('-100123', 456, options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteMessage'));
});

it('passes options to deleteMessages', function () {
    $this->telegram->deleteMessages('-100123', [1, 2], options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteMessages'));
});

it('passes options to forwardMessage', function () {
    $this->telegram->forwardMessage('-100dest', '-100src', 789, options: [
        'disable_notification' => true,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/forwardMessage')
        && $request['disable_notification'] === true
    );
});

it('passes options to copyMessage', function () {
    $this->telegram->copyMessage('-100dest', '-100src', 789, options: [
        'disable_notification' => true,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/copyMessage')
        && $request['disable_notification'] === true
    );
});

it('passes options to setWebhook', function () {
    $this->telegram->setWebhook('https://example.com/hook', options: [
        'max_connections' => 100,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setWebhook')
        && $request['max_connections'] === 100
    );
});

it('passes options to deleteWebhook', function () {
    $this->telegram->deleteWebhook(options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteWebhook'));
});

it('passes options to getChat', function () {
    $this->telegram->getChat('-100123', options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getChat'));
});

it('passes options to getChatMember', function () {
    $this->telegram->getChatMember('-100123', 789, options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getChatMember'));
});

it('passes options to getChatMemberCount', function () {
    $this->telegram->getChatMemberCount('-100123', options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getChatMemberCount'));
});

it('passes options to pinChatMessage', function () {
    $this->telegram->pinChatMessage('-100123', 456, options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/pinChatMessage'));
});

it('passes options to unpinChatMessage', function () {
    $this->telegram->unpinChatMessage('-100123', 456, options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/unpinChatMessage'));
});

it('passes options to unpinAllChatMessages', function () {
    $this->telegram->unpinAllChatMessages('-100123', options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/unpinAllChatMessages'));
});

it('passes options to getFile', function () {
    $this->telegram->getFile('file-id', options: []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getFile'));
});

it('passes options to setMyCommands', function () {
    $this->telegram->setMyCommands([], options: [
        'scope' => ['type' => 'all_private_chats'],
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMyCommands')
        && $request['scope'] === ['type' => 'all_private_chats']
    );
});

it('passes options to deleteMyCommands', function () {
    $this->telegram->deleteMyCommands(options: [
        'scope' => ['type' => 'all_group_chats'],
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteMyCommands')
        && $request['scope'] === ['type' => 'all_group_chats']
    );
});

it('passes options to getMyCommands', function () {
    $this->telegram->getMyCommands(options: [
        'scope' => ['type' => 'default'],
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getMyCommands')
        && $request['scope'] === ['type' => 'default']
    );
});

// --- reply_markup auto-encoding ---

it('auto-encodes InlineKeyboard in reply_markup option', function () {
    $keyboard = InlineKeyboard::make()
        ->callback('Click', 'data_1');

    $this->telegram->sendMessage('-100123', 'Hello', options: [
        'reply_markup' => $keyboard,
    ]);

    Http::assertSent(function ($request) {
        $markup = json_decode($request['reply_markup'], true);

        return $markup['inline_keyboard'][0][0]['text'] === 'Click';
    });
});

it('auto-encodes ReplyKeyboard in reply_markup option', function () {
    $keyboard = ReplyKeyboard::make()
        ->button('Option 1');

    $this->telegram->sendMessage('-100123', 'Hello', options: [
        'reply_markup' => $keyboard,
    ]);

    Http::assertSent(function ($request) {
        $markup = json_decode($request['reply_markup'], true);

        return isset($markup['keyboard']);
    });
});

it('auto-encodes ReplyKeyboardRemove in reply_markup option', function () {
    $remove = ReplyKeyboardRemove::make();

    $this->telegram->sendMessage('-100123', 'Hello', options: [
        'reply_markup' => $remove,
    ]);

    Http::assertSent(function ($request) {
        $markup = json_decode($request['reply_markup'], true);

        return $markup['remove_keyboard'] === true;
    });
});

it('auto-encodes array reply_markup as JSON', function () {
    $markup = ['inline_keyboard' => [[['text' => 'Test', 'callback_data' => 'test']]]];

    $this->telegram->sendMessage('-100123', 'Hello', options: [
        'reply_markup' => $markup,
    ]);

    Http::assertSent(function ($request) {
        $decoded = json_decode($request['reply_markup'], true);

        return $decoded['inline_keyboard'][0][0]['text'] === 'Test';
    });
});

it('does not encode reply_markup when not present', function () {
    $this->telegram->sendMessage('-100123', 'Hello', options: [
        'disable_notification' => true,
    ]);

    Http::assertSent(function ($request) {
        return ! isset($request['reply_markup'])
            && $request['disable_notification'] === true;
    });
});
