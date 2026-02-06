<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Enums\ChatAction;
use SamuelTerra22\TelegramNotifications\Telegram;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ]),
    ]);

    $this->telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'default-token'],
            'alerts' => ['token' => 'alerts-token'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('gets default bot', function () {
    $bot = $this->telegram->bot();

    expect($bot->getToken())->toBe('default-token');
});

it('gets named bot', function () {
    $bot = $this->telegram->bot('alerts');

    expect($bot->getToken())->toBe('alerts-token');
});

it('reuses bot instances', function () {
    $bot1 = $this->telegram->bot('default');
    $bot2 = $this->telegram->bot('default');

    expect($bot1)->toBe($bot2);
});

it('throws for unconfigured bot', function () {
    $this->telegram->bot('nonexistent');
})->throws(InvalidArgumentException::class, 'Bot [nonexistent] not configured.');

it('sends a message', function () {
    $result = $this->telegram->sendMessage('-100123', 'Hello');

    expect($result['ok'])->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
        && $request['chat_id'] === '-100123'
        && $request['text'] === 'Hello'
        && $request['parse_mode'] === 'HTML'
    );
});

it('sends a message with topic', function () {
    $this->telegram->sendMessage('-100123', 'Hello', topicId: '42');

    Http::assertSent(fn ($request) => $request['message_thread_id'] === '42');
});

it('sends chat action', function () {
    $this->telegram->sendChatAction('-100123', ChatAction::Typing);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendChatAction')
        && $request['action'] === 'typing'
    );
});

it('edits message text', function () {
    $this->telegram->editMessageText('-100123', 456, 'Updated');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageText')
        && $request['message_id'] === 456
        && $request['text'] === 'Updated'
    );
});

it('deletes a message', function () {
    $this->telegram->deleteMessage('-100123', 456);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteMessage')
        && $request['message_id'] === 456
    );
});

it('deletes multiple messages', function () {
    $this->telegram->deleteMessages('-100123', [1, 2, 3]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteMessages')
        && $request['message_ids'] === [1, 2, 3]
    );
});

it('forwards a message', function () {
    $this->telegram->forwardMessage('-100dest', '-100source', 789);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/forwardMessage')
        && $request['from_chat_id'] === '-100source'
        && $request['message_id'] === 789
    );
});

it('copies a message', function () {
    $this->telegram->copyMessage('-100dest', '-100source', 789);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/copyMessage'));
});

it('gets bot info', function () {
    $this->telegram->getMe();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getMe'));
});

it('sets webhook', function () {
    $this->telegram->setWebhook('https://example.com/webhook', 'secret123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setWebhook')
        && $request['url'] === 'https://example.com/webhook'
        && $request['secret_token'] === 'secret123'
    );
});

it('deletes webhook', function () {
    $this->telegram->deleteWebhook(true);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteWebhook')
        && $request['drop_pending_updates'] === true
    );
});

it('gets chat info', function () {
    $this->telegram->getChat('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getChat'));
});

it('gets chat member count', function () {
    $this->telegram->getChatMemberCount('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getChatMemberCount'));
});

it('returns default bot name', function () {
    expect($this->telegram->getDefaultBot())->toBe('default');
});

it('returns bots config', function () {
    expect($this->telegram->getBotsConfig())->toHaveKeys(['default', 'alerts']);
});

it('pins chat message', function () {
    $this->telegram->pinChatMessage('-100123', 456);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/pinChatMessage'));
});

it('unpins chat message', function () {
    $this->telegram->unpinChatMessage('-100123', 456);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/unpinChatMessage'));
});

it('unpins all chat messages', function () {
    $this->telegram->unpinAllChatMessages('-100123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/unpinAllChatMessages'));
});

it('edits message caption', function () {
    $this->telegram->editMessageCaption('-100123', 456, 'New caption');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageCaption')
        && $request['chat_id'] === '-100123'
        && $request['message_id'] === 456
        && $request['caption'] === 'New caption'
        && $request['parse_mode'] === 'HTML'
    );
});

it('edits message caption without parse mode', function () {
    $this->telegram->editMessageCaption('-100123', 456, 'New caption', null);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageCaption')
        && $request['caption'] === 'New caption'
    );
});

it('edits message reply markup', function () {
    $replyMarkup = [
        'inline_keyboard' => [
            [['text' => 'Button', 'callback_data' => 'test']],
        ],
    ];

    $this->telegram->editMessageReplyMarkup('-100123', 456, $replyMarkup);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageReplyMarkup')
        && $request['chat_id'] === '-100123'
        && $request['message_id'] === 456
        && $request['reply_markup'] === $replyMarkup
    );
});

it('gets chat member', function () {
    $this->telegram->getChatMember('-100123', 789);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getChatMember')
        && $request['chat_id'] === '-100123'
        && $request['user_id'] === 789
    );
});

it('gets webhook info', function () {
    $this->telegram->getWebhookInfo();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getWebhookInfo'));
});

it('gets file info', function () {
    $this->telegram->getFile('AgACAgIAAxkBAAI');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getFile')
        && $request['file_id'] === 'AgACAgIAAxkBAAI'
    );
});

it('sets bot commands', function () {
    $commands = [
        ['command' => 'start', 'description' => 'Start the bot'],
        ['command' => 'help', 'description' => 'Show help'],
    ];

    $this->telegram->setMyCommands($commands);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMyCommands')
        && $request['commands'] === $commands
    );
});

it('deletes bot commands', function () {
    $this->telegram->deleteMyCommands();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteMyCommands'));
});

it('gets bot commands', function () {
    $this->telegram->getMyCommands();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getMyCommands'));
});

// --- Unhappy path / edge case tests ---

it('throws InvalidArgumentException for empty string bot name', function () {
    $this->telegram->bot('');
})->throws(InvalidArgumentException::class, 'Bot [] not configured.');

it('sends deleteMessages with empty array', function () {
    $this->telegram->deleteMessages('-100123', []);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/deleteMessages')
        && $request['message_ids'] === []
    );
});

it('filters out null parseMode in sendMessage', function () {
    $this->telegram->sendMessage('-100123', 'Hello', parseMode: null);

    Http::assertSent(function ($request) {
        // array_filter removes null values, so parse_mode should not be present
        return str_contains($request->url(), '/sendMessage')
            && $request['text'] === 'Hello'
            && ! isset($request['parse_mode']);
    });
});

it('sends editMessageText with empty text to API', function () {
    $this->telegram->editMessageText('-100123', 456, '');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/editMessageText')
        && $request['message_id'] === 456
    );
});

it('sends setMyCommands with empty commands array', function () {
    $this->telegram->setMyCommands([]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/setMyCommands')
        && $request['commands'] === []
    );
});
