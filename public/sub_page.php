<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use Zorvex\Core\Database;

$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    die('توکن اشتراک یافت نشد.');
}

$db = Database::getInstance();
$service = $db->selectOne("SELECT s.*, p.name as product_name FROM services s LEFT JOIN products p ON s.product_id = p.id WHERE s.sub_id = :token", ['token' => $token]);

if (!$service) {
    die('اشتراک مورد نظر در سیستم ثبت نشده است.');
}

$usedBytes = (int)$service['traffic_used_bytes'];
$totalBytes = max(1, (int)$service['traffic_total_bytes']);
$usedPct = min(100, round(($usedBytes / $totalBytes) * 100, 1));
$usedFormatted = format_bytes($usedBytes);
$totalFormatted = format_bytes($totalBytes);

$expireTimestamp = (int)$service['expire_date'];
$daysRemaining = max(0, (int)ceil(($expireTimestamp - time()) / 86400));
$expireJalali = $expireTimestamp > 0 ? jalali_now('Y/m/d', $expireTimestamp) : 'نامحدود';

$appUrl = rtrim((string)zorvex_config('app.url'), '/');
$subUrl = "{$appUrl}/sub.php?token={$token}";
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zorvex VIP | اشتراک اختصاصی</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Vazirmatn', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        body {
            background: #090d16;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.18) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(236, 72, 153, 0.15) 0px, transparent 50%);
        }
        .container {
            width: 100%;
            max-width: 480px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 32px 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(99, 102, 241, 0.2);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.4);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .title {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 20px 0;
        }
        .stat-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 18px;
            padding: 16px;
            text-align: center;
        }
        .stat-val {
            font-size: 18px;
            font-weight: 800;
            color: #38bdf8;
            margin-top: 4px;
        }
        .stat-label {
            font-size: 12px;
            color: #94a3b8;
        }
        .progress-box {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 20px;
        }
        .progress-header {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 8px;
            color: #cbd5e1;
        }
        .progress-bar-wrap {
            height: 10px;
            background: #1e293b;
            border-radius: 9999px;
            overflow: hidden;
            position: relative;
        }
        .progress-bar-fill {
            height: 100%;
            width: <?= $usedPct ?>%;
            background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
            border-radius: 9999px;
            transition: width 1s ease-in-out;
        }
        .sub-input-box {
            display: flex;
            gap: 8px;
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 6px;
            margin-bottom: 20px;
        }
        .sub-input {
            flex: 1;
            background: transparent;
            border: none;
            color: #94a3b8;
            padding: 10px 14px;
            font-size: 12px;
            direction: ltr;
            outline: none;
        }
        .btn-copy {
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-copy:hover {
            background: #4338ca;
        }
        .qr-section {
            text-align: center;
            margin: 20px 0;
        }
        .qr-img {
            width: 140px;
            height: 140px;
            border-radius: 16px;
            background: white;
            padding: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .apps-title {
            font-size: 14px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        .app-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 12px 18px;
            color: #f8fafc;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            transition: all 0.2s;
        }
        .app-btn:hover {
            background: rgba(79, 70, 229, 0.25);
            border-color: rgba(99, 102, 241, 0.5);
            transform: translateX(-4px);
        }
        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 100;
        }
        .toast.show {
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="badge">
            <span>●</span> سرویس فعال Zorvex
        </div>
        <h1 class="title"><?= htmlspecialchars($service['product_name'] ?: $service['service_username']) ?></h1>
    </div>

    <div class="progress-box">
        <div class="progress-header">
            <span>مصرف ترافیک</span>
            <span><b><?= $usedFormatted ?></b> از <?= $totalFormatted ?></span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill"></div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">زمان باقی‌مانده</div>
            <div class="stat-val"><?= $daysRemaining ?> روز</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">تاریخ انقضا</div>
            <div class="stat-val"><?= $expireJalali ?></div>
        </div>
    </div>

    <div class="qr-section">
        <img class="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($subUrl) ?>" alt="QR Code">
    </div>

    <div class="sub-input-box">
        <input type="text" id="subUrl" class="sub-input" readonly value="<?= $subUrl ?>">
        <button class="btn-copy" onclick="copyLink()">کپی لینک</button>
    </div>

    <div class="apps-title">افزودن مستقیم به نرم‌افزار:</div>
    <a href="streisand://import/<?= $subUrl ?>" class="app-btn">
        <span>🍏 اتصال به Streisand (iOS)</span>
        <span>←</span>
    </a>
    <a href="v2rayng://install-config?url=<?= urlencode($subUrl) ?>" class="app-btn">
        <span>📱 اتصال به v2rayNG (Android)</span>
        <span>←</span>
    </a>
    <a href="sing-box://import-remote-profile?url=<?= urlencode($subUrl) ?>" class="app-btn">
        <span>⚡ اتصال به Sing-box</span>
        <span>←</span>
    </a>
    <a href="hiddify://import/<?= $subUrl ?>" class="app-btn">
        <span>🛡️ اتصال به Hiddify Next</span>
        <span>←</span>
    </a>
</div>

<div id="toast" class="toast">لینک اشتراک در حافظه کپی شد! ✅</div>

<script>
function copyLink() {
    const copyText = document.getElementById("subUrl");
    copyText.select();
    navigator.clipboard.writeText(copyText.value);
    
    const toast = document.getElementById("toast");
    toast.classList.add("show");
    setTimeout(() => {
        toast.classList.remove("show");
    }, 2500);
}
</script>

</body>
</html>
