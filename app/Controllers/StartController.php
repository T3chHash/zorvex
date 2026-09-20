<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;

class StartController
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

        $activeServicesCount = $this->db->selectOne(
            "SELECT COUNT(*) as cnt FROM services WHERE user_id = :uid AND status = 'active'",
            ['uid' => $userId]
        )['cnt'] ?? 0;

        $referralsCount = $this->db->selectOne(
            "SELECT COUNT(*) as cnt FROM users WHERE invited_by = :uid",
            ['uid' => $userId]
        )['cnt'] ?? 0;

        $balanceFormatted = format_price($this->user['balance'] ?? 0);
        $name = e_html($this->user['first_name'] ?? 'کاربر گرامی');

        $welcomeCard = Theme::card('پنل کاربری Zorvex Pro', [
            'شناسه کاربری' => $userId,
            'نام کاربری' => $this->user['username'] ? '@' . $this->user['username'] : 'ثبت نشده',
            'موجودی کیف پول' => $balanceFormatted,
            'سرویس‌های فعال' => "{$activeServicesCount} سرویس",
            'تعداد زیرمجموعه‌ها' => "{$referralsCount} نفر",
        ], '👤');

        $welcomeText = "سلام <b>{$name}</b> عزیز! 🌟\n"
                     . "به ربات هوشمند مدیریت و خرید سرویس‌های اینترنت بین‌الملل <b>Zorvex</b> خوش آمدید.\n\n"
                     . "⚡ سریع، ایمن و همیشه متصل با جدیدترین پروتکل‌های روز دنیا (VLESS, VMess, Reality, WireGuard).\n\n"
                     . $welcomeCard . "\n\n"
                     . "👇 لطفاً یکی از گزینه‌های زیر را انتخاب نمایید:";

        $isAdmin = (bool)($this->user['is_admin'] ?? false);
        $markup = Theme::mainKeyboard($isAdmin);

        $this->bot->sendMessage($chatId, $welcomeText, $markup);
    }

    public function help(Request $request): void
    {
        $chatId = $request->getChatId();
        $helpText = "📖 <b>راهنمای اتصال سریع و جامع Zorvex</b>\n\n"
            . "📱 <b>اندروید (Android):</b>\n"
            . "1️⃣ نرم‌افزار <b>v2rayNG</b> یا <b>Sing-box</b> یا <b>Happ</b> را از گوگل‌پلی نصب کنید.\n"
            . "2️⃣ وارد بخش <b>🛡️ سرویس‌های من</b> شده و لینک اشتراک هوشمند را کپی کنید.\n"
            . "3️⃣ در برنامه روی دکمه <b>+</b> لمس کرده و گزینه <code>Import from Clipboard</code> را بزنید.\n\n"
            . "🍏 <b>آیفون / آیپد (iOS):</b>\n"
            . "1️⃣ نرم‌افزار <b>Streisand</b>، <b>V2Box</b> یا <b>Sing-box</b> را از App Store دریافت کنید.\n"
            . "2️⃣ لینک اشتراک را در نرم‌افزار Paste و به‌روزرسانی (Update Subscription) نمایید.\n\n"
            . "💻 <b>ویندوز (Windows):</b>\n"
            . "1️⃣ از نرم‌افزار <b>v2rayN</b>، <b>NekoBox</b> یا <b>Hiddify Next</b> استفاده کنید.\n\n"
            . "💡 <i>لینک اشتراک Zorvex هوشمند بوده و همواره جدیدترین سرورهای بدون قطعی را دریافت می‌کند.</i>";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📥 دانلود v2rayNG (اندروید)', 'url' => 'https://play.google.com/store/apps/details?id=com.v2ray.ang'],
                    ['text' => '📥 دانلود Streisand (iOS)', 'url' => 'https://apps.apple.com/app/streisand/id6450534064'],
                ],
                [
                    ['text' => '🌐 وب‌سایت آموزش و ابزارها', 'url' => (string)zorvex_config('app.url')],
                ]
            ]
        ];

        $this->bot->sendMessage($chatId, $helpText, $keyboard);
    }

    public function fallback(Request $request): void
    {
        $chatId = $request->getChatId();
        $this->bot->sendMessage($chatId, "دستور وارد شده نامعتبر است. لطفاً از دکمه‌های منوی زیر استفاده فرمایید.");
    }
}
