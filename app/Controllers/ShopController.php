<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;
use Zorvex\Drivers\Panels\PanelFactory;
use Zorvex\Drivers\Payments\PaymentFactory;
use Exception;

class ShopController
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

    public function categories(Request $request): void
    {
        $chatId = $request->getChatId();
        $categories = $this->db->select("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");

        if (empty($categories)) {
            // Direct products if no categories
            $this->showProductsByCategory($request, 0);
            return;
        }

        $buttons = [];
        $row = [];
        foreach ($categories as $cat) {
            $icon = $cat['icon'] ?: '⚡';
            $row[] = ['text' => "{$icon} {$cat['name']}", 'callback_data' => "shop:cat:{$cat['id']}"];
            if (count($row) === 2) {
                $buttons[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $buttons[] = $row;
        }

        $text = "🛒 <b>فروشگاه سرویس‌های اختصاصی Zorvex</b>\n\n"
              . "لطفاً دسته‌بندی لوکیشن یا پلن مورد نظر خود را انتخاب نمایید:";

        $this->bot->sendMessage($chatId, $text, ['inline_keyboard' => $buttons]);
    }

    public function showProductsByCategory(Request $request, int $categoryId): void
    {
        $chatId = $request->getChatId();
        $sql = "SELECT * FROM products WHERE is_active = 1 AND is_test = 0";
        $params = [];

        if ($categoryId > 0) {
            $sql .= " AND category_id = :cid";
            $params['cid'] = $categoryId;
        }
        $sql .= " ORDER BY sort_order ASC, price ASC";

        $products = $this->db->select($sql, $params);

        if (empty($products)) {
            $this->bot->sendMessage($chatId, "⚠️ در حال حاضر پلن فعالی در این دسته‌بندی موجود نیست.");
            return;
        }

        $buttons = [];
        foreach ($products as $p) {
            $priceFormatted = format_price($p['price']);
            $btnText = "▫️ {$p['name']} | {$p['traffic_gb']} GB ({$p['duration_days']} روز) - {$priceFormatted}";
            $buttons[] = [
                ['text' => $btnText, 'callback_data' => "shop:plan:{$p['id']}"]
            ];
        }

        $buttons[] = [
            ['text' => '🔙 بازگشت به دسته‌بندی‌ها', 'callback_data' => 'shop:back_cats']
        ];

        $text = "🛍️ <b>پلن‌های موجود</b>\n\n"
              . "تمامی پلن‌ها دارای تضمین اتصال، آی‌پی ثابت و پشتیبانی ۲۴ ساعته هستند.\n"
              . "جهت مشاهده جزئیات و خرید، پلن مورد نظر را انتخاب نمایید:";

        if ($request->isCallbackQuery()) {
            $this->bot->editMessageText($chatId, $request->getMessageId(), $text, ['inline_keyboard' => $buttons]);
        } else {
            $this->bot->sendMessage($chatId, $text, ['inline_keyboard' => $buttons]);
        }
    }

    public function showPlanReview(Request $request, int $productId): void
    {
        $chatId = $request->getChatId();
        $product = $this->db->selectOne("SELECT * FROM products WHERE id = :id AND is_active = 1", ['id' => $productId]);

        if (!$product) {
            $this->bot->sendMessage($chatId, "پلن انتخابی یافت نشد.");
            return;
        }

        $price = (int)$product['price'];
        $discount = 0;

        // Check reseller discount
        if (!empty($this->user['is_reseller']) && (int)$this->user['reseller_discount_pct'] > 0) {
            $discountPct = (int)$this->user['reseller_discount_pct'];
            $discount = (int)round(($price * $discountPct) / 100);
        }

        $finalPrice = max(0, $price - $discount);

        $details = Theme::card('پیش‌فاکتور خرید سرویس', [
            'عنوان پلن' => $product['name'],
            'حجم ترافیک' => "{$product['traffic_gb']} گیگابایت",
            'مدت زمان' => "{$product['duration_days']} روز",
            'مبلغ اصلی' => format_price($price),
            'تخفیف همکاری' => format_price($discount),
            'مبلغ قابل پرداخت' => format_price($finalPrice),
        ], '🛒');

        $userBalance = (int)($this->user['balance'] ?? 0);
        $userBalanceFormatted = format_price($userBalance);

        $text = $details . "\n\n"
              . "💰 <b>موجودی فعلی کیف پول شما:</b> <code>{$userBalanceFormatted}</code>\n\n"
              . "روش پرداخت مورد نظر خود را انتخاب نمایید:";

        $buttons = [
            [
                ['text' => '💳 پرداخت آنی از کیف پول', 'callback_data' => "shop:pay:wallet:{$productId}"]
            ],
            [
                ['text' => '🏦 کارت به کارت (تأیید فیش)', 'callback_data' => "shop:pay:card:{$productId}"],
                ['text' => '🌐 درگاه آنلاین شتابی', 'callback_data' => "shop:pay:zarinpal:{$productId}"],
            ],
            [
                ['text' => '🪙 درگاه رمز ارز (NowPayments)', 'callback_data' => "shop:pay:crypto:{$productId}"]
            ],
            [
                ['text' => '🔙 بازگشت به لیست پلن‌ها', 'callback_data' => 'shop:back_cats']
            ]
        ];

        $this->bot->editMessageText($chatId, $request->getMessageId(), $text, ['inline_keyboard' => $buttons]);
    }

    public function initiatePayment(Request $request, string $gateway, int $productId): void
    {
        $chatId = $request->getChatId();
        $userId = $this->user['id'];
        $product = $this->db->selectOne("SELECT * FROM products WHERE id = :id", ['id' => $productId]);

        if (!$product) {
            $this->bot->sendMessage($chatId, "پلن معتبر نیست.");
            return;
        }

        $orderCode = 'ZX' . strtoupper(substr(uniqid(), -6)) . mt_rand(10, 99);
        $amount = (int)$product['price'];
        $discount = 0;

        if (!empty($this->user['is_reseller']) && (int)$this->user['reseller_discount_pct'] > 0) {
            $discountPct = (int)$this->user['reseller_discount_pct'];
            $discount = (int)round(($amount * $discountPct) / 100);
        }

        $finalAmount = max(0, $amount - $discount);
        $now = time();

        $orderId = $this->db->insert('orders', [
            'order_code' => $orderCode,
            'user_id' => $userId,
            'product_id' => $productId,
            'service_id' => null,
            'type' => 'new',
            'amount' => $amount,
            'discount_amount' => $discount,
            'final_amount' => $finalAmount,
            'gateway' => $gateway,
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $order = $this->db->selectOne("SELECT * FROM orders WHERE id = :id", ['id' => $orderId]);

        try {
            $gatewayDriver = PaymentFactory::create($gateway);
            $payResult = $gatewayDriver->createPayment($order);

            if (!$payResult['success']) {
                $this->bot->sendMessage($chatId, "❌ خطا: " . ($payResult['error'] ?? 'انجام تراکنش با مشکل مواجه شد.'));
                return;
            }

            // If wallet, verify and activate directly
            if ($gateway === 'wallet') {
                $verifyResult = $gatewayDriver->verifyPayment($order);
                if ($verifyResult['success']) {
                    $this->db->update('orders', [
                        'status' => 'paid',
                        'receipt_ref' => $verifyResult['ref_id'],
                        'updated_at' => time(),
                    ], 'id = :id', ['id' => $orderId]);

                    $this->provisionService($order);
                    return;
                } else {
                    $this->bot->sendMessage($chatId, "❌ " . ($verifyResult['error'] ?? 'خطا در کسر موجودی'));
                    return;
                }
            }

            // If Card-to-Card, prompt for receipt
            if ($gateway === 'card' || $gateway === 'card_to_card') {
                $this->db->update('users', [
                    'step' => "receipt:{$orderCode}",
                    'step_data' => json_encode(['order_id' => $orderId]),
                ], 'id = :id', ['id' => $userId]);

                $cancelKeyboard = [
                    'keyboard' => [
                        [['text' => '🔙 انصراف و بازگشت به منوی اصلی']]
                    ],
                    'resize_keyboard' => true
                ];

                $this->bot->sendMessage($chatId, $payResult['instruction'], $cancelKeyboard);
                return;
            }

            // Online Gateway (URL Redirect)
            if (!empty($payResult['redirect_url'])) {
                $linkButtons = [
                    'inline_keyboard' => [
                        [['text' => '💳 ورود به صفحه پرداخت امن', 'url' => $payResult['redirect_url']]],
                        [['text' => '🔙 انصراف', 'callback_data' => 'shop:back_cats']]
                    ]
                ];

                $msg = "⚡ <b>درگاه پرداخت آماده است</b>\n\n"
                     . "مبلغ قابل پرداخت: <b>" . format_price($finalAmount) . "</b>\n"
                     . "شناسه سفارش: <code>{$orderCode}</code>\n\n"
                     . "روی دکمه زیر کلیک کرده و مراحل پرداخت را تکمیل کنید:";

                $this->bot->sendMessage($chatId, $msg, $linkButtons);
            }
        } catch (Exception $e) {
            $this->bot->sendMessage($chatId, "خطای سیستم در اتصال به درگاه: " . $e->getMessage());
        }
    }

    public function handleReceiptUpload(Request $request, string $step): bool
    {
        $chatId = $request->getChatId();
        $userId = $this->user['id'];
        $orderCode = str_replace('receipt:', '', $step);

        $order = $this->db->selectOne("SELECT * FROM orders WHERE order_code = :code AND user_id = :uid", [
            'code' => $orderCode,
            'uid' => $userId,
        ]);

        if (!$order || $order['status'] !== 'pending') {
            $this->db->update('users', ['step' => 'none', 'step_data' => null], 'id = :id', ['id' => $userId]);
            $this->bot->sendMessage($chatId, "سفارش نامعتبر یا قبلاً پردازش شده است.", Theme::mainKeyboard($this->user['is_admin']));
            return true;
        }

        $photoId = $request->getPhotoFileId();
        $textRef = $request->getText();

        $this->db->update('orders', [
            'status' => 'waiting_approval',
            'receipt_image' => $photoId,
            'receipt_ref' => $photoId ? 'IMAGE_UPLOAD' : $textRef,
            'updated_at' => time(),
        ], 'id = :id', ['id' => $order['id']]);

        // Reset step
        $this->db->update('users', ['step' => 'none', 'step_data' => null], 'id = :id', ['id' => $userId]);

        $this->bot->sendMessage(
            $chatId,
            "✅ <b>رسید شما با موفقیت ثبت شد!</b>\n\n"
            . "سفارش شما در صف بررسی اپراتور قرار گرفت. به محض تأیید، کانفیگ اختصاصی شما به صورت خودکار در همینجا ارسال خواهد شد.",
            Theme::mainKeyboard((bool)$this->user['is_admin'])
        );

        // Notify Admin Channel
        $this->notifyAdminChannelOfReceipt($order, $photoId, $textRef);

        return true;
    }

    private function notifyAdminChannelOfReceipt(array $order, ?string $photoId, ?string $textRef): void
    {
        $logChannel = (string)zorvex_config('telegram.log_channel');
        $adminList = zorvex_config('telegram.admins', []);
        $targetChat = !empty($logChannel) ? $logChannel : ($adminList[0] ?? null);

        if (!$targetChat) {
            return;
        }

        $userDisplay = $this->user['username'] ? '@' . $this->user['username'] : $this->user['first_name'];
        $amountFormatted = format_price($order['final_amount']);

        $caption = "🔔 <b>رسید کارت به کارت جدید جهت بررسی</b>\n\n"
            . "▫️ <b>شناسه سفارش:</b> <code>{$order['order_code']}</code>\n"
            . "▫️ <b>کاربر:</b> {$userDisplay} (<code>{$this->user['id']}</code>)\n"
            . "▫️ <b>مبلغ:</b> <b>{$amountFormatted}</b>\n"
            . "▫️ <b>توضیح/کد پیگیری:</b> " . e_html($textRef ?? 'تصویر ارسال شده') . "\n\n"
            . "لطفاً با استفاده از دکمه‌های زیر تصمیم‌گیری فرمایید:";

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ تأیید و صدور آنی سرویس', 'callback_data' => "admin:approve_order:{$order['id']}"],
                    ['text' => '❌ رد رسید', 'callback_data' => "admin:reject_order:{$order['id']}"],
                ]
            ]
        ];

        if ($photoId) {
            $this->bot->sendPhoto($targetChat, $photoId, $caption, $buttons);
        } else {
            $this->bot->sendMessage($targetChat, $caption, $buttons);
        }
    }

    public function provisionService(array $order): bool
    {
        $product = $this->db->selectOne("SELECT * FROM products WHERE id = :id", ['id' => $order['product_id']]);
        $panel = $this->db->selectOne("SELECT * FROM panels WHERE id = :id AND is_active = 1", ['id' => $product['panel_id']]);

        if (!$panel) {
            $panel = $this->db->selectOne("SELECT * FROM panels WHERE is_active = 1 LIMIT 1");
        }

        if (!$panel) {
            $this->bot->sendMessage($order['user_id'], "❌ خطای سرور: هیچ پنل فعالی برای ساخت کانفیگ یافت نشد. با پشتیبانی تماس بگیرید.");
            return false;
        }

        $serviceUsername = 'zx_' . $order['user_id'] . '_' . random_str(5);
        $subId = bin2hex(random_bytes(10));
        $trafficBytes = $product['traffic_gb'] * 1024 * 1024 * 1024;
        $expireTimestamp = time() + ($product['duration_days'] * 86400);

        try {
            $driver = PanelFactory::create($panel);
            $result = $driver->createUser($serviceUsername, $trafficBytes, $expireTimestamp);

            if (!$result['success']) {
                $this->bot->sendMessage($order['user_id'], "❌ خطا در ایجاد اکانت در سرور: " . ($result['error'] ?? ''));
                return false;
            }

            $rawSubUrl = $result['sub_url'];
            // Wrap in Zorvex Smart Sub URL
            $appUrl = rtrim((string)zorvex_config('app.url'), '/');
            $smartSubUrl = "{$appUrl}/sub.php?token={$subId}";

            $now = time();
            $serviceId = $this->db->insert('services', [
                'user_id' => $order['user_id'],
                'product_id' => $product['id'],
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

            $this->db->update('orders', ['service_id' => $serviceId], 'id = :id', ['id' => $order['id']]);

            // Handle affiliate reward if user was invited
            $this->processAffiliateCommission($order);

            // Send notification to user
            $card = Theme::serviceCard(
                $product['name'],
                'active',
                0,
                $trafficBytes,
                $expireTimestamp,
                $smartSubUrl
            );

            $msg = "🎉 <b>سرویس اختصاصی شما با موفقیت ساخته شد!</b>\n\n"
                 . $card . "\n\n"
                 . "📱 <b>راهنمای سریع:</b> لینک اشتراک بالا را کپی کرده و در نرم‌افزار خود Import نمایید.";

            $buttons = [
                'inline_keyboard' => [
                    [['text' => '🌐 باز کردن در وب‌اپلیکیشن (Mini App)', 'url' => "{$appUrl}/miniapp/index.php?token={$subId}"]],
                    [['text' => '🛡️ مشاهده سرویس‌های من', 'callback_data' => 'svc:list']],
                ]
            ];

            $this->bot->sendMessage($order['user_id'], $msg, $buttons);
            return true;
        } catch (Exception $e) {
            $this->bot->sendMessage($order['user_id'], "خطای سرور: " . $e->getMessage());
            return false;
        }
    }

    private function processAffiliateCommission(array $order): void
    {
        $buyer = $this->db->selectOne("SELECT invited_by FROM users WHERE id = :id", ['id' => $order['user_id']]);
        if (empty($buyer['invited_by'])) {
            return;
        }

        $inviterId = (int)$buyer['invited_by'];
        $inviter = $this->db->selectOne("SELECT * FROM users WHERE id = :id", ['id' => $inviterId]);
        if (!$inviter) {
            return;
        }

        $affiliatePct = (int)($this->db->selectOne("SELECT key_value FROM settings WHERE key_name = 'affiliate_percent'")['key_value'] ?? 15);
        $reward = (int)round(($order['final_amount'] * $affiliatePct) / 100);

        if ($reward <= 0) {
            return;
        }

        $newBalance = (int)$inviter['balance'] + $reward;
        $this->db->update('users', ['balance' => $newBalance], 'id = :id', ['id' => $inviterId]);

        $this->db->insert('transactions', [
            'user_id' => $inviterId,
            'order_id' => $order['id'],
            'type' => 'commission',
            'amount' => $reward,
            'balance_after' => $newBalance,
            'description' => "کمیسیون معرفی کاربر برای سفارش #{$order['order_code']}",
            'created_at' => time(),
        ]);

        $rewardFormatted = format_price($reward);
        $this->bot->sendMessage(
            $inviterId,
            "🎁 <b>پاداش معرفی زیرمجموعه!</b>\n\n"
            . "مبلغ <b>{$rewardFormatted}</b> بابت خرید یکی از زیرمجموعه‌های شما به کیف پولتان واریز شد! 💸"
        );
    }

    public function handleCallback(Request $request, array $parts): void
    {
        $action = $parts[1] ?? '';

        if ($action === 'cat') {
            $catId = (int)($parts[2] ?? 0);
            $this->showProductsByCategory($request, $catId);
        } elseif ($action === 'plan') {
            $planId = (int)($parts[2] ?? 0);
            $this->showPlanReview($request, $planId);
        } elseif ($action === 'pay') {
            $gateway = $parts[2] ?? 'wallet';
            $planId = (int)($parts[3] ?? 0);
            $this->initiatePayment($request, $gateway, $planId);
        } elseif ($action === 'back_cats') {
            $this->categories($request);
        }
    }
}
