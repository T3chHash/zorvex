<?php
declare(strict_types=1);

/**
 * Zorvex Autonomous Scheduled Task Runner
 * Run via crontab: * * * * * php /var/www/zorvex/cron/scheduler.php >> /var/log/zorvex_cron.log 2>&1
 */

date_default_timezone_set('Asia/Tehran');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once dirname(__DIR__) . '/app/autoload.php';

use Zorvex\Core\Database;
use Zorvex\Core\TelegramBot;
use Zorvex\Drivers\Panels\PanelFactory;

$db = Database::getInstance();
$bot = new TelegramBot();
$now = time();

echo "[" . date('Y-m-d H:i:s') . "] Starting Zorvex Cron...\n";

// 1. Expire Finished Services
$expiredServices = $db->select(
    "SELECT * FROM services WHERE status = 'active' AND (expire_date > 0 AND expire_date <= :now)",
    ['now' => $now]
);

foreach ($expiredServices as $svc) {
    $db->update('services', ['status' => 'expired', 'updated_at' => $now], 'id = :id', ['id' => $svc['id']]);

    $text = "⚠️ <b>هشدار انقضای اشتراک Zorvex</b>\n\n"
          . "سرویس شما با نام <code>{$svc['service_username']}</code> منقضی گردید.\n"
          . "جهت تمدید یا خرید اشتراک جدید، لطفاً وارد ربات شوید.";

    $buttons = [
        'inline_keyboard' => [
            [['text' => '🔄 تمدید اشتراک', 'callback_data' => "svc:renew:{$svc['id']}"]],
            [['text' => '🛒 خرید پلن جدید', 'callback_data' => 'shop:back_cats']],
        ]
    ];

    $bot->sendMessage($svc['user_id'], $text, $buttons);
    echo "Service #{$svc['id']} expired.\n";
}

// 2. Sync Traffic and Send 85% Warning
$activeServices = $db->select("SELECT * FROM services WHERE status = 'active' LIMIT 100");
foreach ($activeServices as $svc) {
    $total = (int)$svc['traffic_total_bytes'];
    $used = (int)$svc['traffic_used_bytes'];

    if ($total > 0 && ($used / $total) >= 0.85 && ($used / $total) < 1.0) {
        // Send alert if not warned in last 24h
        $warningKey = "warn_traffic_{$svc['id']}";
        $warned = $db->selectOne("SELECT key_value FROM settings WHERE key_name = :k", ['k' => $warningKey]);
        if (!$warned) {
            $remFormatted = format_bytes($total - $used);
            $warnMsg = "⚠️ <b>هشدار مصرف حجم اینترنت</b>\n\n"
                     . "بیش از ۸۵٪ از حجم ترافیک سرویس <code>{$svc['service_username']}</code> مصرف شده است!\n"
                     . "حجم باقی‌مانده: <b>{$remFormatted}</b>\n\n"
                     . "جهت جلوگیری از قطع اتصال، می‌توانید هم‌اکنون حجم اضافی تهیه کنید:";

            $bot->sendMessage($svc['user_id'], $warnMsg, [
                'inline_keyboard' => [
                    [['text' => '➕ خرید حجم اضافه', 'callback_data' => "svc:extra:{$svc['id']}"]]
                ]
            ]);

            $db->insert('settings', ['key_name' => $warningKey, 'key_value' => (string)$now]);
        }
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Zorvex Cron Finished Successfully.\n";
