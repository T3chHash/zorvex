<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use Zorvex\Core\Database;
use Zorvex\Controllers\ShopController;
use Zorvex\Core\TelegramBot;

$db = Database::getInstance();

// Handle Actions (Approve/Reject from web)
$action = $_GET['action'] ?? '';
$orderId = (int)($_GET['order_id'] ?? 0);

if ($action === 'approve' && $orderId > 0) {
    $order = $db->selectOne("SELECT * FROM orders WHERE id = :id", ['id' => $orderId]);
    if ($order && $order['status'] !== 'paid') {
        $db->update('orders', ['status' => 'paid', 'updated_at' => time()], 'id = :id', ['id' => $orderId]);
        $bot = new TelegramBot();
        $user = $db->selectOne("SELECT * FROM users WHERE id = :id", ['id' => $order['user_id']]);
        if ($user) {
            $shop = new ShopController($bot, $user);
            $shop->provisionService($order);
        }
    }
    header("Location: index.php?tab=orders");
    exit;
} elseif ($action === 'reject' && $orderId > 0) {
    $db->update('orders', ['status' => 'rejected', 'updated_at' => time()], 'id = :id', ['id' => $orderId]);
    header("Location: index.php?tab=orders");
    exit;
}

// Fetch Stats
$totalUsers = $db->selectOne("SELECT COUNT(*) as c FROM users")['c'] ?? 0;
$activeServices = $db->selectOne("SELECT COUNT(*) as c FROM services WHERE status = 'active'")['c'] ?? 0;
$pendingOrders = $db->selectOne("SELECT COUNT(*) as c FROM orders WHERE status = 'waiting_approval'")['c'] ?? 0;
$totalRevenue = $db->selectOne("SELECT SUM(final_amount) as s FROM orders WHERE status = 'paid'")['s'] ?? 0;

$tab = $_GET['tab'] ?? 'overview';

// Queries for tabs
$ordersList = $db->select("SELECT o.*, u.username, u.first_name, p.name as product_name FROM orders o LEFT JOIN users u ON o.user_id = u.id LEFT JOIN products p ON o.product_id = p.id ORDER BY o.id DESC LIMIT 50");
$usersList = $db->select("SELECT * FROM users ORDER BY id DESC LIMIT 50");
$panelsList = $db->select("SELECT * FROM panels ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zorvex Admin | داشبورد مدیریت</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Vazirmatn', sans-serif; }
        body {
            background: #090e17;
            color: #e2e8f0;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #0f172a;
            border-left: 1px solid rgba(255, 255, 255, 0.08);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .brand {
            font-size: 22px;
            font-weight: 900;
            color: #6366f1;
            padding: 0 12px 20px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .page-title {
            font-size: 24px;
            font-weight: 800;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 20px;
            padding: 20px;
        }
        .stat-label {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #f8fafc;
        }
        .card-table {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 20px;
            padding: 24px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: right;
        }
        th {
            color: #94a3b8;
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-weight: 600;
        }
        td {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }
        .badge-status {
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-paid { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .status-waiting { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .status-rejected { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        .btn-action {
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            margin-left: 6px;
        }
        .btn-approve { background: #059669; color: white; }
        .btn-reject { background: #dc2626; color: white; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">⚡ Zorvex Pro</div>
    <a href="?tab=overview" class="nav-item <?= $tab === 'overview' ? 'active' : '' ?>">📊 پیشخوان</a>
    <a href="?tab=orders" class="nav-item <?= $tab === 'orders' ? 'active' : '' ?>">🛒 سفارش‌ها و فیش‌ها</a>
    <a href="?tab=users" class="nav-item <?= $tab === 'users' ? 'active' : '' ?>">👥 کاربران</a>
    <a href="?tab=panels" class="nav-item <?= $tab === 'panels' ? 'active' : '' ?>">🖥️ پنل‌ها و سرورها</a>
</aside>

<main class="main-content">
    <div class="header-top">
        <h1 class="page-title">کنترل پنل مدیریت Zorvex</h1>
        <div style="font-size: 13px; color: #94a3b8;"><?= jalali_now('l j F Y - H:i') ?></div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">درآمد کل سیستم</div>
            <div class="stat-value" style="color: #34d399;"><?= format_price((int)$totalRevenue) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">سرویس‌های فعال</div>
            <div class="stat-value" style="color: #38bdf8;"><?= number_format($activeServices) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">کاربران ثبت‌نامی</div>
            <div class="stat-value"><?= number_format($totalUsers) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">فیش‌های در انتظار بررسی</div>
            <div class="stat-value" style="color: #fbbf24;"><?= number_format($pendingOrders) ?></div>
        </div>
    </div>

    <?php if ($tab === 'orders'): ?>
        <div class="card-table">
            <h2 style="font-size: 18px; margin-bottom: 16px;">سفارشات و رسیدهای کارت به کارت</h2>
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>کاربر</th>
                        <th>پلن / آیتم</th>
                        <th>مبلغ</th>
                        <th>روش پرداخت</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordersList as $ord): ?>
                    <tr>
                        <td><code><?= $ord['order_code'] ?></code></td>
                        <td><?= htmlspecialchars($ord['first_name'] ?? 'کاربر') ?> (<?= $ord['user_id'] ?>)</td>
                        <td><?= htmlspecialchars($ord['product_name'] ?? $ord['type']) ?></td>
                        <td><b><?= format_price($ord['final_amount']) ?></b></td>
                        <td><?= $ord['gateway'] ?></td>
                        <td>
                            <?php
                            $stClass = match($ord['status']) {
                                'paid' => 'status-paid',
                                'waiting_approval' => 'status-waiting',
                                'rejected' => 'status-rejected',
                                default => ''
                            };
                            ?>
                            <span class="badge-status <?= $stClass ?>"><?= $ord['status'] ?></span>
                        </td>
                        <td>
                            <?php if ($ord['status'] === 'waiting_approval'): ?>
                                <a href="?action=approve&order_id=<?= $ord['id'] ?>" class="btn-action btn-approve">تأیید</a>
                                <a href="?action=reject&order_id=<?= $ord['id'] ?>" class="btn-action btn-reject">رد</a>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($tab === 'panels'): ?>
        <div class="card-table">
            <h2 style="font-size: 18px; margin-bottom: 16px;">پنل‌های متصل به سیستم</h2>
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>نام پنل</th>
                        <th>نوع</th>
                        <th>آدرس وب</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($panelsList as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><b><?= htmlspecialchars($p['name']) ?></b></td>
                        <td><code><?= $p['type'] ?></code></td>
                        <td dir="ltr"><?= htmlspecialchars($p['url']) ?></td>
                        <td><span class="badge-status status-paid">🟢 فعال</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card-table">
            <h2 style="font-size: 18px; margin-bottom: 16px;">آخرین کاربران عضو شده</h2>
            <table>
                <thead>
                    <tr>
                        <th>آیدی تلگرام</th>
                        <th>نام</th>
                        <th>یوزرنیم</th>
                        <th>موجودی</th>
                        <th>تاریخ عضویت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usersList as $u): ?>
                    <tr>
                        <td><code><?= $u['id'] ?></code></td>
                        <td><?= htmlspecialchars($u['first_name'] ?? '') ?></td>
                        <td><?= $u['username'] ? '@' . $u['username'] : '-' ?></td>
                        <td><b><?= format_price($u['balance']) ?></b></td>
                        <td><?= jalali_now('Y/m/d H:i', (int)$u['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
