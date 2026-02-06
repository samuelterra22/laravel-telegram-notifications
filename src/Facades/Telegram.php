<?php

declare(strict_types=1);

namespace SamuelTerra22\TelegramNotifications\Facades;

use Illuminate\Support\Facades\Facade;
use SamuelTerra22\TelegramNotifications\Api\TelegramBotApi;
use SamuelTerra22\TelegramNotifications\Enums\ChatAction;

/**
 * @method static TelegramBotApi bot(?string $name = null)
 * @method static array sendMessage(string $chatId, string $text, ?string $parseMode = 'HTML', ?string $topicId = null)
 * @method static array sendChatAction(string $chatId, ChatAction $action)
 * @method static array editMessageText(string $chatId, int|string $messageId, string $text, ?string $parseMode = 'HTML')
 * @method static array editMessageCaption(string $chatId, int|string $messageId, string $caption, ?string $parseMode = 'HTML')
 * @method static array editMessageReplyMarkup(string $chatId, int|string $messageId, array $replyMarkup)
 * @method static array deleteMessage(string $chatId, int|string $messageId)
 * @method static array deleteMessages(string $chatId, array $messageIds)
 * @method static array forwardMessage(string $chatId, string $fromChatId, int|string $messageId)
 * @method static array copyMessage(string $chatId, string $fromChatId, int|string $messageId)
 * @method static array getMe()
 * @method static array setWebhook(string $url, ?string $secretToken = null)
 * @method static array deleteWebhook(bool $dropPendingUpdates = false)
 * @method static array getWebhookInfo()
 * @method static array getChat(string $chatId)
 * @method static array getChatMember(string $chatId, int|string $userId)
 * @method static array getChatMemberCount(string $chatId)
 * @method static array pinChatMessage(string $chatId, int|string $messageId, bool $disableNotification = false)
 * @method static array unpinChatMessage(string $chatId, ?int $messageId = null)
 * @method static array unpinAllChatMessages(string $chatId)
 * @method static array getFile(string $fileId)
 * @method static array setMyCommands(array $commands)
 * @method static array deleteMyCommands()
 * @method static array getMyCommands()
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
