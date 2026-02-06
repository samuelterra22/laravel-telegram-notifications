<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications;

use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Contracts\ReplyMarkupInterface;
use SamuelTerra22\TelegramNotifications\Enums\ChatAction;
use SamuelTerra22\TelegramNotifications\Fluent\PendingBroadcast;
use SamuelTerra22\TelegramNotifications\Fluent\PendingMessage;

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
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendMessage(
        string $chatId,
        string $text,
        ?string $parseMode = 'HTML',
        ?string $topicId = null,
        array $options = [],
    ): array {
        $params = array_filter([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'message_thread_id' => $topicId,
        ]);

        return $this->bot()->call('sendMessage', array_merge($params, $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a chat action (typing, upload_photo, etc.).
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendChatAction(string $chatId, ChatAction $action, array $options = []): array
    {
        return $this->bot()->call('sendChatAction', array_merge([
            'chat_id' => $chatId,
            'action' => $action->value,
        ], $this->encodeReplyMarkup($options)));
    }

    /**
     * Edit a message text.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function editMessageText(
        string $chatId,
        int|string $messageId,
        string $text,
        ?string $parseMode = 'HTML',
        array $options = [],
    ): array {
        return $this->bot()->call('editMessageText', array_merge(array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Edit a message caption.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function editMessageCaption(
        string $chatId,
        int|string $messageId,
        string $caption,
        ?string $parseMode = 'HTML',
        array $options = [],
    ): array {
        return $this->bot()->call('editMessageCaption', array_merge(array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => $caption,
            'parse_mode' => $parseMode,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Edit a message reply markup.
     *
     * @param  array<string, mixed>  $replyMarkup
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function editMessageReplyMarkup(
        string $chatId,
        int|string $messageId,
        array $replyMarkup,
        array $options = [],
    ): array {
        return $this->bot()->call('editMessageReplyMarkup', array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup,
        ], $this->encodeReplyMarkup($options)));
    }

    /**
     * Edit a message media.
     *
     * @param  array<string, mixed>  $media
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function editMessageMedia(
        string $chatId,
        int|string $messageId,
        array $media,
        array $options = [],
    ): array {
        return $this->bot()->call('editMessageMedia', array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'media' => $media,
        ], $this->encodeReplyMarkup($options)));
    }

    /**
     * Delete a single message.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function deleteMessage(string $chatId, int|string $messageId, array $options = []): array
    {
        return $this->bot()->call('deleteMessage', array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $options));
    }

    /**
     * Delete multiple messages.
     *
     * @param  array<int, int|string>  $messageIds
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function deleteMessages(string $chatId, array $messageIds, array $options = []): array
    {
        return $this->bot()->call('deleteMessages', array_merge([
            'chat_id' => $chatId,
            'message_ids' => $messageIds,
        ], $options));
    }

    /**
     * Forward a message.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function forwardMessage(
        string $chatId,
        string $fromChatId,
        int|string $messageId,
        array $options = [],
    ): array {
        return $this->bot()->call('forwardMessage', array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], $options));
    }

    /**
     * Copy a message.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function copyMessage(
        string $chatId,
        string $fromChatId,
        int|string $messageId,
        array $options = [],
    ): array {
        return $this->bot()->call('copyMessage', array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], $this->encodeReplyMarkup($options)));
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
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function setWebhook(string $url, ?string $secretToken = null, array $options = []): array
    {
        return $this->bot()->call('setWebhook', array_merge(array_filter([
            'url' => $url,
            'secret_token' => $secretToken,
        ]), $options));
    }

    /**
     * Delete webhook.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function deleteWebhook(bool $dropPendingUpdates = false, array $options = []): array
    {
        return $this->bot()->call('deleteWebhook', array_merge([
            'drop_pending_updates' => $dropPendingUpdates,
        ], $options));
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
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getChat(string $chatId, array $options = []): array
    {
        return $this->bot()->call('getChat', array_merge([
            'chat_id' => $chatId,
        ], $options));
    }

    /**
     * Get chat member info.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getChatMember(string $chatId, int|string $userId, array $options = []): array
    {
        return $this->bot()->call('getChatMember', array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options));
    }

    /**
     * Get chat member count.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getChatMemberCount(string $chatId, array $options = []): array
    {
        return $this->bot()->call('getChatMemberCount', array_merge([
            'chat_id' => $chatId,
        ], $options));
    }

    /**
     * Pin a chat message.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function pinChatMessage(string $chatId, int|string $messageId, bool $disableNotification = false, array $options = []): array
    {
        return $this->bot()->call('pinChatMessage', array_merge(array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification ?: null,
        ]), $options));
    }

    /**
     * Unpin a chat message.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function unpinChatMessage(string $chatId, ?int $messageId = null, array $options = []): array
    {
        return $this->bot()->call('unpinChatMessage', array_merge(array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]), $options));
    }

    /**
     * Unpin all chat messages.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function unpinAllChatMessages(string $chatId, array $options = []): array
    {
        return $this->bot()->call('unpinAllChatMessages', array_merge([
            'chat_id' => $chatId,
        ], $options));
    }

    /**
     * Get file info for downloading.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getFile(string $fileId, array $options = []): array
    {
        return $this->bot()->call('getFile', array_merge([
            'file_id' => $fileId,
        ], $options));
    }

    /**
     * Set bot commands.
     *
     * @param  array<int, array{command: string, description: string}>  $commands
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function setMyCommands(array $commands, array $options = []): array
    {
        return $this->bot()->call('setMyCommands', array_merge([
            'commands' => $commands,
        ], $options));
    }

    /**
     * Delete bot commands.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function deleteMyCommands(array $options = []): array
    {
        return $this->bot()->call('deleteMyCommands', $options);
    }

    /**
     * Get bot commands.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getMyCommands(array $options = []): array
    {
        return $this->bot()->call('getMyCommands', $options);
    }

    // -----------------------------------------------------------------------
    // Media methods
    // -----------------------------------------------------------------------

    /**
     * Send a photo.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendPhoto(
        string $chatId,
        string $photo,
        ?string $caption = null,
        ?string $parseMode = 'HTML',
        array $options = [],
    ): array {
        return $this->bot()->call('sendPhoto', array_merge(array_filter([
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
            'parse_mode' => $caption !== null ? $parseMode : null,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a document.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendDocument(
        string $chatId,
        string $document,
        ?string $caption = null,
        ?string $parseMode = 'HTML',
        array $options = [],
    ): array {
        return $this->bot()->call('sendDocument', array_merge(array_filter([
            'chat_id' => $chatId,
            'document' => $document,
            'caption' => $caption,
            'parse_mode' => $caption !== null ? $parseMode : null,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a video.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendVideo(
        string $chatId,
        string $video,
        ?string $caption = null,
        ?string $parseMode = 'HTML',
        array $options = [],
    ): array {
        return $this->bot()->call('sendVideo', array_merge(array_filter([
            'chat_id' => $chatId,
            'video' => $video,
            'caption' => $caption,
            'parse_mode' => $caption !== null ? $parseMode : null,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Send an audio file.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendAudio(
        string $chatId,
        string $audio,
        ?string $caption = null,
        ?string $parseMode = 'HTML',
        array $options = [],
    ): array {
        return $this->bot()->call('sendAudio', array_merge(array_filter([
            'chat_id' => $chatId,
            'audio' => $audio,
            'caption' => $caption,
            'parse_mode' => $caption !== null ? $parseMode : null,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a voice message.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendVoice(
        string $chatId,
        string $voice,
        ?string $caption = null,
        ?string $parseMode = 'HTML',
        array $options = [],
    ): array {
        return $this->bot()->call('sendVoice', array_merge(array_filter([
            'chat_id' => $chatId,
            'voice' => $voice,
            'caption' => $caption,
            'parse_mode' => $caption !== null ? $parseMode : null,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Send an animation (GIF).
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendAnimation(
        string $chatId,
        string $animation,
        ?string $caption = null,
        ?string $parseMode = 'HTML',
        array $options = [],
    ): array {
        return $this->bot()->call('sendAnimation', array_merge(array_filter([
            'chat_id' => $chatId,
            'animation' => $animation,
            'caption' => $caption,
            'parse_mode' => $caption !== null ? $parseMode : null,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a sticker.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendSticker(
        string $chatId,
        string $sticker,
        array $options = [],
    ): array {
        return $this->bot()->call('sendSticker', array_merge([
            'chat_id' => $chatId,
            'sticker' => $sticker,
        ], $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a video note.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendVideoNote(
        string $chatId,
        string $videoNote,
        array $options = [],
    ): array {
        return $this->bot()->call('sendVideoNote', array_merge([
            'chat_id' => $chatId,
            'video_note' => $videoNote,
        ], $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a location.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendLocation(
        string $chatId,
        float $latitude,
        float $longitude,
        array $options = [],
    ): array {
        return $this->bot()->call('sendLocation', array_merge([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a venue.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendVenue(
        string $chatId,
        float $latitude,
        float $longitude,
        string $title,
        string $address,
        array $options = [],
    ): array {
        return $this->bot()->call('sendVenue', array_merge([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'title' => $title,
            'address' => $address,
        ], $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a contact.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendContact(
        string $chatId,
        string $phoneNumber,
        string $firstName,
        ?string $lastName = null,
        array $options = [],
    ): array {
        return $this->bot()->call('sendContact', array_merge(array_filter([
            'chat_id' => $chatId,
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a poll.
     *
     * @param  array<int, string>  $pollOptions
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendPoll(
        string $chatId,
        string $question,
        array $pollOptions,
        array $options = [],
    ): array {
        return $this->bot()->call('sendPoll', array_merge([
            'chat_id' => $chatId,
            'question' => $question,
            'options' => $pollOptions,
        ], $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a dice.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendDice(
        string $chatId,
        ?string $emoji = null,
        array $options = [],
    ): array {
        return $this->bot()->call('sendDice', array_merge(array_filter([
            'chat_id' => $chatId,
            'emoji' => $emoji,
        ]), $this->encodeReplyMarkup($options)));
    }

    /**
     * Send a group of media.
     *
     * @param  array<int, array<string, mixed>>  $media
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function sendMediaGroup(
        string $chatId,
        array $media,
        array $options = [],
    ): array {
        return $this->bot()->call('sendMediaGroup', array_merge([
            'chat_id' => $chatId,
            'media' => $media,
        ], $options));
    }

    // -----------------------------------------------------------------------
    // Callback & Inline methods
    // -----------------------------------------------------------------------

    /**
     * Answer a callback query.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function answerCallbackQuery(
        string $callbackQueryId,
        ?string $text = null,
        bool $showAlert = false,
        array $options = [],
    ): array {
        return $this->bot()->call('answerCallbackQuery', array_merge(array_filter([
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert ?: null,
        ]), $options));
    }

    /**
     * Answer an inline query.
     *
     * @param  array<int, array<string, mixed>>  $results
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function answerInlineQuery(
        string $inlineQueryId,
        array $results,
        array $options = [],
    ): array {
        return $this->bot()->call('answerInlineQuery', array_merge([
            'inline_query_id' => $inlineQueryId,
            'results' => $results,
        ], $options));
    }

    // -----------------------------------------------------------------------
    // Moderation methods
    // -----------------------------------------------------------------------

    /**
     * Ban a chat member.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function banChatMember(
        string $chatId,
        int|string $userId,
        array $options = [],
    ): array {
        return $this->bot()->call('banChatMember', array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options));
    }

    /**
     * Unban a chat member.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function unbanChatMember(
        string $chatId,
        int|string $userId,
        array $options = [],
    ): array {
        return $this->bot()->call('unbanChatMember', array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options));
    }

    // -----------------------------------------------------------------------
    // Chat action shortcuts
    // -----------------------------------------------------------------------

    /**
     * Send typing action.
     *
     * @return array<string, mixed>
     */
    public function typing(string $chatId): array
    {
        return $this->sendChatAction($chatId, ChatAction::Typing);
    }

    /**
     * Send uploading photo action.
     *
     * @return array<string, mixed>
     */
    public function uploadingPhoto(string $chatId): array
    {
        return $this->sendChatAction($chatId, ChatAction::UploadPhoto);
    }

    /**
     * Send uploading document action.
     *
     * @return array<string, mixed>
     */
    public function uploadingDocument(string $chatId): array
    {
        return $this->sendChatAction($chatId, ChatAction::UploadDocument);
    }

    /**
     * Send recording video action.
     *
     * @return array<string, mixed>
     */
    public function recordingVideo(string $chatId): array
    {
        return $this->sendChatAction($chatId, ChatAction::RecordVideo);
    }

    /**
     * Send recording voice action.
     *
     * @return array<string, mixed>
     */
    public function recordingVoice(string $chatId): array
    {
        return $this->sendChatAction($chatId, ChatAction::RecordVoice);
    }

    // -----------------------------------------------------------------------
    // Fluent builders
    // -----------------------------------------------------------------------

    /**
     * Create a fluent message builder.
     */
    public function message(string $chatId): PendingMessage
    {
        return new PendingMessage($this, $chatId);
    }

    /**
     * Create a fluent broadcast builder.
     *
     * @param  array<int, string>  $chatIds
     */
    public function broadcast(array $chatIds = []): PendingBroadcast
    {
        return new PendingBroadcast($this, $chatIds);
    }

    // -----------------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------------

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

    // -----------------------------------------------------------------------
    // Reply markup encoding
    // -----------------------------------------------------------------------

    /**
     * Auto-encode reply_markup if it's a ReplyMarkupInterface or array.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function encodeReplyMarkup(array $options): array
    {
        if (isset($options['reply_markup'])) {
            $markup = $options['reply_markup'];

            if ($markup instanceof ReplyMarkupInterface) {
                $options['reply_markup'] = json_encode($markup->toArray());
            } elseif (is_array($markup)) {
                $options['reply_markup'] = json_encode($markup);
            }
        }

        return $options;
    }
}
