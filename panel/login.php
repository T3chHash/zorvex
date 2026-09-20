<?php
session_start();

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/icons.php';

if (!empty($_SESSION['admin_user'])) {
  header('Location: index.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

  if (!csrf_check_value($_POST['_csrf'] ?? '')) {
    $error = $textbotlang['panel']['loginWrongCredentials'];
  } elseif ($username === '' || $password === '') {
    $error = $textbotlang['panel']['loginEnterCredentials'];
  } elseif (!check_login_rate($ip)) {

    $error = $textbotlang['panel']['loginTooManyAttempts'];
    error_log("Login rate limit hit for IP: $ip username: $username");
  } else {

    $admin = select("admin", "*", "username", $username, "select");

    $dummyHash = '$2y$10$dummy.hash.for.timing.attack.prevention.xxxxxxxxxxxxxxxx';
    $storedHash = $admin ? (string) $admin['password'] : $dummyHash;

    $isCorrect = false;
    $storedIsHash = str_starts_with($storedHash, '$2') || str_starts_with($storedHash, '$argon2');
    if ($storedIsHash) {
      $isCorrect = password_verify($password, $storedHash);
    } elseif ($admin) {
      $isCorrect = hash_equals($storedHash, $password);
    }

    if ($isCorrect && $admin) {

      if (!str_starts_with($admin['password'], '$2')) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        update("admin", "password", $hash, "username", $username);
      }
      clear_login_rate($ip);
      session_regenerate_id(true);
      $_SESSION['admin_user'] = $admin['username'];
      $_SESSION['login_time'] = time();
      flash('success', $textbotlang['panel']['loginWelcomeBack'] . $admin['username']);
      header('Location: index.php');
      exit;
    } else {
      $error = $textbotlang['panel']['loginWrongCredentials'];
      error_log("Failed login for username: $username from IP: $ip");
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
  <meta name="theme-color" content="#0F172A" id="mtc">
  <title>ورود به پنل مدیریت | ZORVEX Enterprise</title>
  <link rel="stylesheet" href="css/style.css">
  <script>(function () { var t = localStorage.getItem('panel-theme') || 'navy'; document.documentElement.setAttribute('data-theme', t); var c = { navy: '#0F172A', purple: '#180D2E', emerald: '#0A1F1C', sunset: '#1A0D0D', slate: '#080808', light: '#F1F5F9', linen: '#FAF7F2', mint: '#F0FDF4', lavender: '#FAF5FF' }; var m = document.getElementById('mtc'); if (m && c[t]) m.content = c[t]; })();</script>
</head>

<body>
  <div class="auth">
    <aside class="auth-aside">
      <div class="auth-mark">
        <div class="dot">Z</div>
        <div style="display:flex; flex-direction:column; margin-right:8px;">
          <span style="font-weight:800; letter-spacing:-0.02em; font-size:1.3rem;">ZORVEX</span>
          <span style="font-size:0.72rem; color:var(--ac); font-weight:700; letter-spacing:0.12em;">ENTERPRISE PRO</span>
        </div>
      </div>
      <div class="auth-quote">
        <h2 style="font-size:1.35rem; font-weight:700; line-height:1.7; margin-bottom:12px;">پلتفرم هوشمند مدیریت و فروش اتوماتیک کانفیگ و اشتراک VPN</h2>
        <cite style="font-size:0.8rem; color:var(--dim); font-style:normal;">طراحی مدرن، سرعت فوق‌العاده و اتصال یکپارچه با انواع پروتکل‌ها و پنل‌ها</cite>
      </div>
      <div class="auth-foot">
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 10px #22c55e; display:inline-block;"></span>
          <span>نسخه پایدار v0.0.2 Pro — کلیه حقوق محفوظ است © <?= date('Y') ?></span>
        </div>
      </div>
    </aside>
    <main class="auth-main">
      <div class="auth-box" style="animation:fadeUp .5s ease-out">
        <div style="text-align:center; margin-bottom:24px;">
          <div style="width:56px; height:56px; border-radius:16px; background:var(--grad); color:#fff; font-size:1.7rem; font-weight:800; display:inline-grid; place-items:center; box-shadow:0 0 24px var(--acg); margin-bottom:12px;">Z</div>
          <h1 style="font-size:1.5rem; font-weight:800; margin-bottom:6px;">ورود به مدیریت Zorvex</h1>
          <p class="lede" style="margin-bottom:0; font-size:0.85rem; color:var(--mute);">جهت دسترسی به داشبورد، مشخصات مدیر را وارد نمایید</p>
        </div>
        <?php if ($error): ?>
          <div class="notice notice-no" style="margin-bottom:20px"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form class="auth-form" method="POST" autocomplete="on">
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
          <div class="field">
            <label for="username">نام کاربری ادمین</label>
            <input type="text" id="username" name="username" class="input" placeholder="admin"
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username" required autofocus
              maxlength="100">
          </div>
          <div class="field">
            <label for="password">رمز عبور امنیتی</label>
            <input type="password" id="password" name="password" class="input" placeholder="••••••••"
              autocomplete="current-password" required maxlength="200">
          </div>
          <button type="submit" class="btn btn-primary" id="loginBtn" style="width:100%; justify-content:center; padding:12px; font-size:0.95rem; margin-top:8px;">
            <span id="loginText">ورود به پنل مدیریت</span>
            <span id="loginSpin"
              style="display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
          </button>
        </form>
        <div class="auth-bottom" style="display:flex; justify-content:space-between; align-items:center; margin-top:24px; padding-top:16px; font-size:0.75rem; color:var(--dim);">
          <span>اتصال رمزگذاری‌شده SSL 🔒</span>
          <a href="https://github.com/T3chHash/zorvex" target="_blank" style="color:var(--ac); font-weight:600;">گیت‌هاب Zorvex ↗</a>
        </div>
      </div>
    </main>
  </div>
  <script src="js/login.js"></script>
</body>

</html>