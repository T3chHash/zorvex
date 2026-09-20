<?php
// Zorvex Pro - National Network Stock Engine
// Handles shelves, stock reservation, and automated delivery for Iran national network / direct configs.

if (!function_exists('nmPanelNationalEnabled')) {
    function nmPanelNationalEnabled($panel): bool {
        if (!is_array($panel)) return false;
        $st = (string)($panel['national_net_status'] ?? '');
        if ($st === '1' || $st === 'active' || $st === 'on' || $st === 'on_national_net') return true;
        $type = strtolower((string)($panel['type'] ?? ''));
        if (strpos($type, 'national') !== false || strpos($type, 'stock') !== false) return true;
        return false;
    }
}

if (!function_exists('nmProductCategoryList')) {
    function nmProductCategoryList($product): array {
        if (!is_array($product)) return [];
        $raw = (string)($product['category'] ?? '');
        if ($raw === '') return [];
        $parts = explode(',', $raw);
        return array_values(array_unique(array_filter(array_map('trim', $parts))));
    }
}

if (!function_exists('nmStockDetectFormat')) {
    function nmStockDetectFormat(string $content): string {
        $c = trim($content);
        if (preg_match('/^https?:\/\//i', $c)) return 'subscription';
        if (preg_match('/^(vmess|vless|trojan|ss|ssr|hysteria2|hy2|tuic):\/\//i', $c)) return 'single';
        if (stripos($c, '[Interface]') !== false || stripos($c, 'PrivateKey') !== false) return 'wireguard';
        return 'text';
    }
}

if (!function_exists('nmStockReserveForProduct')) {
    function nmStockReserveForProduct($panel, $product, $userId, $orderId, string $mode = 'buy') {
        global $pdo;
        if (!($pdo instanceof PDO)) return false;

        $panelCode = is_array($panel) ? (string)($panel['code_panel'] ?? '') : (string)$panel;
        $productCode = is_array($product) ? (string)($product['code_product'] ?? '') : (string)$product;

        try {
            $pdo->beginTransaction();

            $row = null;
            if ($panelCode !== '') {
                $stmt = $pdo->prepare("
                    SELECT c.* FROM nm_config_stock c
                    LEFT JOIN nm_stock_shelves s ON c.shelf_id = s.id
                    WHERE c.status = 'active'
                      AND (c.codeproduct = :prod OR s.codeproduct = :prod)
                      AND (s.source_codepanel = :panel OR s.stock_codepanel = :panel OR c.codepanel = :panel)
                    ORDER BY c.id ASC
                    LIMIT 1
                    FOR UPDATE
                ");
                $stmt->execute([':prod' => $productCode, ':panel' => $panelCode]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            if (!$row) {
                $stmt = $pdo->prepare("
                    SELECT * FROM nm_config_stock
                    WHERE status = 'active' AND codeproduct = :prod
                    ORDER BY id ASC
                    LIMIT 1
                    FOR UPDATE
                ");
                $stmt->execute([':prod' => $productCode]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            if ($row) {
                $up = $pdo->prepare("
                    UPDATE nm_config_stock
                    SET status = 'reserved',
                        assigned_user = :u,
                        assigned_invoice = :inv,
                        assigned_mode = :m,
                        reserved_at = :t
                    WHERE id = :id AND status = 'active'
                ");
                $up->execute([
                    ':u'   => (string)$userId,
                    ':inv' => (string)$orderId,
                    ':m'   => $mode,
                    ':t'   => time(),
                    ':id'  => (int)$row['id'],
                ]);
                $pdo->commit();
                return $row;
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[nm_stock] nmStockReserveForProduct error: ' . $e->getMessage());
        }
        return false;
    }
}

if (!function_exists('nmStockReleaseReservation')) {
    function nmStockReleaseReservation($stock): void {
        global $pdo;
        if (!($pdo instanceof PDO)) return;
        $id = is_array($stock) ? (int)($stock['id'] ?? 0) : (int)$stock;
        if ($id <= 0) return;

        try {
            $stmt = $pdo->prepare("
                UPDATE nm_config_stock
                SET status = 'active',
                    assigned_user = '',
                    assigned_invoice = '',
                    assigned_mode = '',
                    reserved_at = NULL
                WHERE id = :id AND status = 'reserved'
            ");
            $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('[nm_stock] nmStockReleaseReservation error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('nmStockMarkDelivered')) {
    function nmStockMarkDelivered($stockId, $userId, $orderId, array $opts = []): void {
        global $pdo;
        if (!($pdo instanceof PDO)) return;
        $id = (int)$stockId;
        if ($id <= 0) return;

        try {
            $mode = (string)($opts['mode'] ?? 'delivered');
            $stmt = $pdo->prepare("
                UPDATE nm_config_stock
                SET status = 'delivered',
                    assigned_user = :u,
                    assigned_invoice = :inv,
                    assigned_mode = :m,
                    delivered_at = :t
                WHERE id = :id
            ");
            $stmt->execute([
                ':u'   => (string)$userId,
                ':inv' => (string)$orderId,
                ':m'   => $mode,
                ':t'   => time(),
                ':id'  => $id,
            ]);
        } catch (\Throwable $e) {
            error_log('[nm_stock] nmStockMarkDelivered error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('nmStockHasAvailableForProduct')) {
    function nmStockHasAvailableForProduct($panel, $product): bool {
        global $pdo;
        if (!($pdo instanceof PDO)) return false;

        $panelCode = is_array($panel) ? (string)($panel['code_panel'] ?? '') : (string)$panel;
        $productCode = is_array($product) ? (string)($product['code_product'] ?? '') : (string)$product;

        try {
            if ($panelCode !== '') {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM nm_config_stock c
                    LEFT JOIN nm_stock_shelves s ON c.shelf_id = s.id
                    WHERE c.status = 'active'
                      AND (c.codeproduct = :prod OR s.codeproduct = :prod)
                      AND (s.source_codepanel = :panel OR s.stock_codepanel = :panel OR c.codepanel = :panel)
                ");
                $stmt->execute([':prod' => $productCode, ':panel' => $panelCode]);
                if ((int)$stmt->fetchColumn() > 0) return true;
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM nm_config_stock WHERE status = 'active' AND codeproduct = :prod");
            $stmt->execute([':prod' => $productCode]);
            return ((int)$stmt->fetchColumn() > 0);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
