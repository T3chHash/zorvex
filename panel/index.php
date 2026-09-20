<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/icons.php';
require_auth();

$totalUsers = 0;
$newToday = 0;
$totalRevenue = 0;
$activeNow = 0;
$pendingPay = 0;
$txToday = 0;

try {
    $totalUsers = db_count($pdo, "SELECT COUNT(*) FROM user");
    $newToday = db_count($pdo, "SELECT COUNT(*) FROM user WHERE register > ?", [strtotime('today')]);
} catch (Exception $e) {
}

try {
    $totalRevenue = (int) db_query($pdo, "SELECT COALESCE(SUM(price_product),0) FROM invoice WHERE Status IN ('active','end_of_time','end_of_volume','sendedwarn','send_on_hold')")->fetchColumn();
    $activeNow = db_count($pdo, "SELECT COUNT(*) FROM invoice WHERE Status='active'");
} catch (Exception $e) {
}

try {
    $pendingPay = db_count($pdo, "SELECT COUNT(*) FROM Payment_report WHERE payment_Status='waiting'");
    $txToday = db_count($pdo, "SELECT COUNT(*) FROM Payment_report WHERE time > ?", [strtotime('today')]);
} catch (Exception $e) {
}

$recentInvoices = [];
$recentUsers = [];
try {
    $recentInvoices = db_fetchAll($pdo, "SELECT * FROM invoice ORDER BY time_sell DESC LIMIT 8");
} catch (Exception $e) {
}
try {
    $recentUsers = db_fetchAll($pdo, "SELECT * FROM user ORDER BY register DESC LIMIT 8");
} catch (Exception $e) {
}

$pageTitle = $textbotlang['panel']['dashboardTitle'];
$activeNav = 'dashboard';
$showPageHead = false;
include __DIR__ . '/inc/layout_head.php';
?>

<div class="stats fade-up">
    <div class="stat">
        <div class="stat-label"><?= $textbotlang['panel']['dashTotalUsers'] ?></div>
        <div class="stat-num"><?= number_format($totalUsers) ?></div>
        <div class="stat-meta"><?= $newToday > 0 ? '<span class="up">+' . $newToday . $textbotlang['panel']['dashTodaySpan'] : $textbotlang['panel']['dashNoChange'] ?>
        </div>
    </div>
    <div class="stat ok">
        <div class="stat-label"><?= $textbotlang['panel']['dashTotalRevenue'] ?></div>
        <div class="stat-num">
            <?= $totalRevenue >= 1_000_000
                ? number_format($totalRevenue / 1_000_000, 1) . $textbotlang['panel']['dashUnitMillionToman']
                : number_format($totalRevenue) . $textbotlang['panel']['dashUnitToman'] ?>
        </div>
        <div class="stat-meta"><?= $textbotlang['panel']['dashTotalSales'] ?></div>
    </div>
    <div class="stat warn">
        <div class="stat-label"><?= $textbotlang['panel']['dashActiveService'] ?></div>
        <div class="stat-num"><?= number_format($activeNow) ?></div>
    </div>
    <div class="stat <?= $pendingPay > 0 ? 'no' : '' ?>">
        <div class="stat-label"><?= $pendingPay > 0 ? $textbotlang['panel']['dashPendingPayment'] : $textbotlang['panel']['dashTodayTransaction'] ?></div>
        <div class="stat-num" style="<?= $pendingPay > 0 ? 'color:var(--no)' : '' ?>">
            <?= number_format($pendingPay > 0 ? $pendingPay : $txToday) ?>
        </div>
        <div class="stat-meta">
            <?= $pendingPay > 0 ? $textbotlang['panel']['dashReviewLink'] : $textbotlang['panel']['dashStatusRegistered'] ?>
        </div>
    </div>
</div>

<div class="two-col">
    <div class="card fade-up d1">
        <div class="card-head">
            <div>
                <div class="card-title"><?= $textbotlang['panel']['dashRecentOrders'] ?></div>
                <div class="card-subtitle"><?= count($recentInvoices) ?> <?= $textbotlang['panel']['dashRecentItem'] ?></div>
            </div>
            <a href="invoice.php" class="btn-link" style="font-size:.78rem"><?= $textbotlang['panel']['dashViewAll'] ?></a>
        </div>
        <div class="tbl-wrap">
            <table class="tbl-sm">
                <thead>
                    <tr>
                        <th><?= $textbotlang['panel']['dashColUser'] ?></th>
                        <th><?= $textbotlang['panel']['dashColProduct'] ?></th>
                        <th><?= $textbotlang['panel']['dashColAmount'] ?></th>
                        <th><?= $textbotlang['panel']['dashColStatus'] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentInvoices)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty" style="padding:24px">
                                    <p><?= $textbotlang['panel']['dashNoOrdersYet'] ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php else:
                        $statusMap = [
                            'active'         => ['tag-ok',    $textbotlang['panel']['dashStatusActive']],
                            'end_of_time'    => ['tag-warn',  $textbotlang['panel']['dashStatusExpired']],
                            'end_of_volume'  => ['tag-no',    $textbotlang['panel']['dashStatusVolumeFinished']],
                            'sendedwarn'     => ['tag-warn',  $textbotlang['panel']['dashStatusWarning']],
                            'send_on_hold'   => ['tag-plain', $textbotlang['panel']['dashStatusWaiting']],
                            'unpaid'         => ['tag-warn',  $textbotlang['panel']['invoiceStatusUnpaid']],
                        ];
                        foreach ($recentInvoices as $inv):
                            [$tagClass, $label] = $statusMap[$inv['Status'] ?? ''] ?? ['tag-plain', $inv['Status'] ?? '—'];
                            ?>
                            <tr>
                                <td class="cm cf"><?= htmlspecialchars($inv['id_user'] ?? '—') ?></td>
                                <td class="cs"
                                    style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    <?= htmlspecialchars(trunc($inv['name_product'] ?? '—', 20)) ?>
                                </td>
                                <td class="cn" style="white-space:nowrap">
                                    <?= number_format((int) ($inv['price_product'] ?? 0)) ?> <span class="cf"><?= $textbotlang['panel']['dashTomanShort'] ?></span>
                                </td>
                                <td><span class="tag <?= $tagClass ?>"><?= $label ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card fade-up d2">
        <div class="card-head">
            <div>
                <div class="card-title"><?= $textbotlang['panel']['dashRecentUsers'] ?></div>
                <div class="card-subtitle"><?= count($recentUsers) ?> <?= $textbotlang['panel']['dashRecentItem2'] ?></div>
            </div>
            <a href="users.php" class="btn-link" style="font-size:.78rem"><?= $textbotlang['panel']['dashViewAll2'] ?></a>
        </div>
        <div class="tbl-wrap">
            <table class="tbl-sm">
                <thead>
                    <tr>
                        <th><?= $textbotlang['panel']['dashColId'] ?></th>
                        <th><?= $textbotlang['panel']['dashColName'] ?></th>
                        <th><?= $textbotlang['panel']['dashColBalance'] ?></th>
                        <th><?= $textbotlang['panel']['dashColGroup'] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentUsers)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty" style="padding:24px">
                                    <p><?= $textbotlang['panel']['dashNoUsersYet'] ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php else:
                        foreach ($recentUsers as $u):
                            $agent = $u['agent'] ?? 'f';
                            $isBlocked = ($u['User_Status'] ?? '') === 'block';
                            $name = $u['namecustom'] ?? '';
                            if ($name === 'none')
                                $name = '';
                            $uname = $u['username'] ?? '';
                            if ($uname === 'none')
                                $uname = '';
                            ?>
                            <tr>
                                <td class="cm cf"><?= htmlspecialchars($u['id']) ?></td>
                                <td>
                                    <?php if ($name): ?>
                                        <span class="cs"><?= htmlspecialchars(trunc($name, 14)) ?></span>
                                    <?php elseif ($uname): ?>
                                        <span class="cm" style="color:var(--ac)">@<?= htmlspecialchars(trunc($uname, 12)) ?></span>
                                    <?php else: ?>
                                        <span class="cf">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cn" style="white-space:nowrap">
                                    <?= number_format((int) ($u['Balance'] ?? 0)) ?> <span class="cf"><?= $textbotlang['panel']['dashTomanShort2'] ?></span>
                                </td>
                                <td>
                                    <?php if ($isBlocked): ?>
                                        <span class="tag tag-no" style="font-size:.65rem"><?= $textbotlang['panel']['dashLabelBlocked'] ?></span>
                                    <?php else: ?>
                                        <span class="tag <?= user_role_tag($agent) ?>" style="font-size:.65rem">
                                            <?= user_role_label($agent) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/inc/layout_foot.php'; ?>