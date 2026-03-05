<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class WebhookHandler
{
    /**
     * Handle an incoming Telegram webhook update.
     */
    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        if (isset($update['message'])) {
            $message = $update['message'];

            if (isset($message['text']) && str_starts_with($message['text'], '/')) {
                $parts = explode(' ', $message['text'], 2);
                $command = explode('@', ltrim($parts[0], '/'), 2)[0];
                $args = $parts[1] ?? '';

                $method = 'on'.ucfirst($command).'Command';

                if (method_exists($this, $method)) {
                    return $this->$method($message, $args);
                }

                return $this->onUnknownCommand($command, $message, $args);
            }

            return $this->onMessage($message);
        }

        if (isset($update['callback_query'])) {
            return $this->onCallbackQuery($update['callback_query']);
        }

        if (isset($update['inline_query'])) {
            return $this->onInlineQuery($update['inline_query']);
        }

        if (isset($update['pre_checkout_query'])) {
            return $this->onPreCheckoutQuery($update['pre_checkout_query']);
        }

        if (isset($update['shipping_query'])) {
            return $this->onShippingQuery($update['shipping_query']);
        }

        if (isset($update['message_reaction'])) {
            return $this->onMessageReaction($update['message_reaction']);
        }

        return $this->onUnhandledUpdate($update);
    }

    /**
     * @param  array<string, mixed>  $message
     */
    protected function onMessage(array $message): JsonResponse
    {
        return $this->ok();
    }

    /**
     * @param  array<string, mixed>  $callbackQuery
     */
    protected function onCallbackQuery(array $callbackQuery): JsonResponse
    {
        return $this->ok();
    }

    /**
     * @param  array<string, mixed>  $inlineQuery
     */
    protected function onInlineQuery(array $inlineQuery): JsonResponse
    {
        return $this->ok();
    }

    /**
     * @param  array<string, mixed>  $preCheckoutQuery
     */
    protected function onPreCheckoutQuery(array $preCheckoutQuery): JsonResponse
    {
        return $this->ok();
    }

    /**
     * @param  array<string, mixed>  $shippingQuery
     */
    protected function onShippingQuery(array $shippingQuery): JsonResponse
    {
        return $this->ok();
    }

    /**
     * @param  array<string, mixed>  $reaction
     */
    protected function onMessageReaction(array $reaction): JsonResponse
    {
        return $this->ok();
    }

    /**
     * @param  array<string, mixed>  $message
     */
    protected function onUnknownCommand(string $command, array $message, string $args): JsonResponse
    {
        return $this->ok();
    }

    /**
     * @param  array<string, mixed>  $update
     */
    protected function onUnhandledUpdate(array $update): JsonResponse
    {
        return $this->ok();
    }

    protected function ok(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }
}
