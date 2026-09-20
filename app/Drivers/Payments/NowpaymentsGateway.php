<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Payments;

use Zorvex\Core\Database;

class NowpaymentsGateway implements PaymentInterface
{
    private string $apiKey;
    private string $callbackUrl;

    public function __construct(?string $apiKey = null)
    {
        $db = Database::getInstance();
        $setting = $db->selectOne("SELECT key_value FROM settings WHERE key_name = 'nowpayments_api_key'");
        $this->apiKey = $apiKey ?? ($setting['key_value'] ?? '');
        $this->callbackUrl = rtrim((string)zorvex_config('app.url'), '/') . '/api/payment_callback.php?gateway=nowpayments';
    }

    public function createPayment(array $order): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'redirect_url' => null, 'error' => 'کلید API درگاه NowPayments تنظیم نشده است.'];
        }

        // Approximate USD value (e.g. 1 USD = 80,000 Tomans default fallback)
        $amountUsd = max(1.0, round($order['final_amount'] / 80000, 2));

        $data = [
            'price_amount' => $amountUsd,
            'price_currency' => 'usd',
            'order_id' => $order['order_code'],
            'order_description' => "Zorvex Order #{$order['order_code']}",
            'ipn_callback_url' => $this->callbackUrl,
            'success_url' => rtrim((string)zorvex_config('app.url'), '/') . '/payment_success.php',
            'cancel_url' => rtrim((string)zorvex_config('app.url'), '/') . '/payment_cancel.php',
        ];

        $ch = curl_init('https://api.nowpayments.io/v1/invoice');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode((string)$res, true);
        if (!empty($json['invoice_url'])) {
            return [
                'success' => true,
                'redirect_url' => $json['invoice_url'],
                'invoice_id' => $json['id'] ?? null,
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'redirect_url' => null,
            'error' => $json['message'] ?? 'خطا در ایجاد درگاه کریپتو NowPayments',
        ];
    }

    public function verifyPayment(array $order, array $payload = []): array
    {
        $status = $payload['payment_status'] ?? '';
        if (in_array($status, ['finished', 'confirmed', 'sending'])) {
            return [
                'success' => true,
                'ref_id' => (string)($payload['payment_id'] ?? 'CRYPTO_OK'),
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'ref_id' => null,
            'error' => "وضعیت پرداخت: {$status}",
        ];
    }
}
