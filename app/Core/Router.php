<?php
declare(strict_types=1);

namespace Zorvex\Core;

use Zorvex\Controllers\StartController;
use Zorvex\Controllers\ShopController;
use Zorvex\Controllers\ServiceController;
use Zorvex\Controllers\TestAccountController;
use Zorvex\Controllers\WalletController;
use Zorvex\Controllers\AffiliateController;
use Zorvex\Controllers\LotteryController;
use Zorvex\Controllers\SupportController;
use Zorvex\Controllers\AdminController;

class Router
{
    private TelegramBot $bot;
    private Database $db;

    public function __construct(TelegramBot $bot)
    {
        $this->bot = $bot;
        $this->db = Database::getInstance();
    }

    public function dispatch(Request $request): void
    {
        $userId = $request->getUserId();
        $chatId = $request->getChatId();

        if (!$userId || !$chatId) {
            return;
        }

        // Fetch or create user record
        $user = $this->resolveUser($request);

        // Check ban status
        if (($user['status'] ?? 'active') === 'banned') {
            $this->bot->sendMessage($chatId, '⛔ دسترسی شما به این ربات مسدود شده است.');
            return;
        }

        // Check forced channel membership
        if (!$this->checkForcedMembership($userId, $chatId, $request)) {
            return;
        }

        // 1. Handle Callback Queries
        if ($request->isCallbackQuery()) {
            $this->routeCallbackQuery($request, $user);
            return;
        }

        // 2. Handle Stateful Steps (Conversation state machine)
        if (!empty($user['step']) && $user['step'] !== 'none') {
            if ($request->getText() === '🔙 انصراف و بازگشت به منوی اصلی' || $request->getText() === '/start') {
                $this->db->update('users', ['step' => 'none', 'step_data' => null], 'id = :id', ['id' => $userId]);
                (new StartController($this->bot, $user))->index($request);
                return;
            }

            if ($this->routeStep($request, $user)) {
                return;
            }
        }

        // 3. Handle Text Commands and Buttons
        $text = $request->getText() ?? '';

        if (str_starts_with($text, '/start')) {
            $this->db->update('users', ['step' => 'none', 'step_data' => null], 'id = :id', ['id' => $userId]);
            (new StartController($this->bot, $user))->index($request);
            return;
        }

        match ($text) {
            '🛒 خرید سرویس جدید' => (new ShopController($this->bot, $user))->categories($request),
            '🛡️ سرویس‌های من' => (new ServiceController($this->bot, $user))->myServices($request),
            '🎁 دریافت تست رایگان' => (new TestAccountController($this->bot, $user))->claim($request),
            '💳 افزایش موجودی / کیف پول' => (new WalletController($this->bot, $user))->index($request),
            '👥 کسب درآمد (زیرمجموعه‌گیری)' => (new AffiliateController($this->bot, $user))->index($request),
            '🎡 گردونه شانس روزانه' => (new LotteryController($this->bot, $user))->spin($request),
            '📖 راهنمای اتصال' => (new StartController($this->bot, $user))->help($request),
            '🎧 پشتیبانی و تیکت' => (new SupportController($this->bot, $user))->index($request),
            '⚙️ مدیریت پیشرفته (ادمین)' => (new AdminController($this->bot, $user))->dashboard($request),
            default => (new StartController($this->bot, $user))->fallback($request),
        };
    }

    private function resolveUser(Request $request): array
    {
        $info = $request->getUserInfo();
        $userId = $info['id'];

        $user = $this->db->selectOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);

        if (!$user) {
            $refId = null;
            $text = $request->getText() ?? '';
            if (str_starts_with($text, '/start ref_')) {
                $candidateRef = (int)str_replace('/start ref_', '', $text);
                if ($candidateRef > 0 && $candidateRef !== $userId) {
                    $refUser = $this->db->selectOne("SELECT id FROM users WHERE id = :id", ['id' => $candidateRef]);
                    if ($refUser) {
                        $refId = $candidateRef;
                    }
                }
            }

            $now = time();
            $adminList = zorvex_config('telegram.admins', []);
            $isAdmin = in_array((string)$userId, array_map('strval', $adminList), true) ? 1 : 0;

            $this->db->insert('users', [
                'id' => $userId,
                'username' => $info['username'],
                'first_name' => $info['first_name'],
                'balance' => 0,
                'step' => 'none',
                'step_data' => null,
                'is_admin' => $isAdmin,
                'invited_by' => $refId,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $user = $this->db->selectOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
        } else {
            // Keep username and first name fresh
            if ($user['username'] !== $info['username'] || $user['first_name'] !== $info['first_name']) {
                $this->db->update('users', [
                    'username' => $info['username'],
                    'first_name' => $info['first_name'],
                    'updated_at' => time(),
                ], 'id = :id', ['id' => $userId]);
            }
        }

        return $user;
    }

    private function checkForcedMembership(int $userId, int $chatId, Request $request): bool
    {
        $channel = (string)zorvex_config('telegram.force_channel');
        if (empty($channel)) {
            return true;
        }

        $res = $this->bot->getChatMember($channel, $userId);
        $status = $res['result']['status'] ?? 'left';

        if (in_array($status, ['member', 'administrator', 'creator'])) {
            return true;
        }

        $channelClean = ltrim($channel, '@');
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📢 عضویت در کانال اطلاع‌رسانی', 'url' => "https://t.me/{$channelClean}"]],
                [['text' => '🔄 تأیید عضویت', 'callback_data' => 'cb:check_membership']],
            ]
        ];

        $msg = "⚠️ <b>عضویت اجباری</b>\n\n"
             . "برای استفاده از ربات، لطفاً ابتدا در کانال رسمی ما عضو شوید و سپس دکمه <b>تأیید عضویت</b> را لمس کنید.";

        if ($request->isCallbackQuery()) {
            $this->bot->answerCallbackQuery($request->getCallbackQueryId(), 'شما هنوز در کانال عضو نشده‌اید!', true);
        } else {
            $this->bot->sendMessage($chatId, $msg, $keyboard);
        }

        return false;
    }

    private function routeCallbackQuery(Request $request, array $user): void
    {
        $data = $request->getCallbackData() ?? '';
        $parts = explode(':', $data);
        $prefix = $parts[0] ?? '';
        $action = $parts[1] ?? '';

        if ($prefix === 'cb' && $action === 'check_membership') {
            $this->bot->answerCallbackQuery($request->getCallbackQueryId(), 'بررسی عضویت انجام شد.');
            (new StartController($this->bot, $user))->index($request);
            return;
        }

        // Shop callbacks
        if ($prefix === 'shop') {
            (new ShopController($this->bot, $user))->handleCallback($request, $parts);
            return;
        }

        // Service callbacks
        if ($prefix === 'svc') {
            (new ServiceController($this->bot, $user))->handleCallback($request, $parts);
            return;
        }

        // Wallet callbacks
        if ($prefix === 'wallet') {
            (new WalletController($this->bot, $user))->handleCallback($request, $parts);
            return;
        }

        // Admin callbacks
        if ($prefix === 'admin') {
            (new AdminController($this->bot, $user))->handleCallback($request, $parts);
            return;
        }

        $this->bot->answerCallbackQuery($request->getCallbackQueryId());
    }

    private function routeStep(Request $request, array $user): bool
    {
        $step = $user['step'];

        if (str_starts_with($step, 'receipt:')) {
            return (new ShopController($this->bot, $user))->handleReceiptUpload($request, $step);
        }

        if ($step === 'support:ticket_message') {
            return (new SupportController($this->bot, $user))->handleTicketStep($request);
        }

        if (str_starts_with($step, 'admin:')) {
            return (new AdminController($this->bot, $user))->handleAdminStep($request, $step);
        }

        return false;
    }
}
