<?php


if (!function_exists('fx_bulk_delete_ids')) {

    function fx_bulk_delete_ids(PDO $pdo, string $table, string $pkCol, array $rawIds, bool $castInt = true): int
    {
        if ($castInt) {
            $ids = array_filter(array_map('intval', $rawIds), function ($v) { return $v > 0; });
            $ids = array_values(array_unique($ids));
        } else {
            $ids = array_filter(array_map('strval', $rawIds), function ($v) { return $v !== ''; });
            $ids = array_values(array_unique($ids));
        }
        if (!$ids) return 0;
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$pkCol` IN ($ph)");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }
}

if (!function_exists('fx_bulk_delete_redirect')) {

    function fx_bulk_delete_redirect(string $baseUrl, int $requested, int $deleted, string $suffix = ''): void
    {
        $param = 'bulk' . $suffix;
        $qs = $requested > 0
            ? ($deleted > 0 ? "$param=ok&{$param}n=$deleted" : "$param=err")
            : "$param=empty";
        header('Location: ' . $baseUrl . '?' . $qs);
        exit;
    }
}

if (!function_exists('fx_bulk_delete_flash_html')) {

    function fx_bulk_delete_flash_html(string $suffix = '', string $label = ''): string
    {
        $param = 'bulk' . $suffix;
        $bulk = (string)($_GET[$param] ?? '');
        if ($bulk === '') return '';

        $prefix = $label !== '' ? $label . ': ' : '';

        if ($bulk === 'ok') {
            $n = (int)($_GET[$param . 'n'] ?? 0);
            $msg = $prefix . $n . ' مورد با موفقیت حذف شد.';
            return '<div class="alert" style="background:var(--color-success-soft); border:1px solid var(--color-success); color:var(--color-success); padding:12px 16px; border-radius:10px; margin-bottom:18px; display:flex; align-items:center; gap:10px;">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        if ($bulk === 'empty') {
            return '<div class="alert" style="background:var(--color-warning-soft); border:1px solid var(--color-warning); color:var(--color-warning); padding:12px 16px; border-radius:10px; margin-bottom:18px;">' . htmlspecialchars($prefix . 'هیچ موردی انتخاب نشده بود.', ENT_QUOTES, 'UTF-8') . '</div>';
        }
        return '<div class="alert" style="background:var(--color-danger-soft); border:1px solid var(--color-danger); color:var(--color-danger); padding:12px 16px; border-radius:10px; margin-bottom:18px;">' . htmlspecialchars($prefix . 'حذف موارد انتخاب‌شده ناموفق بود.', ENT_QUOTES, 'UTF-8') . '</div>';
    }
}
