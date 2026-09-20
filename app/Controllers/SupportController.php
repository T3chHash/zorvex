<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;

class SupportController
{
    private TelegramBot $bot;
    private array $user;
    private Database $db;

    public function __construct(TelegramBot $bot, array $user)
    {
        $this->bot = $bot;
        $this->user = $user;
        $this->db = Database::getInstance();
    }

    public function index(Request $request): void
    {
        $chatId = $request->getChatId();
        $supportText = (string)($this->db->selectOne("SELECT key_value FROM settings WHERE key_name = 'support_text'")['key_value'] ?? 'پشتیبانی ۲۴ ساعته Zorvex');

        $text = "🎧 <b>مرکز پشتیبانی و ارتباط با کارشناسان Zorvex</b>\n\n"
              . "{$supportText}\n\n"
              . "جهت ارسال پیام و ثبت تیکت پشتیبانی، روی دکمه زیر کلیک نمایید:";

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '✍️ ارسال پیام به پشتیبانی', 'callback_data' => 'support:new_ticket'],
                ],
            ]
        ];

        $this->bot->sendMessage($chatId, $text, $buttons);
    }

    public function handleTicketStep(Request $request): bool
    {
        $chatId = $request->getChatId();
        $userId = $this->user['id'];
        $msgText = $request->getText() ?? '';

        if (empty($msgText)) {
            $this->bot->sendMessage($chatId, "لطفاً متن پیام خود را ارسال فرمایید.");
            return true;
        }

        $now = time();
        $ticketId = $this->db->insert('tickets', [
            'user_id' => $userId,
            'subject' => mb_substr($msgText, 0, 50) . '...',
            'status' => 'open',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->db->insert('ticket_messages', [
            'ticket_id' => $ticketId,
            'sender_type' => 'user',
            'sender_id' => $userId,
            'message' => $msgText,
            'file_id' => $request->getPhotoFileId(),
            'created_at' => $now,
        ]);

        $this->db->update('users', ['step' => 'none', 'step_data' => null], 'id = :id', ['id' => $userId]);

        $this->bot->sendMessage(
            $chatId,
            "✅ <b>پیام شما دریافت شد!</b>\n\nتیکت شماره <code>#{$ticketId}</code> ایجاد شد. کارشناسان ما به زودی پاسخ را در همین ربات برای شما ارسال خواهند کرد.",
            Theme::mainKeyboard((bool)$this->user['is_admin'])
        );

        // Notify Admins
        $adminList = zorvex_config('telegram.admins', []);
        $logChannel = (string)zorvex_config('telegram.log_channel');
        $target = !empty($logChannel) ? $logChannel : ($adminList[0] ?? null);

        if ($target) {
            $userDisplay = $this->user['username'] ? '@' . $this->user['username'] : $this->user['first_name'];
            $adminMsg = "📩 <b>تیکت پشتیبانی جدید (#{$ticketId})</b>\n\n"
                      . "▫️ <b>کاربر:</b> {$userDisplay} (<code>{$userId}</code>)\n"
                      . "▫️ <b>متن پیام:</b>\n" . e_html($msgText);

            $this->bot->sendMessage($target, $adminMsg, [
                'inline_keyboard' => [
                    [['text' => '✍️ پاسخ به تیکت', 'callback_data' => "admin:reply_ticket:{$ticketId}"]],
                ]
            ]);
        }

        return true;
    }
}
