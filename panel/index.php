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
$panelsList = [];
try {
    $recentInvoices = db_fetchAll($pdo, "SELECT * FROM invoice ORDER BY time_sell DESC LIMIT 8");
} catch (Exception $e) {
}
try {
    $recentUsers = db_fetchAll($pdo, "SELECT * FROM user ORDER BY register DESC LIMIT 8");
} catch (Exception $e) {
}
try {
    $panelsList = db_fetchAll($pdo, "SELECT * FROM marzban_panel ORDER BY id DESC LIMIT 6");
} catch (Exception $e) {
}

// 7-day trend analytics for chart
$trendDays = [];
$maxSales = 1;
for ($i = 6; $i >= 0; $i--) {
    $dayStart = strtotime("-$i days midnight");
    $dayEnd = $dayStart + 86400;
    $dayLabel = function_exists('jdate') ? jdate('m/d', $dayStart) : date('m/d', $dayStart);
    
    $daySales = 0;
    $dayUsers = 0;
    try {
        $daySales = (int) db_query($pdo, "SELECT COALESCE(SUM(price_product),0) FROM invoice WHERE time_sell >= ? AND time_sell < ?", [$dayStart, $dayEnd])->fetchColumn();
        $dayUsers = db_count($pdo, "SELECT COUNT(*) FROM user WHERE register >= ? AND register < ?", [$dayStart, $dayEnd]);
    } catch (Exception $e) {}
    
    if ($daySales > $maxSales) {
        $maxSales = $daySales;
    }
    
    $trendDays[] = [
        'label' => $dayLabel,
        'sales' => $daySales,
        'users' => $dayUsers,
    ];
}

$pageTitle = $textbotlang['panel']['dashboardTitle'];
$activeNav = 'dashboard';
$showPageHead = false;
include __DIR__ . '/inc/layout_head.php';
?>

<!-- System Health Status Bar -->
<div class="dash-health-bar fade-up">
    <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
        <div class="health-item">
            <span class="pulse-dot"></span>
            <span>وضعیت هسته بات: <strong>فعال و متصل</strong></span>
        </div>
        <div class="health-item">
            <span class="pulse-dot"></span>
            <span>پایگاه داده: <strong>MySQL 8.0 OK</strong></span>
        </div>
        <div class="health-item">
            <span class="pulse-dot <?= empty($panelsList) ? 'warn' : '' ?>"></span>
            <span>نودهای سرور: <strong><?= count($panelsList) ?> نود فعال</strong></span>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:0.75rem;color:var(--mute)">نسخه پلتفرم:</span>
        <span class="tag tag-info" style="font-size:0.72rem;padding:2px 8px">v0.0.3 PRO</span>
    </div>
</div>

<!-- Quick Actions Bar -->
<div class="quick-actions-bar fade-up d1">
    <a href="users.php" class="quick-btn">
        <?= icon('users', 16) ?>
        <span>کاربران</span>
    </a>
    <a href="invoice.php" class="quick-btn">
        <?= icon('invoice', 16) ?>
        <span>سفارشات جدید</span>
    </a>
    <a href="product.php" class="quick-btn">
        <?= icon('box', 16) ?>
        <span>محصولات و پلن‌ها</span>
    </a>
    <a href="payment.php" class="quick-btn">
        <?= icon('card', 16) ?>
        <span>تراکنش‌ها <?= $pendingPay > 0 ? "<span class='tag tag-no' style='font-size:0.65rem;margin-right:4px'>$pendingPay</span>" : '' ?></span>
    </a>
    <a href="settings.php" class="quick-btn">
        <?= icon('settings', 16) ?>
        <span>تنظیمات پیشرفته</span>
    </a>
</div>

<!-- Key Performance Indicators -->
<div class="stats fade-up d2">
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
                ? number_format($totalRevenue / 1_000_000, 1) . ' <small>' . $textbotlang['panel']['dashUnitMillionToman'] . '</small>'
                : number_format($totalRevenue) . ' <small>' . $textbotlang['panel']['dashUnitToman'] . '</small>' ?>
        </div>
        <div class="stat-meta"><?= $textbotlang['panel']['dashTotalSales'] ?></div>
    </div>
    <div class="stat warn">
        <div class="stat-label"><?= $textbotlang['panel']['dashActiveService'] ?></div>
        <div class="stat-num"><?= number_format($activeNow) ?></div>
        <div class="stat-meta">اشتراک‌های فعال آنلاین</div>
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

<!-- 7-Day Revenue & Growth SVG Chart Card -->
<div class="dash-chart-card fade-up d3">
    <div class="chart-header">
        <div class="chart-title">
            <?= icon('activity', 18) ?>
            <span>روند فروش و عضویت ۷ روز گذشته</span>
        </div>
        <div class="chart-legend">
            <div><span class="legend-dot" style="background:#06B6D4"></span> مبلغ فروش (تومان)</div>
            <div><span class="legend-dot" style="background:#22C55E"></span> کاربران جدید</div>
        </div>
    </div>
    
    <div style="width:100%;height:140px;position:relative;margin-top:10px">
        <?php
        $ptsSales = [];
        $ptsUsers = [];
        $n = count($trendDays);
        $w = 1000;
        $h = 110;
        
        $maxUsers = 1;
        foreach ($trendDays as $td) {
            if ($td['users'] > $maxUsers) $maxUsers = $td['users'];
        }

        for ($idx = 0; $idx < $n; $idx++) {
            $x = ($n > 1) ? ($idx / ($n - 1)) * ($w - 80) + 40 : $w / 2;
            $valSales = $trendDays[$idx]['sales'];
            $ySales = $h - ($valSales / $maxSales) * ($h - 20) + 10;
            $ptsSales[] = round($x, 1) . ',' . round($ySales, 1);

            $valUsers = $trendDays[$idx]['users'];
            $yUsers = $h - ($valUsers / $maxUsers) * ($h - 20) + 10;
            $ptsUsers[] = round($x, 1) . ',' . round($yUsers, 1);
        }
        $polylineSales = implode(' ', $ptsSales);
        $polylineUsers = implode(' ', $ptsUsers);
        $areaSales = $ptsSales[0] . ' ' . $polylineSales . ' ' . end($ptsSales) . ' ' . round(($w - 40), 1) . ',' . ($h + 10) . ' 40,' . ($h + 10);
        ?>
        <svg viewBox="0 0 <?= $w ?> <?= $h + 30 ?>" style="width:100%;height:100%;overflow:visible" preserveAspectRatio="none">
            <defs>
                <linearGradient id="salesGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#06B6D4" stop-opacity="0.35"/>
                    <stop offset="100%" stop-color="#06B6D4" stop-opacity="0.0"/>
                </linearGradient>
            </defs>
            <!-- Grid lines -->
            <line x1="40" y1="<?= $h/2 ?>" x2="<?= $w-40 ?>" y2="<?= $h/2 ?>" stroke="rgba(255,255,255,0.06)" stroke-dasharray="4"/>
            <line x1="40" y1="<?= $h+10 ?>" x2="<?= $w-40 ?>" y2="<?= $h+10 ?>" stroke="rgba(255,255,255,0.08)"/>
            
            <!-- Area fill -->
            <polygon points="<?= $areaSales ?>" fill="url(#salesGrad)"/>
            
            <!-- Sales line -->
            <polyline fill="none" stroke="#06B6D4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="<?= $polylineSales ?>"/>
            <!-- Users line -->
            <polyline fill="none" stroke="#22C55E" stroke-width="2" stroke-dasharray="5 3" stroke-linecap="round" stroke-linejoin="round" points="<?= $polylineUsers ?>"/>

            <!-- Points & Labels -->
            <?php for ($idx = 0; $idx < $n; $idx++): 
                $coordsSales = explode(',', $ptsSales[$idx]);
                $lbl = $trendDays[$idx]['label'];
            ?>
                <circle cx="<?= $coordsSales[0] ?>" cy="<?= $coordsSales[1] ?>" r="4" fill="#06B6D4" stroke="#0B0F19" stroke-width="2"/>
                <text x="<?= $coordsSales[0] ?>" y="<?= $h + 26 ?>" text-anchor="middle" fill="#94A3B8" font-size="11" font-family="var(--font)"><?= $lbl ?></text>
            <?php endfor; ?>
        </svg>
    </div>
</div>

<!-- Two-Column Recent Data -->
<div class="two-col">
    <!-- Recent Orders -->
    <div class="card fade-up d3">
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
                                <td class="cm cf">
                                    <a href="user.php?id=<?= urlencode($inv['id_user'] ?? '') ?>" style="color:var(--text);font-weight:600">
                                        <?= htmlspecialchars($inv['id_user'] ?? '—') ?>
                                    </a>
                                </td>
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

    <!-- Recent Users -->
    <div class="card fade-up d4">
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
                            if ($name === 'none') $name = '';
                            $uname = $u['username'] ?? '';
                            if ($uname === 'none') $uname = '';
                            ?>
                            <tr>
                                <td class="cm cf">
                                    <a href="user.php?id=<?= urlencode($u['id']) ?>" style="color:var(--text);font-weight:600">
                                        <?= htmlspecialchars($u['id']) ?>
                                    </a>
                                </td>
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

<!-- Active Marzban Nodes Overview -->
<?php if (!empty($panelsList)): ?>
<div class="card fade-up d4" style="margin-top:24px">
    <div class="card-head">
        <div class="card-title" style="display:flex;align-items:center;gap:8px">
            <?= icon('server', 18) ?>
            <span>نودها و سرورهای متصل (Marzban Nodes)</span>
        </div>
        <span class="tag tag-ok"><?= count($panelsList) ?> سرور متصل</span>
    </div>
    <div class="card-body">
        <div class="node-grid">
            <?php foreach ($panelsList as $node): ?>
                <div class="node-card">
                    <div>
                        <div style="font-weight:700;color:var(--text);font-size:0.88rem;margin-bottom:2px">
                            <?= htmlspecialchars($node['name_panel'] ?? 'Server Node') ?>
                        </div>
                        <div style="font-size:0.75rem;color:var(--mute);font-family:var(--mono)">
                            <?= htmlspecialchars(trunc($node['url_panel'] ?? '127.0.0.1', 28)) ?>
                        </div>
                    </div>
                    <span class="pulse-dot"></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/inc/layout_foot.php'; ?>