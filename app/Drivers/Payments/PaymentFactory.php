<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Payments;

use InvalidArgumentException;

class PaymentFactory
{
    public static function create(string $gateway): PaymentInterface
    {
        return match (strtolower($gateway)) {
            'card', 'card_to_card' => new CardToCardGateway(),
            'zarinpal' => new ZarinpalGateway(),
            'nowpayments', 'crypto' => new NowpaymentsGateway(),
            'wallet' => new WalletPaymentGateway(),
            default => throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}"),
        };
    }
}
