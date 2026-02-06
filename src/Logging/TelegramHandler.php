<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;

class TelegramHandler extends AbstractProcessingHandler
{
    /** @var array<string, string> */
    private const LEVEL_EMOJIS = [
        'DEBUG' => "\xF0\x9F\x94\x8D",
        'INFO' => "\xE2\x84\xB9\xEF\xB8\x8F",
        'NOTICE' => "\xF0\x9F\x93\x8B",
        'WARNING' => "\xE2\x9A\xA0\xEF\xB8\x8F",
        'ERROR' => "\xF0\x9F\x94\xB4",
        'CRITICAL' => "\xF0\x9F\x94\xA5",
        'ALERT' => "\xF0\x9F\x9A\xA8",
        'EMERGENCY' => "\xE2\x9B\x94",
    ];

    public function __construct(
        private readonly TelegramBotApi $api,
        private readonly string $chatId,
        private readonly ?string $topicId = null,
        int|string|Level $level = Level::Error,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $message = $this->formatMessage($record);

        $this->api->callSilent('sendMessage', array_filter([
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'message_thread_id' => $this->topicId,
        ]));
    }

    private function formatMessage(LogRecord $record): string
    {
        $emoji = self::LEVEL_EMOJIS[strtoupper($record->level->name)];
        $appName = config('app.name', 'Laravel');
        $environment = config('app.env', 'production');

        $lines = [];
        $lines[] = "{$emoji} <b>{$record->level->name}</b>";
        $lines[] = '';
        $lines[] = "<b>App:</b> {$this->escapeHtml($appName)}";
        $lines[] = "<b>Env:</b> {$this->escapeHtml($environment)}";
        $lines[] = '';
        $lines[] = '<b>Message:</b>';
        $lines[] = $this->escapeHtml($record->message);

        if (! empty($record->context)) {
            $exception = $record->context['exception'] ?? null;

            if ($exception instanceof \Throwable) {
                $lines[] = '';
                $lines[] = "<b>Exception:</b> {$this->escapeHtml(get_class($exception))}";
                $lines[] = "<b>File:</b> {$this->escapeHtml($exception->getFile())}:{$exception->getLine()}";

                $trace = $exception->getTraceAsString();
                $truncatedTrace = mb_strlen($trace) > 2000
                    ? mb_substr($trace, 0, 2000).'...'
                    : $trace;
                $lines[] = '';
                $lines[] = '<pre>'.htmlspecialchars($truncatedTrace, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</pre>';
            }
        }

        $text = implode("\n", $lines);

        if (mb_strlen($text) > 4096) {
            $text = mb_substr($text, 0, 4089).'...</b>';
        }

        return $text;
    }

    private function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
