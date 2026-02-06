<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramWebhook
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('telegram-notifications.webhook_secret');

        if ($secret === null || $secret === '') {
            return $next($request);
        }

        $token = $request->header('X-Telegram-Bot-Api-Secret-Token');

        if ($token !== $secret) {
            abort(403, 'Invalid webhook secret token.');
        }

        return $next($request);
    }
}
