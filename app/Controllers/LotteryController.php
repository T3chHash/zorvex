<?php
declare(strict_types=1);

namespace Zorvex\Controllers;

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Theme;
use Zorvex\Core\Database;

class LotteryController
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

    public function spin(Request $request): void
    {
        $chatId = $request->getChatId();
        $userId = $this->user['id'];

        // Check last spin time (1 spin every 24h)
        $lastSpin = $this->db->selectOne(
            "SELECT created_at FROM transactions WHERE user_id = :uid AND type = 'bonus' ORDER BY id DESC LIMIT 1",
            ['uid' => $userId]
        );

        $now = time();
        if ($lastSpin && ($now - (int)$lastSpin['created_at']) < 86400) {
            $hoursLeft = (int)ceil((86400 - ($now - (int)$lastSpin['created_at'])) / 3600);
            $this->bot->sendMessage($chatId, "⏳ شما در ۲۴ ساعت گذشته گردونه شانس را چرخاندید!\nلطفاً <b>{$hoursLeft} ساعت دیگر</b> مجدداً شانس خود را امتحان فرمایید.");
            return;
        }

        // Possible prizes
        $prizes = [
            ['text' => '🎁 ۵,۰۰۰ تومان شارژ هدیه کیف پول', 'amount' => 5000],
            ['text' => '🎁 ۱۰,۰۰۰ تومان شارژ هدیه کیف پول', 'amount' => 10000],
            ['text' => '🎁 ۲۰,۰۰۰ تومان شارژ هدیه کیف پول', 'amount' => 20000],
            ['text' => '💫 ۲,۰۰۰ تومان شارژ هدیه کیف پول', 'amount' => 2000],
        ];

        $won = $prizes[array_rand($prizes)];
        $newBalance = (int)($this->user['balance'] ?? 0) + $won['amount'];

        $this->db->update('users', ['balance' => $newBalance], 'id = :id', ['id' => $userId]);

        $this->db->insert('transactions', [
            'user_id' => $userId,
            'order_id' => null,
            'type' => 'bonus',
            'amount' => $won['amount'],
            'balance_after' => $newBalance,
            'description' => 'جایزه گردونه شانس روزانه',
            'created_at' => $now,
        ]);

        $msg = "🎡 <b>گردونه شانس Zorvex با موفقیت چرخید!</b>\n\n"
             . "تبریک! شما برنده شدید:\n"
             . "<b>{$won['text']}</b>\n\n"
             . "مبلغ به موجودی حساب شما افزوده شد. موجودی جدید: <b>" . format_price($newBalance) . "</b>";

        $this->bot->sendMessage($chatId, $msg);
    }
}
