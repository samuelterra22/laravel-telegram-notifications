<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Facades;

use Illuminate\Support\Facades\Facade;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Enums\ChatAction;
use SamuelTerra22\TelegramNotifications\Fluent\PendingBroadcast;
use SamuelTerra22\TelegramNotifications\Fluent\PendingMessage;

/**
 * @method static TelegramBotApi bot(?string $name = null)
 * @method static array sendMessage(string $chatId, string $text, ?string $parseMode = 'HTML', ?string $topicId = null, array $options = [])
 * @method static array sendChatAction(string $chatId, ChatAction $action, array $options = [])
 * @method static array editMessageText(string $chatId, int|string $messageId, string $text, ?string $parseMode = 'HTML', array $options = [])
 * @method static array editMessageCaption(string $chatId, int|string $messageId, string $caption, ?string $parseMode = 'HTML', array $options = [])
 * @method static array editMessageReplyMarkup(string $chatId, int|string $messageId, array $replyMarkup, array $options = [])
 * @method static array editMessageMedia(string $chatId, int|string $messageId, array $media, array $options = [])
 * @method static array deleteMessage(string $chatId, int|string $messageId, array $options = [])
 * @method static array deleteMessages(string $chatId, array $messageIds, array $options = [])
 * @method static array forwardMessage(string $chatId, string $fromChatId, int|string $messageId, array $options = [])
 * @method static array copyMessage(string $chatId, string $fromChatId, int|string $messageId, array $options = [])
 * @method static array getMe()
 * @method static array setWebhook(string $url, ?string $secretToken = null, array $options = [])
 * @method static array deleteWebhook(bool $dropPendingUpdates = false, array $options = [])
 * @method static array getWebhookInfo()
 * @method static array getChat(string $chatId, array $options = [])
 * @method static array getChatMember(string $chatId, int|string $userId, array $options = [])
 * @method static array getChatMemberCount(string $chatId, array $options = [])
 * @method static array pinChatMessage(string $chatId, int|string $messageId, bool $disableNotification = false, array $options = [])
 * @method static array unpinChatMessage(string $chatId, ?int $messageId = null, array $options = [])
 * @method static array unpinAllChatMessages(string $chatId, array $options = [])
 * @method static array getFile(string $fileId, array $options = [])
 * @method static array setMyCommands(array $commands, array $options = [])
 * @method static array deleteMyCommands(array $options = [])
 * @method static array getMyCommands(array $options = [])
 * @method static array sendPhoto(string $chatId, string $photo, ?string $caption = null, ?string $parseMode = 'HTML', array $options = [])
 * @method static array sendDocument(string $chatId, string $document, ?string $caption = null, ?string $parseMode = 'HTML', array $options = [])
 * @method static array sendVideo(string $chatId, string $video, ?string $caption = null, ?string $parseMode = 'HTML', array $options = [])
 * @method static array sendAudio(string $chatId, string $audio, ?string $caption = null, ?string $parseMode = 'HTML', array $options = [])
 * @method static array sendVoice(string $chatId, string $voice, ?string $caption = null, ?string $parseMode = 'HTML', array $options = [])
 * @method static array sendAnimation(string $chatId, string $animation, ?string $caption = null, ?string $parseMode = 'HTML', array $options = [])
 * @method static array sendSticker(string $chatId, string $sticker, array $options = [])
 * @method static array sendVideoNote(string $chatId, string $videoNote, array $options = [])
 * @method static array sendLocation(string $chatId, float $latitude, float $longitude, array $options = [])
 * @method static array sendVenue(string $chatId, float $latitude, float $longitude, string $title, string $address, array $options = [])
 * @method static array sendContact(string $chatId, string $phoneNumber, string $firstName, ?string $lastName = null, array $options = [])
 * @method static array sendPoll(string $chatId, string $question, array $pollOptions, array $options = [])
 * @method static array sendDice(string $chatId, ?string $emoji = null, array $options = [])
 * @method static array sendMediaGroup(string $chatId, array $media, array $options = [])
 * @method static array answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false, array $options = [])
 * @method static array answerInlineQuery(string $inlineQueryId, array $results, array $options = [])
 * @method static array banChatMember(string $chatId, int|string $userId, array $options = [])
 * @method static array unbanChatMember(string $chatId, int|string $userId, array $options = [])
 * @method static array typing(string $chatId)
 * @method static array uploadingPhoto(string $chatId)
 * @method static array uploadingDocument(string $chatId)
 * @method static array recordingVideo(string $chatId)
 * @method static array recordingVoice(string $chatId)
 * @method static PendingMessage message(string $chatId)
 * @method static PendingBroadcast broadcast(array $chatIds = [])
 * @method static string getDefaultBot()
 * @method static array getBotsConfig()
 *
 * @see \SamuelTerra22\TelegramNotifications\Telegram
 */
class Telegram extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SamuelTerra22\TelegramNotifications\Telegram::class;
    }
}
