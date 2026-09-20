<?php


declare(strict_types=1);

require_once __DIR__ . '/BaseHandler.php';
require_once __DIR__ . '/DiscountSupport.php';

final class GiftCodeHandler extends BaseHandler
{
    public function handle(): void
    {
        $this->requireMethod('POST');

        $code = FaoximaInput::string($this->data, 'code');
        if ($code === '') {
            FaoximaResponse::badRequest('code is required');
        }

        $result = MiniDiscount::redeemGift($code, $this->user);
        if (empty($result['ok'])) {
            FaoximaResponse::fail(422, (string)($result['reason'] ?? '❌ کد هدیه نامعتبر است.'));
        }

        $this->reportGift($code, (int)$result['amount']);

        FaoximaResponse::ok([
            'amount'      => (int)$result['amount'],
            'new_balance' => (float)$result['new_balance'],
            'message'     => '🎁 کد هدیه با موفقیت اعمال شد. مبلغ ' . number_format((int)$result['amount']) . ' تومان به کیف پول شما اضافه شد.',
        ]);
    }

    private function reportGift(string $code, int $amount): void
    {
        $channel = (string)($this->setting['Channel_Report'] ?? '');
        if ($channel === '') return;
        $otherRow = select('topicid', 'idreport', 'report', 'otherservice', 'select');
        $topic = is_array($otherRow) ? (string)($otherRow['idreport'] ?? '') : '';
        $uid = (string)($this->user['id'] ?? '');
        $text = "🎁 استفاده از کد هدیه\n"
              . "<blockquote>آیدی عددی: {$uid}</blockquote>\n"
              . "<blockquote>کد: {$code}</blockquote>\n"
              . "<blockquote>مبلغ: " . number_format($amount) . " تومان</blockquote>";
        try {
            telegram('sendmessage', [
                'chat_id'           => $channel,
                'message_thread_id' => $topic,
                'text'              => $text,
                'parse_mode'        => 'HTML',
            ]);
        } catch (Throwable $e) {
        }
    }
}
