<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Payments;

use Zorvex\Core\Database;

class CardToCardGateway implements PaymentInterface
{
    public function createPayment(array $order): array
    {
        $db = Database::getInstance();
        $card = $db->selectOne("SELECT * FROM cards WHERE is_active = 1 ORDER BY current_daily ASC LIMIT 1");

        if (!$card) {
            return [
                'success' => false,
                'redirect_url' => null,
                'instruction' => null,
                'error' => 'در حال حاضر هیچ شماره کارتی برای پرداخت فعال نیست. لطفاً با پشتیبانی تماس بگیرید.',
            ];
        }

        $formattedAmount = format_price($order['final_amount']);
        $cardNumber = chunk_split($card['card_number'], 4, ' ');

        $instruction = "💳 <b>اطلاعات پرداخت کارت به کارت</b>\n\n"
            . "▫️ <b>شماره کارت:</b> <code>{$card['card_number']}</code>\n"
            . "▫️ <b>بانک:</b> {$card['bank_name']}\n"
            . "▫️ <b>به نام:</b> <b>{$card['holder_name']}</b>\n"
            . "▫️ <b>مبلغ قابل پرداخت:</b> <code>{$formattedAmount}</code>\n"
            . "▫️ <b>شناسه سفارش:</b> <code>{$order['order_code']}</code>\n\n"
            . "⚠️ <i>پس از واریز، لطفاً تصویر فیش واریزی یا شماره پیگیری تراکنش را در همین چت ارسال نمایید.</i>";

        return [
            'success' => true,
            'redirect_url' => null,
            'instruction' => $instruction,
            'error' => null,
            'card' => $card,
        ];
    }

    public function verifyPayment(array $order, array $payload = []): array
    {
        $refId = $payload['ref_id'] ?? 'MANUAL_VERIFIED';
        return [
            'success' => true,
            'ref_id' => $refId,
            'error' => null,
        ];
    }
}
