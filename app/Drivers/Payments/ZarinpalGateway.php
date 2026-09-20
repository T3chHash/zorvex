<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Payments;

use Zorvex\Core\Database;

class ZarinpalGateway implements PaymentInterface
{
    private string $merchantId;
    private string $callbackUrl;

    public function __construct(?string $merchantId = null)
    {
        $db = Database::getInstance();
        $setting = $db->selectOne("SELECT key_value FROM settings WHERE key_name = 'zarinpal_merchant'");
        $this->merchantId = $merchantId ?? ($setting['key_value'] ?? '');
        $this->callbackUrl = rtrim((string)zorvex_config('app.url'), '/') . '/api/payment_callback.php?gateway=zarinpal';
    }

    public function createPayment(array $order): array
    {
        if (empty($this->merchantId)) {
            return ['success' => false, 'redirect_url' => null, 'error' => 'مرچنت زرین‌پال تنظیم نشده است.'];
        }

        // Convert Toman to Rial if necessary or keep Toman per ZarinPal v4
        $amountRials = $order['final_amount'] * 10;

        $data = [
            'merchant_id' => $this->merchantId,
            'amount' => $amountRials,
            'description' => "خرید سرویس Zorvex - شناسه سفارش: {$order['order_code']}",
            'callback_url' => "{$this->callbackUrl}&order_id={$order['id']}",
        ];

        $ch = curl_init('https://payment.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode((string)$res, true);
        if (!empty($json['data']['authority']) && ($json['data']['code'] ?? 0) === 100) {
            $authority = $json['data']['authority'];
            return [
                'success' => true,
                'redirect_url' => "https://payment.zarinpal.com/pg/StartPay/{$authority}",
                'authority' => $authority,
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'redirect_url' => null,
            'error' => $json['errors']['message'] ?? 'خطا در اتصال به درگاه زرین‌پال',
        ];
    }

    public function verifyPayment(array $order, array $payload = []): array
    {
        $authority = $payload['Authority'] ?? '';
        $amountRials = $order['final_amount'] * 10;

        $data = [
            'merchant_id' => $this->merchantId,
            'amount' => $amountRials,
            'authority' => $authority,
        ];

        $ch = curl_init('https://payment.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode((string)$res, true);
        $code = $json['data']['code'] ?? -1;
        if ($code === 100 || $code === 101) {
            return [
                'success' => true,
                'ref_id' => (string)($json['data']['ref_id'] ?? $authority),
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'ref_id' => null,
            'error' => $json['errors']['message'] ?? 'تراکنش ناموفق بود یا لغو گردید.',
        ];
    }
}
