<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SamuelTerra22\TelegramNotifications\Http\Middleware\VerifyTelegramWebhook;

it('allows request when no secret is configured', function () {
    config()->set('telegram-notifications.webhook_secret', null);

    $request = Request::create('/webhook', 'POST');
    $middleware = new VerifyTelegramWebhook;

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('allows request when secret is empty string', function () {
    config()->set('telegram-notifications.webhook_secret', '');

    $request = Request::create('/webhook', 'POST');
    $middleware = new VerifyTelegramWebhook;

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('allows request with valid secret token', function () {
    config()->set('telegram-notifications.webhook_secret', 'my-secret-token');

    $request = Request::create('/webhook', 'POST');
    $request->headers->set('X-Telegram-Bot-Api-Secret-Token', 'my-secret-token');

    $middleware = new VerifyTelegramWebhook;

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('rejects request with invalid secret token', function () {
    config()->set('telegram-notifications.webhook_secret', 'my-secret-token');

    $request = Request::create('/webhook', 'POST');
    $request->headers->set('X-Telegram-Bot-Api-Secret-Token', 'wrong-token');

    $middleware = new VerifyTelegramWebhook;

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

it('rejects request with missing secret token header', function () {
    config()->set('telegram-notifications.webhook_secret', 'my-secret-token');

    $request = Request::create('/webhook', 'POST');

    $middleware = new VerifyTelegramWebhook;

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

it('returns 403 status code for invalid token', function () {
    config()->set('telegram-notifications.webhook_secret', 'my-secret-token');

    $request = Request::create('/webhook', 'POST');
    $request->headers->set('X-Telegram-Bot-Api-Secret-Token', 'wrong');

    $middleware = new VerifyTelegramWebhook;

    try {
        $middleware->handle($request, fn ($req) => response('OK'));
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        expect($e->getStatusCode())->toBe(403);

        return;
    }

    $this->fail('Expected HttpException was not thrown');
});
