<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseHandler.php';

final class CardWhitelistHandler extends BaseHandler
{
    public function handle(): void
    {
        $this->requireMethod('GET');

        $userId = (string)$this->user['id'];

        try {
            $pdo = FaoximaDb::pdo();

            $stmt = $pdo->prepare("
                SELECT DISTINCT cn.id, cn.cardnumber, cn.namecard, cn.is_active, cn.is_visible
                FROM card_number cn
                WHERE (
                    (cn.is_active = 1 AND (
                        NOT EXISTS (SELECT 1 FROM card_whitelist cw WHERE cw.card_id = cn.id)
                        OR EXISTS (SELECT 1 FROM card_whitelist cw WHERE cw.card_id = cn.id AND cw.user_id = ?)
                    ))
                    OR (cn.is_active = 0 AND EXISTS (SELECT 1 FROM card_whitelist cw WHERE cw.card_id = cn.id AND cw.user_id = ?))
                )
                AND NOT EXISTS (SELECT 1 FROM user_card_block ucb WHERE ucb.card_id = cn.id AND ucb.user_id = ?)
                ORDER BY cn.created_at DESC
            ");
            $stmt->execute([$userId, $userId, $userId]);
            $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($cards)) {
                FaoximaResponse::notFound('کارتی برای این کاربر موجود نیست');
                return;
            }

            $result = [];
            foreach ($cards as $card) {
                $result[] = [
                    'id' => (int)$card['id'],
                    'cardnumber' => (string)$card['cardnumber'],
                    'namecard' => (string)$card['namecard'],
                    'last4' => substr((string)$card['cardnumber'], -4),
                    'masked' => substr((string)$card['cardnumber'], 0, 4) . '****' . substr((string)$card['cardnumber'], -4),
                ];
            }

            FaoximaResponse::ok(['cards' => $result]);
        } catch (Throwable $e) {
            FaoximaLogger::error('CardWhitelistHandler error', ['error' => $e->getMessage()]);
            FaoximaResponse::fail(500, '❌ خطا در دریافت اطلاعات کارت');
        }
    }
}
