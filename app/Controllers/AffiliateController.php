<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;

class AffiliateController
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
        $userId = $this->user['id'];

        $botUsername = (string)zorvex_config('telegram.bot_username');
        $referralLink = "https://t.me/{$botUsername}?start=ref_{$userId}";

        $referralsCount = $this->db->selectOne(
            "SELECT COUNT(*) as cnt FROM users WHERE invited_by = :uid",
            ['uid' => $userId]
        )['cnt'] ?? 0;

        $totalEarned = $this->db->selectOne(
            "SELECT SUM(amount) as total FROM transactions WHERE user_id = :uid AND type = 'commission'",
            ['uid' => $userId]
        )['total'] ?? 0;

        $affiliatePct = (int)($this->db->selectOne("SELECT key_value FROM settings WHERE key_name = 'affiliate_percent'")['key_value'] ?? 15);

        $card = Theme::card('سامانه همکاری در فروش Zorvex', [
            'درصد سود شما' => "{$affiliatePct}٪ از هر خرید",
            'تعداد زیرمجموعه‌ها' => "{$referralsCount} کاربر",
            'کل درآمد کسب‌شده' => format_price((int)$totalEarned),
            'موجودی قابل برداشت/خرید' => format_price($this->user['balance'] ?? 0),
        ], '👥');

        $text = $card . "\n\n"
              . "🔗 <b>لینک اختصاصی دعوت شما:</b>\n"
              . "<code>{$referralLink}</code>\n\n"
              . "💡 با ارسال این لینک به دوستان یا کانال‌های خود، با هر بار خرید یا تمدید آن‌ها، <b>{$affiliatePct} درصد</b> به صورت آنی به کیف پول شما افزوده می‌شود!";

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '📤 اشتراک‌گذاری سریع لینک', 'url' => "https://t.me/share/url?url=" . urlencode($referralLink) . "&text=" . urlencode("خرید فیلترشکن پرسرعت و بدون قطعی Zorvex")],
                ]
            ]
        ];

        $this->bot->sendMessage($chatId, $text, $buttons);
    }
}
