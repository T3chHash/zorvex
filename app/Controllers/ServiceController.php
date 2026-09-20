<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;
use Zorvex\Drivers\Panels\PanelFactory;
use Exception;

class ServiceController
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

    public function myServices(Request $request): void
    {
        $chatId = $request->getChatId();
        $userId = $this->user['id'];

        $services = $this->db->select(
            "SELECT s.*, p.name as product_name FROM services s
             LEFT JOIN products p ON s.product_id = p.id
             WHERE s.user_id = :uid
             ORDER BY s.id DESC",
            ['uid' => $userId]
        );

        if (empty($services)) {
            $buttons = [
                'inline_keyboard' => [
                    [['text' => '🛒 خرید سرویس جدید', 'callback_data' => 'shop:back_cats']],
                    [['text' => '🎁 دریافت تست رایگان', 'callback_data' => 'cb:test_claim']],
                ]
            ];
            $this->bot->sendMessage($chatId, "⚠️ شما در حال حاضر هیچ سرویس فعالی ندارید.", $buttons);
            return;
        }

        $buttons = [];
        foreach ($services as $svc) {
            $name = $svc['product_name'] ?? $svc['service_username'];
            $statusIcon = $svc['status'] === 'active' ? '🟢' : '🔴';
            $trafficRemaining = max(0, $svc['traffic_total_bytes'] - $svc['traffic_used_bytes']);
            $remFormatted = format_bytes($trafficRemaining);

            $buttons[] = [
                ['text' => "{$statusIcon} {$name} (مانده: {$remFormatted})", 'callback_data' => "svc:view:{$svc['id']}"]
            ];
        }

        $text = "🛡️ <b>لیست سرویس‌های خریداری شده شما:</b>\n\n"
              . "برای مشاهده مشخصات، لینک اتصال، تمدید یا حجم باقی‌مانده، روی سرویس مورد نظر کلیک نمایید:";

        if ($request->isCallbackQuery()) {
            $this->bot->editMessageText($chatId, $request->getMessageId(), $text, ['inline_keyboard' => $buttons]);
        } else {
            $this->bot->sendMessage($chatId, $text, ['inline_keyboard' => $buttons]);
        }
    }

    public function showService(Request $request, int $serviceId): void
    {
        $chatId = $request->getChatId();
        $userId = $this->user['id'];

        $svc = $this->db->selectOne(
            "SELECT s.*, p.name as product_name, pan.id as panel_db_id FROM services s
             LEFT JOIN products p ON s.product_id = p.id
             LEFT JOIN panels pan ON s.panel_id = pan.id
             WHERE s.id = :id AND s.user_id = :uid",
            ['id' => $serviceId, 'uid' => $userId]
        );

        if (!$svc) {
            $this->bot->sendMessage($chatId, "سرویس یافت نشد.");
            return;
        }

        // Synchronize with live panel stats
        $this->syncServiceWithPanel($svc);
        // Refresh local record
        $svc = $this->db->selectOne("SELECT * FROM services WHERE id = :id", ['id' => $serviceId]);

        $appUrl = rtrim((string)zorvex_config('app.url'), '/');
        $smartSubUrl = "{$appUrl}/sub.php?token={$svc['sub_id']}";
        $webPageUrl = "{$appUrl}/sub_page.php?token={$svc['sub_id']}";

        $card = Theme::serviceCard(
            $svc['service_username'],
            $svc['status'],
            (int)$svc['traffic_used_bytes'],
            (int)$svc['traffic_total_bytes'],
            (int)$svc['expire_date'],
            $smartSubUrl
        );

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '🌐 صفحه وب اشتراک و دانلود کلاینت‌ها', 'url' => $webPageUrl],
                ],
                [
                    ['text' => '🔄 تمدید سرویس', 'callback_data' => "svc:renew:{$svc['id']}"],
                    ['text' => '➕ خرید حجم اضافه', 'callback_data' => "svc:extra:{$svc['id']}"],
                ],
                [
                    ['text' => '🔙 بازگشت به لیست سرویس‌ها', 'callback_data' => 'svc:list']
                ]
            ]
        ];

        if ($request->isCallbackQuery()) {
            $this->bot->editMessageText($chatId, $request->getMessageId(), $card, $buttons);
        } else {
            $this->bot->sendMessage($chatId, $card, $buttons);
        }
    }

    private function syncServiceWithPanel(array $svc): void
    {
        $panel = $this->db->selectOne("SELECT * FROM panels WHERE id = :id", ['id' => $svc['panel_id']]);
        if (!$panel) {
            return;
        }

        try {
            $driver = PanelFactory::create($panel);
            $panelUser = $driver->getUser($svc['service_username']);

            if ($panelUser) {
                $status = $panelUser['status'] === 'active' ? 'active' : 'expired';
                if ($panelUser['expire'] > 0 && $panelUser['expire'] < time()) {
                    $status = 'expired';
                }

                $this->db->update('services', [
                    'traffic_used_bytes' => $panelUser['used_traffic'],
                    'traffic_total_bytes' => $panelUser['data_limit'] ?: $svc['traffic_total_bytes'],
                    'expire_date' => $panelUser['expire'] ?: $svc['expire_date'],
                    'status' => $status,
                    'updated_at' => time(),
                ], 'id = :id', ['id' => $svc['id']]);
            }
        } catch (Exception $e) {
            error_log("Panel Sync Error: " . $e->getMessage());
        }
    }

    public function handleCallback(Request $request, array $parts): void
    {
        $action = $parts[1] ?? '';

        if ($action === 'list') {
            $this->myServices($request);
        } elseif ($action === 'view') {
            $svcId = (int)($parts[2] ?? 0);
            $this->showService($request, $svcId);
        } elseif ($action === 'renew') {
            $svcId = (int)($parts[2] ?? 0);
            $this->bot->answerCallbackQuery($request->getCallbackQueryId(), 'در حال پردازش درخواست تمدید...', true);
            $this->bot->sendMessage($request->getChatId(), "جهت تمدید، می‌توانید از منوی فروشگاه پلن مشابه را خریداری فرمایید یا موجودی کیف پول خود را شارژ نمایید.");
        }
    }
}
