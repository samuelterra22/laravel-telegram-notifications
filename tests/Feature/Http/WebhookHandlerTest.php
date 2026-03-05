<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SamuelTerra22\TelegramNotifications\Http\WebhookHandler;

// Define concrete test handler
class TestBotHandler extends WebhookHandler
{
    /** @var array<int, array<string, mixed>> */
    public array $log = [];

    protected function onStartCommand(array $message, string $args): JsonResponse
    {
        $this->log[] = ['command' => 'start', 'args' => $args];

        return $this->ok();
    }

    protected function onHelpCommand(array $message, string $args): JsonResponse
    {
        $this->log[] = ['command' => 'help', 'args' => $args];

        return $this->ok();
    }

    protected function onMessage(array $message): JsonResponse
    {
        $this->log[] = ['message' => $message['text'] ?? ''];

        return $this->ok();
    }

    protected function onCallbackQuery(array $callbackQuery): JsonResponse
    {
        $this->log[] = ['callback' => $callbackQuery['data'] ?? ''];

        return $this->ok();
    }

    protected function onInlineQuery(array $inlineQuery): JsonResponse
    {
        $this->log[] = ['inline' => $inlineQuery['query'] ?? ''];

        return $this->ok();
    }

    protected function onPreCheckoutQuery(array $preCheckoutQuery): JsonResponse
    {
        $this->log[] = ['pre_checkout' => $preCheckoutQuery['id'] ?? ''];

        return $this->ok();
    }

    protected function onShippingQuery(array $shippingQuery): JsonResponse
    {
        $this->log[] = ['shipping' => $shippingQuery['id'] ?? ''];

        return $this->ok();
    }

    protected function onMessageReaction(array $reaction): JsonResponse
    {
        $this->log[] = ['reaction' => true];

        return $this->ok();
    }

    protected function onUnknownCommand(string $command, array $message, string $args): JsonResponse
    {
        $this->log[] = ['unknown_command' => $command];

        return $this->ok();
    }
}

it('routes /start command', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['message' => ['text' => '/start hello world', 'chat' => ['id' => 123]]]);

    $response = $handler->handle($request);
    expect($response->getStatusCode())->toBe(200);
    expect($handler->log)->toHaveCount(1);
    expect($handler->log[0])->toBe(['command' => 'start', 'args' => 'hello world']);
});

it('routes /help command', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['message' => ['text' => '/help', 'chat' => ['id' => 123]]]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['command' => 'help', 'args' => '']);
});

it('strips @botname from commands', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['message' => ['text' => '/start@MyBot arguments', 'chat' => ['id' => 123]]]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['command' => 'start', 'args' => 'arguments']);
});

it('routes plain message to onMessage', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['message' => ['text' => 'Hello there', 'chat' => ['id' => 123]]]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['message' => 'Hello there']);
});

it('routes callback query', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['callback_query' => ['id' => '123', 'data' => 'action:confirm']]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['callback' => 'action:confirm']);
});

it('routes inline query', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['inline_query' => ['id' => '123', 'query' => 'search term']]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['inline' => 'search term']);
});

it('routes pre_checkout_query', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['pre_checkout_query' => ['id' => 'pq123', 'currency' => 'XTR']]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['pre_checkout' => 'pq123']);
});

it('routes shipping_query', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['shipping_query' => ['id' => 'sq123']]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['shipping' => 'sq123']);
});

it('routes message_reaction', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['message_reaction' => ['chat' => ['id' => 123], 'message_id' => 456]]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['reaction' => true]);
});

it('handles unknown command', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['message' => ['text' => '/nonexistent', 'chat' => ['id' => 123]]]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['unknown_command' => 'nonexistent']);
});

it('handles unrecognized update type', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['poll_answer' => ['poll_id' => '123']]);

    $response = $handler->handle($request);
    expect($response->getStatusCode())->toBe(200);
    expect($handler->log)->toBeEmpty();
});

it('handles message without text', function () {
    $handler = new TestBotHandler;
    $request = Request::create('/webhook', 'POST');
    $request->merge(['message' => ['photo' => [['file_id' => 'abc']], 'chat' => ['id' => 123]]]);

    $handler->handle($request);
    expect($handler->log[0])->toBe(['message' => '']);
});
