<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;
use Zorvex\Drivers\Panels\PanelFactory;
use Exception;

class TestAccountController
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

    public function claim(Request $request): void
    {
        $chatId = $request->getChatId();
        $userId = $this->user['id'];

        // Check if trial feature is enabled
        $trialEnabled = (bool)($this->db->selectOne("SELECT key_value FROM settings WHERE key_name = 'trial_enabled'")['key_value'] ?? '1');
        if (!$trialEnabled) {
            $this->bot->sendMessage($chatId, "⚠️ دریافت اکانت تست در حال حاضر غیرفعال می‌باشد.");
            return;
        }

        // Anti-abuse check: has user already claimed?
        if (!empty($this->user['test_service_claimed'])) {
            $this->bot->sendMessage($chatId, "⚠️ شما پیش از این یک‌بار اشتراک تست رایگان را دریافت نموده‌اید.\nجهت تداوم اتصال، لطفاً از بخش <b>🛒 خرید سرویس جدید</b> اشتراک خود را ارتقا دهید.");
            return;
        }

        $panel = $this->db->selectOne("SELECT * FROM panels WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
        if (!$panel) {
            $this->bot->sendMessage($chatId, "❌ در حال حاضر سرور آزمایشی در دسترس نیست. لطفاً دقایقی دیگر تلاش کنید.");
            return;
        }

        $trafficGb = (int)($this->db->selectOne("SELECT key_value FROM settings WHERE key_name = 'trial_traffic_gb'")['key_value'] ?? 2);
        $days = (int)($this->db->selectOne("SELECT key_value FROM settings WHERE key_name = 'trial_days'")['key_value'] ?? 1);

        $trafficBytes = $trafficGb * 1024 * 1024 * 1024;
        $expireTimestamp = time() + ($days * 86400);
        $serviceUsername = 'test_' . $userId . '_' . random_str(4);
        $subId = bin2hex(random_bytes(10));

        try {
            $driver = PanelFactory::create($panel);
            $result = $driver->createUser($serviceUsername, $trafficBytes, $expireTimestamp);

            if (!$result['success']) {
                $this->bot->sendMessage($chatId, "خطا در برقراری ارتباط با سرور تست: " . ($result['error'] ?? ''));
                return;
            }

            $appUrl = rtrim((string)zorvex_config('app.url'), '/');
            $smartSubUrl = "{$appUrl}/sub.php?token={$subId}";

            $now = time();
            $this->db->insert('services', [
                'user_id' => $userId,
                'product_id' => null,
                'panel_id' => $panel['id'],
                'service_username' => $serviceUsername,
                'sub_id' => $subId,
                'traffic_total_bytes' => $trafficBytes,
                'traffic_used_bytes' => 0,
                'expire_date' => $expireTimestamp,
                'status' => 'active',
                'subscription_url' => $smartSubUrl,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Mark user as having claimed trial
            $this->db->update('users', ['test_service_claimed' => 1], 'id = :id', ['id' => $userId]);

            $card = Theme::serviceCard(
                'اکانت تست رایگان Zorvex',
                'active',
                0,
                $trafficBytes,
                $expireTimestamp,
                $smartSubUrl
            );

            $msg = "🎁 <b>اکانت تست رایگان شما با موفقیت فعال شد!</b>\n\n"
                 . $card . "\n\n"
                 . "📱 <b>راهنمای اتصال:</b> لینک بالا را در اپلیکیشن v2rayNG / Streisand وارد کرده و کیفیت را تست فرمایید.";

            $this->bot->sendMessage($chatId, $msg);
        } catch (Exception $e) {
            $this->bot->sendMessage($chatId, "خطای سیستمی: " . $e->getMessage());
        }
    }
}
