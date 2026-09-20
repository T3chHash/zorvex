<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Zorvex Mini App</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(30, 41, 59, 0.6);
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Vazirmatn', sans-serif; }
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: 80px;
        }
        .header {
            padding: 20px 16px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .brand {
            font-size: 18px;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .user-chip {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 9999px;
            padding: 4px 12px;
            font-size: 12px;
            color: #818cf8;
            font-weight: 600;
        }
        .content {
            padding: 16px;
        }
        .tab-pane {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .tab-pane.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .progress-bar-wrap {
            height: 8px;
            background: #1e293b;
            border-radius: 9999px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #ec4899);
            border-radius: 9999px;
        }
        .btn-primary {
            display: block;
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-primary:active {
            transform: scale(0.98);
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-around;
            padding: 8px 0;
            z-index: 100;
        }
        .nav-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: var(--text-sub);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 12px;
            border: none;
            background: transparent;
        }
        .nav-tab.active {
            color: var(--primary);
        }
        .nav-icon {
            font-size: 20px;
        }
    </style>
</head>
<body>

<header class="header">
    <div class="brand">⚡ Zorvex Mini App</div>
    <div class="user-chip" id="userBadge">کاربر تلگرام</div>
</header>

<main class="content">
    <!-- Tab 1: Services -->
    <section id="tab-services" class="tab-pane active">
        <div class="card">
            <div class="card-title">
                <span>🛡️ اشتراک فعال من</span>
                <span style="font-size: 12px; color: #34d399;">🟢 فعال</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--text-sub);">
                <span>ترافیک مصرفی:</span>
                <span><b>۱۲.۴ GB</b> از ۵۰ GB</span>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill" style="width: 25%;"></div>
            </div>
            <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 12px;">
                اعتبار باقی‌مانده: ۲۲ روز
            </div>
            <button class="btn-primary" onclick="copyConfig()">📋 کپی لینک اشتراک هوشمند</button>
        </div>
    </section>

    <!-- Tab 2: Shop -->
    <section id="tab-shop" class="tab-pane">
        <div class="card">
            <div class="card-title">🚀 پلن اقتصادی ۳۰ روزه</div>
            <p style="font-size: 13px; color: var(--text-sub); margin-bottom: 12px;">۳۰ گیگابایت ترافیک بین‌الملل - آی‌پی ثابت</p>
            <div style="font-size: 18px; font-weight: 800; color: #38bdf8; margin-bottom: 12px;">۹۵,۰۰۰ تومان</div>
            <button class="btn-primary" onclick="orderPlan(1)">خرید آنی</button>
        </div>
        <div class="card">
            <div class="card-title">⚡ پلن حرفه‌ای ۳۰ روزه</div>
            <p style="font-size: 13px; color: var(--text-sub); margin-bottom: 12px;">۶۰ گیگابایت ترافیک نامحدود کاربر - پینگ فوق‌العاده</p>
            <div style="font-size: 18px; font-weight: 800; color: #38bdf8; margin-bottom: 12px;">۱۷۵,۰۰۰ تومان</div>
            <button class="btn-primary" onclick="orderPlan(2)">خرید آنی</button>
        </div>
    </section>

    <!-- Tab 3: Wallet -->
    <section id="tab-wallet" class="tab-pane">
        <div class="card" style="text-align: center;">
            <div style="font-size: 13px; color: var(--text-sub);">موجودی فعلی حساب شما</div>
            <div style="font-size: 28px; font-weight: 900; color: #34d399; margin: 10px 0;">۰ تومان</div>
            <button class="btn-primary" onclick="switchTab('shop')">💳 شارژ حساب</button>
        </div>
    </section>
</main>

<nav class="bottom-nav">
    <button class="nav-tab active" onclick="switchTab('services')">
        <span class="nav-icon">🛡️</span>
        <span>سرویس‌ها</span>
    </button>
    <button class="nav-tab" onclick="switchTab('shop')">
        <span class="nav-icon">🛒</span>
        <span>فروشگاه</span>
    </button>
    <button class="nav-tab" onclick="switchTab('wallet')">
        <span class="nav-icon">💳</span>
        <span>کیف پول</span>
    </button>
</nav>

<script>
const tg = window.Telegram?.WebApp;
if (tg) {
    tg.ready();
    tg.expand();
    const user = tg.initDataUnsafe?.user;
    if (user) {
        document.getElementById('userBadge').innerText = user.first_name || ('@' + user.username);
    }
}

function switchTab(tabId) {
    if (tg?.HapticFeedback) tg.HapticFeedback.selectionChanged();
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-tab').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}

function copyConfig() {
    if (tg?.HapticFeedback) tg.HapticFeedback.notificationOccurred('success');
    alert('لینک اشتراک کپی شد!');
}

function orderPlan(id) {
    if (tg?.HapticFeedback) tg.HapticFeedback.impactOccurred('medium');
    if (tg) {
        tg.sendData(JSON.stringify({ action: 'buy_plan', plan_id: id }));
        tg.close();
    } else {
        alert('درخواست خرید به بات تلگرام ارسال شد.');
    }
}
</script>

</body>
</html>
