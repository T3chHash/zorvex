<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;

class WalletController
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

        $balanceFormatted = format_price($this->user['balance'] ?? 0);

        // Fetch recent transactions
        $transactions = $this->db->select(
            "SELECT * FROM transactions WHERE user_id = :uid ORDER BY id DESC LIMIT 5",
            ['uid' => $userId]
        );

        $txListText = "";
        if (!empty($transactions)) {
            $txListText = "\n\n📋 <b>آخرین تراکنش‌ها:</b>\n";
            foreach ($transactions as $tx) {
                $sign = $tx['amount'] >= 0 ? '🟢 +' : '🔴 ';
                $amt = format_price(abs($tx['amount']));
                $date = jalali_now('m/d H:i', (int)$tx['created_at']);
                $txListText .= "▫️ {$sign}{$amt} | {$tx['description']} ({$date})\n";
            }
        }

        $card = Theme::card('کیف پول اختصاصی Zorvex', [
            'موجودی شما' => $balanceFormatted,
            'وضعیت حساب' => '🟢 فعال و معتبر',
        ], '💳');

        $text = $card . $txListText . "\n\n"
              . "برای افزایش موجودی، یکی از مبالغ پیشنهادی زیر را انتخاب نمایید:";

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '➕ ۵۰,۰۰۰ تومان', 'callback_data' => 'wallet:charge:50000'],
                    ['text' => '➕ ۱۰۰,۰۰۰ تومان', 'callback_data' => 'wallet:charge:100000'],
                ],
                [
                    ['text' => '➕ ۲۰۰,۰۰۰ تومان', 'callback_data' => 'wallet:charge:200000'],
                    ['text' => '➕ ۵۰۰,۰۰۰ تومان', 'callback_data' => 'wallet:charge:500000'],
                ],
            ]
        ];

        $this->bot->sendMessage($chatId, $text, $buttons);
    }

    public function handleCallback(Request $request, array $parts): void
    {
        $action = $parts[1] ?? '';

        if ($action === 'charge') {
            $amount = (int)($parts[2] ?? 50000);
            $chatId = $request->getChatId();

            $orderCode = 'WAL' . strtoupper(substr(uniqid(), -6));
            $now = time();

            $orderId = $this->db->insert('orders', [
                'order_code' => $orderCode,
                'user_id' => $this->user['id'],
                'product_id' => null,
                'service_id' => null,
                'type' => 'wallet_charge',
                'amount' => $amount,
                'discount_amount' => 0,
                'final_amount' => $amount,
                'gateway' => 'card',
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->db->update('users', [
                'step' => "receipt:{$orderCode}",
                'step_data' => json_encode(['order_id' => $orderId]),
            ], 'id = :id', ['id' => $this->user['id']]);

            $card = $this->db->selectOne("SELECT * FROM cards WHERE is_active = 1 LIMIT 1");
            $cardNum = $card['card_number'] ?? '6037990000000000';
            $holder = $card['holder_name'] ?? 'مدیریت';
            $bank = $card['bank_name'] ?? 'ملی';

            $msg = "💳 <b>شارژ کیف پول به مبلغ " . format_price($amount) . "</b>\n\n"
                . "▫️ <b>شماره کارت:</b> <code>{$cardNum}</code>\n"
                . "▫️ <b>بانک:</b> {$bank}\n"
                . "▫️ <b>به نام:</b> {$holder}\n"
                . "▫️ <b>شناسه سفارش:</b> <code>{$orderCode}</code>\n\n"
                . "⚠️ لطفاً پس از واریز، عکس فیش یا شماره پیگیری تراکنش را همینجا ارسال نمایید.";

            $this->bot->sendMessage($chatId, $msg, [
                'keyboard' => [[['text' => '🔙 انصراف و بازگشت به منوی اصلی']]],
                'resize_keyboard' => true,
            ]);
        }
    }
}
