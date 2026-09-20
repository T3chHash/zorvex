<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Payments;

interface PaymentInterface
{
    /**
     * Initialize payment request
     *
     * @param array $order Order record
     * @return array ['success' => bool, 'redirect_url' => ?string, 'instruction' => ?string, 'error' => ?string]
     */
    public function createPayment(array $order): array;

    /**
     * Verify payment status
     *
     * @param array $order Order record
     * @param array $payload Callback or webhook data
     * @return array ['success' => bool, 'ref_id' => ?string, 'error' => ?string]
     */
    public function verifyPayment(array $order, array $payload = []): array;
}
