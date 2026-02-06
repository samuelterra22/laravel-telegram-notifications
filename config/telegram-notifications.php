<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Bot
    |--------------------------------------------------------------------------
    |
    | The default bot to use when sending messages. Must match a key in the
    | 'bots' array below.
    |
    */

    'default' => env('TELEGRAM_BOT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Bots
    |--------------------------------------------------------------------------
    |
    | Configure one or more Telegram bots. Each bot needs at minimum a token.
    | The chat_id and topic_id are optional defaults for that bot.
    |
    */

    'bots' => [
        'default' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'topic_id' => env('TELEGRAM_TOPIC_ID'),
        ],
        // 'alerts' => [
        //     'token' => env('TELEGRAM_ALERTS_BOT_TOKEN'),
        //     'chat_id' => env('TELEGRAM_ALERTS_CHAT_ID'),
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Telegram Bot API. Override for testing or when
    | using a local Bot API server.
    |
    */

    'api_base_url' => env('TELEGRAM_API_BASE_URL', 'https://api.telegram.org'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for HTTP requests to the Telegram API.
    |
    */

    'timeout' => (int) env('TELEGRAM_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the Monolog Telegram handler. Enable this to send error
    | logs directly to a Telegram chat.
    |
    */

    'logging' => [
        'enabled' => (bool) env('TELEGRAM_LOG_ENABLED', false),
        'bot' => env('TELEGRAM_LOG_BOT', 'default'),
        'chat_id' => env('TELEGRAM_LOG_CHAT_ID'),
        'topic_id' => env('TELEGRAM_LOG_TOPIC_ID'),
    ],

];
