<?php

if (!defined('FAOXIMA_SKIP_BOTAPI_ROUTER')) {
    define('FAOXIMA_SKIP_BOTAPI_ROUTER', true);
}


register_shutdown_function(static function () {
    $err = error_get_last();
    if (!$err) return;
    $fatal = [E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR];
    if (!in_array($err['type'], $fatal, true)) return;

    while (ob_get_level() > 0) { @ob_end_clean(); }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    $msg = htmlspecialchars(
        $err['message'] . ' @ ' . basename((string)$err['file']) . ':' . (int)$err['line'],
        ENT_QUOTES, 'UTF-8'
    );
    echo '<!DOCTYPE html><html lang="fa" dir="rtl"><meta charset="utf-8">'
       . '<title>خطای سرور</title>'
       . '<body style="font-family:sans-serif;background:#0a0a0f;color:#f1f3f8;padding:32px;">'
       . '<h2>خطای داخلی سرور</h2><pre style="white-space:pre-wrap">' . $msg . '</pre>'
       . '</body></html>';
});

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 86400 * 30,
        'path'     => '/',
        'domain'   => '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/lib/icons.php';
require_once __DIR__ . '/../function.php';
require_once __DIR__ . '/../botapi.php';
require_once __DIR__ . '/../jdf.php';

$user_ip = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '';
if (str_contains($user_ip, ',')) {
    $user_ip = trim(explode(',', $user_ip)[0]);
}

$texterrr = "";
$ip_denied = false;

if (isset($_POST['login'])) {
    $username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
    $passwordRaw = isset($_POST['password']) ? (string)$_POST['password'] : '';
    $password = trim($passwordRaw);

    if ($username !== '' && ($password !== '' || $passwordRaw !== '')) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? OR id_admin = ?");
            $stmt->execute([$username, $username]);
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $candidates = [];
        }

        // If no match found by username/id_admin, and there is only 1 admin in the entire table, test that admin
        if (empty($candidates)) {
            try {
                $allQ = $pdo->query("SELECT * FROM admin");
                $allRows = $allQ ? $allQ->fetchAll(PDO::FETCH_ASSOC) : [];
                if (count($allRows) === 1) {
                    $candidates = $allRows;
                }
            } catch (\Throwable $e) {
                $candidates = [];
            }
        }

        $result = null;
        $passwordOk = false;
        $passInputs = array_values(array_unique([$password, $passwordRaw]));

        foreach ($candidates as $candidate) {
            $storedPass = (string)($candidate["password"] ?? '');
            $storedHash = (string)($candidate["password_hash"] ?? '');

            foreach ($passInputs as $pInput) {
                if ($pInput === '') continue;

                // 1. Check if `password` column is a bcrypt / argon2 hash
                if ($storedPass !== '') {
                    if (str_starts_with($storedPass, '$2') || str_starts_with($storedPass, '$argon2')) {
                        if (password_verify($pInput, $storedPass)) {
                            $passwordOk = true;
                            $result = $candidate;
                            break 2;
                        }
                    } else {
                        // Plain text check
                        if (hash_equals($storedPass, $pInput)) {
                            $passwordOk = true;
                            $result = $candidate;
                            break 2;
                        }
                        // MD5 check
                        if (hash_equals($storedPass, md5($pInput)) || hash_equals(strtolower($storedPass), md5($pInput))) {
                            $passwordOk = true;
                            $result = $candidate;
                            break 2;
                        }
                        // SHA256 check
                        if (hash_equals($storedPass, hash('sha256', $pInput))) {
                            $passwordOk = true;
                            $result = $candidate;
                            break 2;
                        }
                    }
                }

                // 2. Check if `password_hash` column is a bcrypt / argon2 hash
                if ($storedHash !== '') {
                    if (str_starts_with($storedHash, '$2') || str_starts_with($storedHash, '$argon2')) {
                        if (password_verify($pInput, $storedHash)) {
                            $passwordOk = true;
                            $result = $candidate;
                            break 2;
                        }
                    } else {
                        if (hash_equals($storedHash, $pInput)) {
                            $passwordOk = true;
                            $result = $candidate;
                            break 2;
                        }
                    }
                }

                // 3. Fallback generic password_verify
                if ($storedPass !== '') {
                    try {
                        if (@password_verify($pInput, $storedPass)) {
                            $passwordOk = true;
                            $result = $candidate;
                            break 2;
                        }
                    } catch (\Throwable $e) {}
                }
                if ($storedHash !== '') {
                    try {
                        if (@password_verify($pInput, $storedHash)) {
                            $passwordOk = true;
                            $result = $candidate;
                            break 2;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        $adminIpOk = true;
        if ($result) {
            $rawAdminIp = $result['iplogin'] ?? null;
            if ($rawAdminIp !== null && $rawAdminIp !== '') {
                $adminIpDecoded = json_decode((string)$rawAdminIp, true);
                if (is_array($adminIpDecoded)) {
                    if (in_array('*', $adminIpDecoded, true) || in_array('all', $adminIpDecoded, true) || in_array('unlimited', $adminIpDecoded, true)) {
                        $adminIpOk = true;
                    } else {
                        $adminIpOk = in_array($user_ip, $adminIpDecoded, true);
                    }
                } elseif ($rawAdminIp === '*' || $rawAdminIp === 'all' || $rawAdminIp === 'unlimited') {
                    $adminIpOk = true;
                } elseif (filter_var($rawAdminIp, FILTER_VALIDATE_IP)) {
                    $adminIpOk = ($rawAdminIp === $user_ip);
                }
            }
        }

        if (empty($candidates)) {
            $texterrr = 'نام کاربری یا رمزعبور وارد شده اشتباه است!';
        } elseif (!$passwordOk) {
            $texterrr = 'رمز صحیح نمی باشد';
        } elseif (!$adminIpOk) {
            http_response_code(403);
            $ip_denied = true;
        } else {
            session_regenerate_id(true);
            $authUsername = trim((string)($result["username"] ?? ''));
            if ($authUsername === '') {
                $authUsername = !empty($result["id_admin"]) ? (string)$result["id_admin"] : 'admin';
                try {
                    $pdo->prepare("UPDATE admin SET username = :u WHERE id_admin = :id")->execute([
                        ':u' => $authUsername,
                        ':id' => $result["id_admin"]
                    ]);
                } catch (\Throwable $e) {}
            }
            $_SESSION["user"] = $authUsername;
            $_SESSION["admin_user"] = $authUsername;
            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_id"] = (string)($result["id_admin"] ?? '');
            $_SESSION["rule"] = (string)($result["rule"] ?? 'administrator');
            $_SESSION["_session_regenerated"] = true;

            // Commit session to storage before redirect
            session_write_close();

            header('Location: index.php', true, 302);
            exit;
        }
    } else {
        $texterrr = 'نام کاربری یا رمز عبور خالی است.';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="dark" data-color="blue">
<head>
    <script>
    (function(){try{var t=localStorage.getItem('faoxima_theme');
    if(t!=='light'&&t!=='dark')t='dark';
    document.documentElement.setAttribute('data-theme',t);
    var c=localStorage.getItem('faoxima_color');
    if(c)document.documentElement.setAttribute('data-color',c);}catch(e){}})();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>ورود به پنل مدیریت | زوروکس پرو</title>
    <link rel="stylesheet" href="css/theme.css?v=flat47">
<script src="js/theme.js?v=flat5" defer>

</script>
</head>
<body class="login-page">

<?php if ($ip_denied): ?>
    <div class="ip-card">
        <span style="font-size:48px; color: var(--accent);"><?php echo icon('shield-halved', 'svg-icon'); ?></span>
        <h2>دسترسی غیرمجاز</h2>
        <p>IP فعلی شما در لیست IPهای مجاز این حساب قرار ندارد.<br>برای ورود، از IP مجاز استفاده کنید یا تنظیمات IP این حساب را از طریق ربات مدیریت کنید.</p>
        <div class="ip-box"><?php echo htmlspecialchars($user_ip, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
<?php else: ?>

    <div class="login-card">
        <div class="login-terminal-bar">
            <span class="terminal__lights"><i></i><i></i><i></i></span>
        </div>

        <div class="login-body">
            <h2>پنل مدیریت زوروکس پرو</h2>
            <p>برای ادامه، اطلاعات حساب خود را وارد کنید.</p>

            <?php if (!empty($texterrr)): ?>
                <div class="alert alert-error">
                    <?php echo icon('circle-exclamation', 'svg-icon'); ?>
                    <span><?php echo htmlspecialchars($texterrr, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label class="form-label">نام کاربری</label>
                    <div class="input-icon-wrap">
                        <?php echo icon('user', 'svg-icon'); ?>
                        <input type="text" name="username" class="form-control" placeholder="نام کاربری..." required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">رمز عبور</label>
                    <div class="input-icon-wrap">
                        <?php echo icon('lock', 'svg-icon'); ?>
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="toggle-pass" onclick="togglePass()" aria-label="نمایش رمز">
                            <span id="eye-icon"><?php echo icon('eye', 'svg-icon'); ?></span>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary btn-block mt-2">
                    <?php echo icon('arrow-left', 'svg-icon'); ?>
                    ورود به پنل
                </button>
            </form>

            <p class="text-muted mt-2" style="font-size:11px;">
                <?php echo icon('circle-info', 'svg-icon'); ?>
                IP شما: <span style="direction:ltr;"><?php echo htmlspecialchars($user_ip, ENT_QUOTES, 'UTF-8'); ?></span>
            </p>

            
            <div class="login-social" style="display:flex; gap:10px; justify-content:center; margin-top:18px; padding-top:14px; border-top:1px solid var(--border-soft);">
                <a href="https://t.me/zorvexpanel" target="_blank" rel="noopener noreferrer"
                   aria-label="کانال تلگرام زوروکس پرو"
                   title="کانال تلگرام زوروکس پرو"
                   style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:var(--accent-soft, rgba(59,130,246,0.12)); color:var(--accent, #3b82f6); transition:transform .15s ease, background .15s ease;"
                   onmouseover="this.style.transform='translateY(-2px)';"
                   onmouseout="this.style.transform='translateY(0)';">
                    <?php echo icon('telegram', 'svg-icon'); ?>
                </a>
                <a href="https://github.com/T3chHash/zorvex" target="_blank" rel="noopener noreferrer"
                   aria-label="مخزن گیت‌هاب زوروکس"
                   title="مخزن گیت‌هاب زوروکس"
                   style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:var(--accent-soft, rgba(59,130,246,0.12)); color:var(--accent, #3b82f6); transition:transform .15s ease, background .15s ease;"
                   onmouseover="this.style.transform='translateY(-2px)';"
                   onmouseout="this.style.transform='translateY(0)';">
                    <?php echo icon('github', 'svg-icon'); ?>
                </a>
            </div>

            <p class="text-muted" style="text-align:center; font-size:11px; margin-top:14px; direction:ltr; font-family:'JetBrains Mono',monospace;">
                <?php
                    $__loginVer = trim((string)@file_get_contents(__DIR__ . '/../version'));
                    if ($__loginVer === '') $__loginVer = '0.0.2';
                    echo 'v' . htmlspecialchars(ltrim($__loginVer, 'vV'), ENT_QUOTES, 'UTF-8');
                ?>
            </p>
        </div>
    </div>

<?php endif; ?>

<script>
    var SVG_EYE       = '<svg class="svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    var SVG_EYE_SLASH = '<svg class="svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    function togglePass() {
        var inp = document.getElementById('passwordInput');
        var icn = document.getElementById('eye-icon');
        if (!inp || !icn) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            icn.innerHTML = SVG_EYE_SLASH;
        } else {
            inp.type = 'password';
            icn.innerHTML = SVG_EYE;
        }
    }
</script>
</body>
</html>


