<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Payments;

use Zorvex\Core\Database;

class WalletPaymentGateway implements PaymentInterface
{
    public function createPayment(array $order): array
    {
        $db = Database::getInstance();
        $user = $db->selectOne("SELECT balance FROM users WHERE id = :id", ['id' => $order['user_id']]);

        if (!$user || (int)$user['balance'] < (int)$order['final_amount']) {
            $required = format_price($order['final_amount']);
            $current = format_price($user['balance'] ?? 0);
            return [
                'success' => false,
                'redirect_url' => null,
                'error' => "موجودی کیف پول شما کافی نیست!\nموجودی فعلی: {$current}\nمبلغ مورد نیاز: {$required}",
            ];
        }

        return [
            'success' => true,
            'redirect_url' => null,
            'instruction' => 'پرداخت مستقیم و آنی از کیف پول',
            'error' => null,
        ];
    }

    public function verifyPayment(array $order, array $payload = []): array
    {
        $db = Database::getInstance();
        $user = $db->selectOne("SELECT balance FROM users WHERE id = :id", ['id' => $order['user_id']]);

        if (!$user || (int)$user['balance'] < (int)$order['final_amount']) {
            return ['success' => false, 'ref_id' => null, 'error' => 'موجودی ناکافی'];
        }

        // Deduct balance atomically
        $newBalance = (int)$user['balance'] - (int)$order['final_amount'];
        $db->update('users', ['balance' => $newBalance], 'id = :id', ['id' => $order['user_id']]);

        // Insert transaction record
        $db->insert('transactions', [
            'user_id' => $order['user_id'],
            'order_id' => $order['id'],
            'type' => 'purchase',
            'amount' => -$order['final_amount'],
            'balance_after' => $newBalance,
            'description' => "خرید سرویس با شناسه سفارش: {$order['order_code']}",
            'created_at' => time(),
        ]);

        return [
            'success' => true,
            'ref_id' => 'WALLET_' . time(),
            'error' => null,
        ];
    }
}
