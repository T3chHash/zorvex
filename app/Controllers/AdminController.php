<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;
use Zorvex\Drivers\Panels\PanelFactory;
use Exception;

class AdminController
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

    private function checkAccess(): bool
    {
        return !empty($this->user['is_admin']);
    }

    public function dashboard(Request $request): void
    {
        if (!$this->checkAccess()) {
            return;
        }

        $chatId = $request->getChatId();

        $totalUsers = $this->db->selectOne("SELECT COUNT(*) as c FROM users")['c'] ?? 0;
        $activeServices = $this->db->selectOne("SELECT COUNT(*) as c FROM services WHERE status = 'active'")['c'] ?? 0;
        $pendingOrders = $this->db->selectOne("SELECT COUNT(*) as c FROM orders WHERE status = 'waiting_approval'")['c'] ?? 0;
        $totalSales = $this->db->selectOne("SELECT SUM(final_amount) as s FROM orders WHERE status = 'paid'")['s'] ?? 0;

        $statsCard = Theme::card('داشبورد مدیریت پیشرفته Zorvex', [
            'تعداد کل کاربران' => "{$totalUsers} نفر",
            'سرویس‌های فعال' => "{$activeServices} اکانت",
            'سفارشات در انتظار بررسی' => "{$pendingOrders} فیش",
            'مجموع فروش موفق' => format_price((int)$totalSales),
            'نسخه سیستم' => (string)zorvex_config('app.version'),
        ], '⚙️');

        $webPanelUrl = rtrim((string)zorvex_config('app.url'), '/') . '/admin/index.php';

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => "📥 بررسی فیش‌ها ({$pendingOrders})", 'callback_data' => 'admin:pending_orders'],
                    ['text' => '🖥️ وضعیت سرورها و پنل‌ها', 'callback_data' => 'admin:panels_status'],
                ],
                [
                    ['text' => '📢 ارسال پیام همگانی (Broadcast)', 'callback_data' => 'admin:broadcast'],
                    ['text' => '➕ افزودن کارت بانکی', 'callback_data' => 'admin:add_card'],
                ],
                [
                    ['text' => '🌐 ورود به پنل وب ادمین', 'url' => $webPanelUrl],
                ]
            ]
        ];

        $this->bot->sendMessage($chatId, $statsCard, $buttons);
    }

    public function handleCallback(Request $request, array $parts): void
    {
        if (!$this->checkAccess()) {
            return;
        }

        $action = $parts[1] ?? '';
        $chatId = $request->getChatId();
        $messageId = $request->getMessageId();

        if ($action === 'approve_order') {
            $orderId = (int)($parts[2] ?? 0);
            $order = $this->db->selectOne("SELECT * FROM orders WHERE id = :id", ['id' => $orderId]);

            if (!$order || $order['status'] === 'paid') {
                $this->bot->answerCallbackQuery($request->getCallbackQueryId(), 'این سفارش قبلاً تأیید یا لغو شده است.', true);
                return;
            }

            $this->db->update('orders', [
                'status' => 'paid',
                'updated_at' => time(),
            ], 'id = :id', ['id' => $orderId]);

            // Provision Service
            $shopCtrl = new ShopController($this->bot, $this->user);
            $shopCtrl->provisionService($order);

            $adminName = e_html($this->user['first_name'] ?? 'ادمین');
            $this->bot->answerCallbackQuery($request->getCallbackQueryId(), 'سفارش با موفقیت تأیید شد و اکانت ساخته شد.');

            $updatedText = "✅ <b>سفارش #{$order['order_code']} تأیید و اکانت صادر گردید.</b>\n"
                         . "▫️ تأییدکننده: {$adminName}\n"
                         . "▫️ تاریخ: " . jalali_now();

            $this->bot->editMessageText($chatId, $messageId, $updatedText);
        } elseif ($action === 'reject_order') {
            $orderId = (int)($parts[2] ?? 0);
            $order = $this->db->selectOne("SELECT * FROM orders WHERE id = :id", ['id' => $orderId]);

            if (!$order) {
                return;
            }

            $this->db->update('orders', [
                'status' => 'rejected',
                'updated_at' => time(),
            ], 'id = :id', ['id' => $orderId]);

            $this->bot->sendMessage(
                $order['user_id'],
                "❌ <b>رسید پرداخت شما برای سفارش #{$order['order_code']} تأیید نشد.</b>\n\nعلت: عدم تطابق مشخصات یا واریز نشدن مبلغ.\nجهت بررسی بیشتر می‌توانید به بخش پشتیبانی پیام ارسال نمایید."
            );

            $this->bot->answerCallbackQuery($request->getCallbackQueryId(), 'سفارش رد شد.');
            $this->bot->editMessageText($chatId, $messageId, "❌ سفارش #{$order['order_code']} توسط ادمین رد شد.");
        } elseif ($action === 'panels_status') {
            $panels = $this->db->select("SELECT * FROM panels");
            $report = "🖥️ <b>وضعیت اتصال به پنل‌های VPN:</b>\n\n";

            foreach ($panels as $p) {
                $status = '🔴 قطع / خطا';
                try {
                    $driver = PanelFactory::create($p);
                    if ($driver->checkHealth()) {
                        $status = '🟢 متصل و فعال';
                    }
                } catch (Exception $e) {
                    $status = '🔴 خطا: ' . $e->getMessage();
                }
                $report .= "▫️ <b>{$p['name']}</b> ({$p['type']}): {$status}\n";
            }

            $this->bot->sendMessage($chatId, $report);
        } elseif ($action === 'broadcast') {
            $this->db->update('users', ['step' => 'admin:broadcast'], 'id = :id', ['id' => $this->user['id']]);
            $this->bot->sendMessage($chatId, "📢 لطفاً پیامی که قصد ارسال آن به تمام کاربران دارید را ارسال فرمایید (متن، عکس، فرمت HTML):", [
                'keyboard' => [[['text' => '🔙 انصراف و بازگشت به منوی اصلی']]],
                'resize_keyboard' => true,
            ]);
        }
    }

    public function handleAdminStep(Request $request, string $step): bool
    {
        if (!$this->checkAccess()) {
            return false;
        }

        $chatId = $request->getChatId();

        if ($step === 'admin:broadcast') {
            $broadcastText = $request->getText() ?? '';
            $photoId = $request->getPhotoFileId();

            $users = $this->db->select("SELECT id FROM users WHERE status = 'active'");
            $count = 0;

            foreach ($users as $u) {
                if ($photoId) {
                    $this->bot->sendPhoto($u['id'], $photoId, $broadcastText);
                } else {
                    $this->bot->sendMessage($u['id'], $broadcastText);
                }
                $count++;
                usleep(50000); // 50ms delay to prevent Telegram flood limits
            }

            $this->db->update('users', ['step' => 'none'], 'id = :id', ['id' => $this->user['id']]);
            $this->bot->sendMessage($chatId, "✅ پیام همگانی با موفقیت برای <b>{$count} کاربر</b> ارسال شد.", Theme::mainKeyboard(true));
            return true;
        }

        return false;
    }
}
