<?php
require_once __DIR__ . '/icons.php';
$pageLede = $pageLede ?? '';
$activeNav = $activeNav ?? '';
$showPageHead = $showPageHead ?? true;
$currentUser = $_SESSION['admin_user'] ?? $textbotlang['panel']['layoutDefaultAdminName'];
$initials = mb_strtoupper(mb_substr($currentUser, 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
  <meta name="theme-color" content="#0F172A" id="mtc">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title><?= $textbotlang['panel']['layoutBrandName'] ?></title>
  <link rel="stylesheet" href="css/style.css">
  <script>
    (function () {
      var t = localStorage.getItem('panel-theme') || 'navy';
      var bg = {
        navy: '#0F172A', purple: '#180D2E', emerald: '#0A1F1C',
        sunset: '#1A0D0D', slate: '#080808', light: '#F1F5F9',
        linen: '#FAF7F2', mint: '#F0FDF4', lavender: '#FAF5FF'
      };

      var root = document.documentElement;
      root.style.backgroundColor = bg[t] || '#0F172A';
      root.setAttribute('data-theme', t);

      root.style.colorScheme = (t === 'light' || t === 'linen' || t === 'mint' || t === 'lavender') ? 'light' : 'dark';
      var mtc = document.getElementById('mtc');
      if (mtc && bg[t]) mtc.content = bg[t];
      if (localStorage.getItem('panel-sb-collapsed') === '1' && window.innerWidth > 768)
        root.classList.add('sb-pre-collapsed');
    }());
  </script>
  <script>
    window.PANEL_I18N = <?= json_encode(
      array_filter(
        $textbotlang['panel'] ?? [],
        fn($k) => strncmp($k, 'js_', 3) === 0,
        ARRAY_FILTER_USE_KEY
      ),
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ) ?>;
    window.t = function (key, vars) {
      var s = (window.PANEL_I18N && window.PANEL_I18N[key]) || key;
      if (vars) {
        for (var k in vars) {
          if (Object.prototype.hasOwnProperty.call(vars, k)) {
            s = s.replace('{' + k + '}', vars[k]);
          }
        }
      }
      return s;
    };
  </script>
</head>

<body>

  <div id="load-bar"></div>
  <div id="toast-area"></div>

  <div class="confirm-veil" id="confirm-veil">
    <div class="confirm-box">
      <div class="confirm-icon"><?= icon('block', 26) ?></div>
      <h4 id="confirm-title">تأیید عملیات</h4>
      <p id="confirm-msg">آیا از انجام این عملیات اطمینان دارید؟</p>
      <div class="confirm-btns">
        <button class="btn btn-no" id="confirm-ok">بله، انجام بده</button>
        <button class="btn btn-ghost" onclick="closeConfirm()">انصراف</button>
      </div>
    </div>
  </div>

  <div class="app">
    <div class="sidebar-backdrop" id="backdrop" onclick="closeSidebar()"></div>

    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <div class="brand-mark">Z</div>
        <div class="brand-name">ZORVEX<span> · PRO</span></div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section">
          <div class="nav-heading">اصلی</div>
          <a href="index.php" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>"
            title="داشبورد مدیریت">
            <span class="nav-icon"><?= icon('dashboard') ?></span><span
              class="nav-label">داشبورد</span>
          </a>
        </div>
        <div class="nav-section">
          <div class="nav-heading">مدیریت فروش و کاربران</div>
          <a href="users.php" class="nav-item <?= $activeNav === 'users' ? 'active' : '' ?>"
            title="مدیریت کاربران ربات">
            <span class="nav-icon"><?= icon('users') ?></span><span
              class="nav-label">کاربران</span>
          </a>
          <a href="invoice.php" class="nav-item <?= $activeNav === 'invoice' ? 'active' : '' ?>"
            title="فاکتورها و سفارشات">
            <span class="nav-icon"><?= icon('invoice') ?></span><span
              class="nav-label">سفارشات</span>
          </a>
          <a href="service.php" class="nav-item <?= $activeNav === 'service' ? 'active' : '' ?>"
            title="سرویس‌ها و کانفیگ‌ها">
            <span class="nav-icon"><?= icon('server') ?></span><span
              class="nav-label">سرویس‌ها</span>
          </a>
          <a href="product.php" class="nav-item <?= $activeNav === 'product' ? 'active' : '' ?>"
            title="بسته‌ها و محصولات">
            <span class="nav-icon"><?= icon('package') ?></span><span
              class="nav-label">پلن‌ها و تعرفه‌ها</span>
          </a>
          <a href="category.php" class="nav-item <?= $activeNav === 'category' ? 'active' : '' ?>"
            title="دسته‌بندی‌های محصولات">
            <span class="nav-icon"><?= icon('folder') ?></span><span
              class="nav-label">دسته‌بندی‌ها</span>
          </a>
          <a href="payment.php" class="nav-item <?= $activeNav === 'payment' ? 'active' : '' ?>"
            title="درگاه‌های پرداخت بانکی و کریپتو">
            <span class="nav-icon"><?= icon('card') ?></span><span
              class="nav-label">درگاه‌های پرداخت</span>
          </a>
          <a href="keyboard.php" class="nav-item <?= $activeNav === 'keyboard' ? 'active' : '' ?>"
            title="چیدمان دکمه‌های ربات">
            <span class="nav-icon"><?= icon('settings') ?></span><span
              class="nav-label">چیدمان دکمه‌ها</span>
          </a>
          <a href="bottext.php" class="nav-item <?= $activeNav === 'bottext' ? 'active' : '' ?>"
            title="مدیریت پیام‌ها و متن‌های ربات">
            <span class="nav-icon"><?= icon('edit') ?></span><span
              class="nav-label">متن‌های ربات</span>
          </a>
        </div>
        <div class="nav-section">
          <div class="nav-heading">سیستم</div>
          <a href="settings.php" class="nav-item <?= $activeNav === 'settings' ? 'active' : '' ?>"
            title="تنظیمات سیستم و ربات">
            <span class="nav-icon"><?= icon('settings') ?></span><span
              class="nav-label">تنظیمات ربات</span>
          </a>
          <a href="logout.php" class="nav-item" title="خروج از پنل مدیریت">
            <span class="nav-icon"><?= icon('logout') ?></span><span
              class="nav-label">خروج از حساب</span>
          </a>
        </div>
      </nav>
      <div class="sidebar-foot">
        <div class="user-pill">
          <div class="user-mono"><?= htmlspecialchars($initials) ?></div>
          <div class="user-info">
            <div class="uname"><?= htmlspecialchars($currentUser) ?></div>
            <div class="urole">مدیر سامانه</div>
          </div>
        </div>
      </div>
    </aside>

    <div class="main">
      <header class="topbar">
        <div class="topbar-left">
          <button class="icon-btn menu-toggle" onclick="openSidebar()"><?= icon('menu', 18) ?></button>
          <button class="icon-btn sb-toggle" onclick="toggleSidebar()"><?= icon('menu', 17) ?></button>
          <div>
            <div class="topbar-title"><?= htmlspecialchars($pageTitle) ?></div>
            <div class="crumb"><span>ZORVEX PRO</span><span
                style="opacity:.4;margin:0 3px">/</span><span><?= htmlspecialchars($pageTitle) ?></span></div>
          </div>
        </div>
        <div class="topbar-tools">
          <span class="badge-v002" style="font-size: 0.72rem; padding: 4px 10px; background: rgba(6, 182, 212, 0.15); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 99px; font-weight: 700; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;">● v0.0.2 Pro</span>
          <a href="settings.php" class="icon-btn"
            title="تنظیمات ربات"><?= icon('settings', 16) ?></a>
          <a href="logout.php" class="icon-btn"
            title="خروج از حساب"><?= icon('logout', 16) ?></a>
        </div>
      </header>
      <main class="content">
        <?php
        $s = get_flash('success');
        $e = get_flash('error');
        $w = get_flash('warning');
        if ($s): ?>
          <div class="notice notice-ok"><?= htmlspecialchars($s) ?></div><?php endif;
        if ($e): ?>
          <div class="notice notice-no"><?= htmlspecialchars($e) ?></div><?php endif;
        if ($w): ?>
          <div class="notice notice-warn"><?= htmlspecialchars($w) ?></div><?php endif;
        if ($showPageHead): ?>
          <div class="page-head fade-up">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <?php if ($pageLede): ?>
              <p><?= htmlspecialchars($pageLede) ?></p><?php endif; ?>
          </div>
        <?php endif; ?>