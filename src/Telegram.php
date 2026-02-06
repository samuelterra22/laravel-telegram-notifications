<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications;

use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Enums\ChatAction;

class Telegram
{
    /** @var array<string, TelegramBotApi> */
    private array $bots = [];

    /**
     * @param  array<string, array{token: string, chat_id?: string|null, topic_id?: string|null}>  $botsConfig
     */
    public function __construct(
        private readonly array $botsConfig,
        private readonly string $defaultBot,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    /**
     * Get a bot API instance by name.
     */
    public function bot(?string $name = null): TelegramBotApi
    {
        $name ??= $this->defaultBot;

        if (! isset($this->bots[$name])) {
            $config = $this->botsConfig[$name]
                ?? throw new \InvalidArgumentException("Bot [{$name}] not configured.");

            $this->bots[$name] = new TelegramBotApi(
                token: $config['token'],
                baseUrl: $this->baseUrl,
                timeout: $this->timeout,
            );
        }

        return $this->bots[$name];
    }

    /**
     * Send a text message.
     *
     * @return array<string, mixed>
     */
    public function sendMessage(
        string $chatId,
        string $text,
        ?string $parseMode = 'HTML',
        ?string $topicId = null,
    ): array {
        $params = array_filter([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'message_thread_id' => $topicId,
        ]);

        return $this->bot()->call('sendMessage', $params);
    }

    /**
     * Send a chat action (typing, upload_photo, etc.).
     *
     * @return array<string, mixed>
     */
    public function sendChatAction(string $chatId, ChatAction $action): array
    {
        return $this->bot()->call('sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action->value,
        ]);
    }

    /**
     * Edit a message text.
     *
     * @return array<string, mixed>
     */
    public function editMessageText(
        string $chatId,
        int|string $messageId,
        string $text,
        ?string $parseMode = 'HTML',
    ): array {
        return $this->bot()->call('editMessageText', array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ]));
    }

    /**
     * Edit a message caption.
     *
     * @return array<string, mixed>
     */
    public function editMessageCaption(
        string $chatId,
        int|string $messageId,
        string $caption,
        ?string $parseMode = 'HTML',
    ): array {
        return $this->bot()->call('editMessageCaption', array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => $caption,
            'parse_mode' => $parseMode,
        ]));
    }

    /**
     * Edit a message reply markup.
     *
     * @param  array<string, mixed>  $replyMarkup
     * @return array<string, mixed>
     */
    public function editMessageReplyMarkup(
        string $chatId,
        int|string $messageId,
        array $replyMarkup,
    ): array {
        return $this->bot()->call('editMessageReplyMarkup', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup,
        ]);
    }

    /**
     * Delete a single message.
     *
     * @return array<string, mixed>
     */
    public function deleteMessage(string $chatId, int|string $messageId): array
    {
        return $this->bot()->call('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Delete multiple messages.
     *
     * @param  array<int, int|string>  $messageIds
     * @return array<string, mixed>
     */
    public function deleteMessages(string $chatId, array $messageIds): array
    {
        return $this->bot()->call('deleteMessages', [
            'chat_id' => $chatId,
            'message_ids' => $messageIds,
        ]);
    }

    /**
     * Forward a message.
     *
     * @return array<string, mixed>
     */
    public function forwardMessage(
        string $chatId,
        string $fromChatId,
        int|string $messageId,
    ): array {
        return $this->bot()->call('forwardMessage', [
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Copy a message.
     *
     * @return array<string, mixed>
     */
    public function copyMessage(
        string $chatId,
        string $fromChatId,
        int|string $messageId,
    ): array {
        return $this->bot()->call('copyMessage', [
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Get bot info.
     *
     * @return array<string, mixed>
     */
    public function getMe(): array
    {
        return $this->bot()->call('getMe');
    }

    /**
     * Set webhook URL.
     *
     * @return array<string, mixed>
     */
    public function setWebhook(string $url, ?string $secretToken = null): array
    {
        return $this->bot()->call('setWebhook', array_filter([
            'url' => $url,
            'secret_token' => $secretToken,
        ]));
    }

    /**
     * Delete webhook.
     *
     * @return array<string, mixed>
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): array
    {
        return $this->bot()->call('deleteWebhook', [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
    }

    /**
     * Get webhook info.
     *
     * @return array<string, mixed>
     */
    public function getWebhookInfo(): array
    {
        return $this->bot()->call('getWebhookInfo');
    }

    /**
     * Get chat info.
     *
     * @return array<string, mixed>
     */
    public function getChat(string $chatId): array
    {
        return $this->bot()->call('getChat', [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * Get chat member info.
     *
     * @return array<string, mixed>
     */
    public function getChatMember(string $chatId, int|string $userId): array
    {
        return $this->bot()->call('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Get chat member count.
     *
     * @return array<string, mixed>
     */
    public function getChatMemberCount(string $chatId): array
    {
        return $this->bot()->call('getChatMemberCount', [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * Pin a chat message.
     *
     * @return array<string, mixed>
     */
    public function pinChatMessage(string $chatId, int|string $messageId, bool $disableNotification = false): array
    {
        return $this->bot()->call('pinChatMessage', array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification ?: null,
        ]));
    }

    /**
     * Unpin a chat message.
     *
     * @return array<string, mixed>
     */
    public function unpinChatMessage(string $chatId, ?int $messageId = null): array
    {
        return $this->bot()->call('unpinChatMessage', array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]));
    }

    /**
     * Unpin all chat messages.
     *
     * @return array<string, mixed>
     */
    public function unpinAllChatMessages(string $chatId): array
    {
        return $this->bot()->call('unpinAllChatMessages', [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * Get file info for downloading.
     *
     * @return array<string, mixed>
     */
    public function getFile(string $fileId): array
    {
        return $this->bot()->call('getFile', [
            'file_id' => $fileId,
        ]);
    }

    /**
     * Set bot commands.
     *
     * @param  array<int, array{command: string, description: string}>  $commands
     * @return array<string, mixed>
     */
    public function setMyCommands(array $commands): array
    {
        return $this->bot()->call('setMyCommands', [
            'commands' => $commands,
        ]);
    }

    /**
     * Delete bot commands.
     *
     * @return array<string, mixed>
     */
    public function deleteMyCommands(): array
    {
        return $this->bot()->call('deleteMyCommands');
    }

    /**
     * Get bot commands.
     *
     * @return array<string, mixed>
     */
    public function getMyCommands(): array
    {
        return $this->bot()->call('getMyCommands');
    }

    public function getDefaultBot(): string
    {
        return $this->defaultBot;
    }

    /**
     * @return array<string, array{token: string, chat_id?: string|null, topic_id?: string|null}>
     */
    public function getBotsConfig(): array
    {
        return $this->botsConfig;
    }
}
