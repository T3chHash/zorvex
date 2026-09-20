<?php


if (!function_exists('faoxima_schema_table_exists')) {


function faoxima_schema_table_exists(PDO $pdo, string $table): bool {
    try {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t LIMIT 1"
        );
        $stmt->execute([':t' => $table]);
        return (bool)$stmt->fetchColumn();
    } catch (\Throwable $e) {
        error_log('[schema] table_exists ' . $table . ': ' . $e->getMessage());
        return false;
    }
}


function faoxima_schema_column_exists(PDO $pdo, string $table, string $column): bool {
    try {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = :t
               AND COLUMN_NAME  = :c LIMIT 1"
        );
        $stmt->execute([':t' => $table, ':c' => $column]);
        return (bool)$stmt->fetchColumn();
    } catch (\Throwable $e) {
        error_log('[schema] column_exists ' . $table . '.' . $column . ': ' . $e->getMessage());
        return false;
    }
}


function faoxima_schema_ensure_column(PDO $pdo, string $table, string $column, string $definition): void {
    if (!faoxima_schema_table_exists($pdo, $table)) {

        return;
    }
    if (faoxima_schema_column_exists($pdo, $table, $column)) {
        return;
    }
    try {
        $sanT = preg_replace('/[^A-Za-z0-9_]/', '', $table);
        $sanC = preg_replace('/[^A-Za-z0-9_]/', '', $column);
        $pdo->exec("ALTER TABLE `{$sanT}` ADD COLUMN `{$sanC}` {$definition}");
    } catch (\PDOException $e) {

        if (!empty($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1060) return;
        error_log('[schema] ensure_column ' . $table . '.' . $column . ': ' . $e->getMessage());
    } catch (\Throwable $e) {
        error_log('[schema] ensure_column ' . $table . '.' . $column . ': ' . $e->getMessage());
    }
}


function faoxima_schema_ready(PDO $pdo): void {
    if (!empty($_SESSION['__faoxima_schema_ok'])) {
        return;
    }


    faoxima_schema_ensure_column(
        $pdo, 'marzban_panel', 'national_net_status',
        "VARCHAR(50) NOT NULL DEFAULT 'off_national_net'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'marzban_panel', 'stock_source_panel',
        "VARCHAR(191) NULL"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'redis_enabled',
        "VARCHAR(20) NOT NULL DEFAULT '0'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'receipt_topic_reporting',
        "VARCHAR(10) NOT NULL DEFAULT '1'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'subscription_link_button',
        "VARCHAR(10) NOT NULL DEFAULT '1'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'PublicLog_Status',
        "VARCHAR(10) NOT NULL DEFAULT '0'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'PublicLog_Channel',
        "VARCHAR(255) NULL"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'PublicLog_NewSub',
        "VARCHAR(10) NOT NULL DEFAULT '1'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'PublicLog_Renewal',
        "VARCHAR(10) NOT NULL DEFAULT '1'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'PublicLog_VolumeTopup',
        "VARCHAR(10) NOT NULL DEFAULT '1'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'PublicLog_TimeExtra',
        "VARCHAR(10) NOT NULL DEFAULT '1'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'PublicLog_WalletDeposit',
        "VARCHAR(10) NOT NULL DEFAULT '1'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'banner_start_status',
        "VARCHAR(10) NOT NULL DEFAULT 'off'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'banner_start_file_id',
        "VARCHAR(255) NULL"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'banner_cart_status',
        "VARCHAR(10) NOT NULL DEFAULT 'off'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'banner_cart_file_id',
        "VARCHAR(255) NULL"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'banner_buy_status',
        "VARCHAR(10) NOT NULL DEFAULT 'off'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'banner_buy_file_id',
        "VARCHAR(255) NULL"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'keyboardmain',
        "LONGTEXT NULL"
    );
    faoxima_schema_ensure_column(
        $pdo, 'setting', 'text_edit',
        "LONGTEXT NULL"
    );

    // Product table columns
    faoxima_schema_ensure_column(
        $pdo, 'product', 'position',
        "INT NOT NULL DEFAULT 0"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'ip_limit',
        "VARCHAR(10) NOT NULL DEFAULT '0'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'hwid_limit',
        "VARCHAR(10) NOT NULL DEFAULT '0'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'symbolic_limit_enabled',
        "VARCHAR(10) NOT NULL DEFAULT '0'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'symbolic_limit_users',
        "VARCHAR(10) NOT NULL DEFAULT '0'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'category',
        "VARCHAR(255) NOT NULL DEFAULT ''"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'hide_panel',
        "TEXT NULL"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'note',
        "TEXT NULL"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'agent',
        "VARCHAR(50) NOT NULL DEFAULT 'f'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'data_limit_reset',
        "VARCHAR(50) NOT NULL DEFAULT '0'"
    );
    faoxima_schema_ensure_column(
        $pdo, 'product', 'one_buy_status',
        "VARCHAR(10) NOT NULL DEFAULT '0'"
    );

    if (!faoxima_schema_table_exists($pdo, 'category')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS category (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                remark VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin");
        } catch (\Throwable $e) {
            error_log('[schema] category: ' . $e->getMessage());
        }
    }

    if (!faoxima_schema_table_exists($pdo, 'PaySetting')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS PaySetting (
                NamePay VARCHAR(191) PRIMARY KEY NOT NULL,
                ValuePay LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $payDefaults = [
                'Cartstatus' => 'oncard',
                'CartDirect' => '@support',
                'cardnumber' => '603700000000',
                'namecard' => 'مدیریت',
                'Cartstatuspv' => 'offcardpv',
                'nowpaymentstatus' => 'offnowpayment',
                'zarinpalstatus' => 'offzarinpal',
                'merchant_zarinpal' => '0',
                'statusiranpay3' => 'offiranpay3',
                'apiiranpay' => '0',
                'statusstar' => '0',
                'minbalance' => '20000',
                'maxbalance' => '1000000',
                'minbalancecart' => '20000',
                'maxbalancecart' => '1000000',
                'helpcart' => '2',
                'chashbackcart' => '0',
            ];
            $pSeed = $pdo->prepare("INSERT IGNORE INTO PaySetting (NamePay, ValuePay) VALUES (?, ?)");
            foreach ($payDefaults as $k => $v) {
                $pSeed->execute([$k, $v]);
            }
        } catch (\Throwable $e) {
            error_log('[schema] PaySetting: ' . $e->getMessage());
        }
    }

    if (!faoxima_schema_table_exists($pdo, 'nm_stock_shelves')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS nm_stock_shelves (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(191) NOT NULL,
                source_codepanel VARCHAR(100) NOT NULL,
                stock_codepanel VARCHAR(100) NOT NULL,
                codeproduct VARCHAR(100) NOT NULL,
                product_name VARCHAR(255) NULL,
                volume_gb DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                service_days INT(11) NOT NULL DEFAULT 0,
                price BIGINT(20) NOT NULL DEFAULT 0,
                status VARCHAR(50) NOT NULL DEFAULT 'active',
                created_at INT(11) NULL,
                KEY idx_source (source_codepanel),
                KEY idx_stock (stock_codepanel),
                KEY idx_product (codeproduct)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            error_log('[schema] nm_stock_shelves: ' . $e->getMessage());
        }
    }

    if (!faoxima_schema_table_exists($pdo, 'nm_config_stock')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS nm_config_stock (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                shelf_id INT(11) UNSIGNED NOT NULL,
                codepanel VARCHAR(100) NULL,
                codeproduct VARCHAR(100) NULL,
                tier VARCHAR(50) NOT NULL DEFAULT 'auto',
                format VARCHAR(50) NOT NULL DEFAULT 'single',
                content LONGTEXT NULL,
                sub_link TEXT NULL,
                status VARCHAR(50) NOT NULL DEFAULT 'active',
                assigned_user VARCHAR(100) NOT NULL DEFAULT '',
                assigned_invoice VARCHAR(100) NOT NULL DEFAULT '',
                assigned_mode VARCHAR(100) NOT NULL DEFAULT '',
                reserved_at INT(11) NULL,
                delivered_at INT(11) NULL,
                created_at INT(11) NULL,
                KEY idx_shelf (shelf_id),
                KEY idx_status (status),
                KEY idx_assigned_user (assigned_user),
                KEY idx_assigned_invoice (assigned_invoice)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            error_log('[schema] nm_config_stock: ' . $e->getMessage());
        }
    }

    if (!faoxima_schema_table_exists($pdo, 'textbot')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS textbot (
                id_text VARCHAR(191) PRIMARY KEY NOT NULL,
                text LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            error_log('[schema] textbot: ' . $e->getMessage());
        }
    }

    // Seed default bot buttons and standard texts so bot buttons & admin panel are always fully populated
    try {
        $textbotDefaults = [
            'text_sell'               => '🛍 خرید اشتراک',
            'text_Purchased_services' => '📋 سرویس‌های من',
            'text_extend'             => '🔄 تمدید سرویس',
            'text_usertest'           => '🎁 اکانت تست رایگان',
            'text_wheel_luck'         => '🎡 گردونه شانس',
            'accountwallet'           => '💳 کیف پول و شارژ',
            'text_Add_Balance'        => '➕ افزایش موجودی',
            'text_Tariff_list'        => '📊 تعرفه اشتراک‌ها',
            'text_dec_Tariff_list'    => 'تعرفه‌های اشتراک ما به شرح زیر است:',
            'text_support'            => '☎️ پشتیبانی',
            'text_help'               => '📚 راهنما و آموزش',
            'text_fq'                 => '❓ سوالات متداول',
            'text_dec_fq'             => 'سوالات متداول شما در اینجا پاسخ داده شده است.',
            'text_affiliates'         => '👥 زیرمجموعه‌گیری',
            'text_start'              => "سلام خوش آمدید\nبرای استفاده از امکانات ربات یکی از گزینه‌های زیر را انتخاب نمایید:",
            'text_roll'               => "♨️ قوانین استفاده از خدمات ما\n\n1- به اطلاعیه‌های کانال دقت فرمایید.\n2- در صورت هرگونه سوال به پشتیبانی پیام دهید.",
            'text_channel'            => '📢 کانال اطلاع‌رسانی',
            'text_pishinvoice'        => "🧾 پیش‌فاکتور خرید سرویس\n\nبرای نهایی‌سازی سفارش روش پرداخت را انتخاب فرمایید.",
            'textafterpay'            => "✅ پرداخت شما با موفقیت تایید شد.\nاطلاعات اشتراک برای شما ارسال گردید.",
            'textaftertext'           => "🎁 اشتراک تست شما ایجاد شد.\nامیدواریم از کیفیت و سرعت لذت ببرید.",
            'text_Discount'           => '🎁 کد تخفیف',
            'carttocart'              => '💳 کارت به کارت',
        ];
        $tSeed = $pdo->prepare("INSERT IGNORE INTO textbot (id_text, text) VALUES (?, ?)");
        foreach ($textbotDefaults as $k => $v) {
            $tSeed->execute([$k, $v]);
        }
    } catch (\Throwable $e) {
        error_log('[schema] textbot seed: ' . $e->getMessage());
    }

    if (!faoxima_schema_table_exists($pdo, 'x_ui')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS x_ui (
                codepanel VARCHAR(100) NOT NULL,
                setting LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                protocol VARCHAR(100) DEFAULT NULL,
                PRIMARY KEY (codepanel)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            error_log('[schema] x_ui: ' . $e->getMessage());
        }
    }


    if (!faoxima_schema_table_exists($pdo, 'crypto_wallets')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS crypto_wallets (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                currency VARCHAR(20) NOT NULL,
                network VARCHAR(20) NOT NULL,
                wallet_address VARCHAR(255) NOT NULL DEFAULT '',
                label VARCHAR(255) DEFAULT NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 0,
                min_irt BIGINT NOT NULL DEFAULT 20000,
                max_irt BIGINT NOT NULL DEFAULT 100000000,
                cashback_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
                rate_irt_override DECIMAL(20,4) DEFAULT NULL,
                verification_mode ENUM('automated','manual') NOT NULL DEFAULT 'automated',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_currency (currency)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $seed = $pdo->prepare(
                "INSERT IGNORE INTO crypto_wallets (currency, network, wallet_address, label, enabled)
                 VALUES (?,?,?,?,0)"
            );
            foreach ([
                ['TRX',         'TRON', '', 'ترون (TRX)'],
                ['TON',         'TON',  '', 'تون (TON)'],
                ['USDT_TRC20',  'TRON', '', 'تتر روی شبکه ترون (USDT-TRC20)'],
                ['USDT_TON',    'TON',  '', 'تتر روی شبکه تون (USDT-TON)'],
            ] as $r) $seed->execute($r);
        } catch (\Throwable $e) {
            error_log('[schema] crypto_wallets: ' . $e->getMessage());
        }
    }
    faoxima_schema_ensure_column(
        $pdo, 'crypto_wallets', 'verification_mode',
        "ENUM('automated','manual') NOT NULL DEFAULT 'automated'"
    );


    if (!faoxima_schema_table_exists($pdo, 'app')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS app (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                link VARCHAR(500) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            error_log('[schema] app: ' . $e->getMessage());
        }
    }


    if (!faoxima_schema_table_exists($pdo, 'shopSetting')) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS shopSetting (
                Namevalue VARCHAR(500) PRIMARY KEY NOT NULL,
                value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");

            $seed = $pdo->prepare(
                "INSERT IGNORE INTO shopSetting (Namevalue, value) VALUES (?, ?)"
            );
            foreach ([
                ['statusextra',         'offextra'],
                ['statusdirectpabuy',   'ondirectbuy'],
                ['statustimeextra',     'ontimeextraa'],
                ['statusdisorder',      'offdisorder'],
                ['statuschangeservice', 'onstatus'],
                ['statusshowprice',     'offshowprice'],
                ['configshow',          'onconfig'],
                ['backserviecstatus',   'on'],
                ['minbalancebuybulk',   '0'],
                ['customvolmef',        '4000'],
                ['customvolmen',        '4000'],
                ['customvolmen2',       '4000'],
                ['customtimepricef',    '4000'],
                ['customtimepricen',    '4000'],
                ['customtimepricen2',   '4000'],
            ] as $r) $seed->execute($r);
        } catch (\Throwable $e) {
            error_log('[schema] shopSetting: ' . $e->getMessage());
        }
    }


    $_SESSION['__faoxima_schema_ok'] = true;
}

}

if (!function_exists('zorvex_bust_bot_selectcache')) {
    function zorvex_bust_bot_selectcache($table = null)
    {
        if (function_exists('clearSelectCache')) {
            clearSelectCache($table);
        }
    }
}
if (!function_exists('faoxima_bust_bot_selectcache')) {
    function faoxima_bust_bot_selectcache($table = null)
    {
        zorvex_bust_bot_selectcache($table);
    }
}

if (!function_exists('zorvex_textbot_get')) {
    function zorvex_textbot_get($key, $default = '')
    {
        static $cache = null;
        global $pdo;
        if ($cache === null) {
            $cache = [];
            try {
                if (isset($pdo) && $pdo instanceof PDO) {
                    $stmt = $pdo->query("SELECT id_text, text FROM textbot");
                    if ($stmt) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $cache[(string)$row['id_text']] = (string)$row['text'];
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log('[zorvex_textbot_get] fetch error: ' . $e->getMessage());
            }
        }
        if (isset($cache[$key]) && $cache[$key] !== '') {
            return $cache[$key];
        }
        return $default;
    }
}
if (!function_exists('faoxima_textbot_get')) {
    function faoxima_textbot_get($key, $default = '')
    {
        return zorvex_textbot_get($key, $default);
    }
}

if (!function_exists('zorvex_render_text')) {
    function zorvex_render_text($template, array $vars = [])
    {
        if (!is_string($template)) {
            $template = (string)$template;
        }
        foreach ($vars as $k => $v) {
            $template = str_replace('{' . $k . '}', (string)$v, $template);
        }
        return $template;
    }
}
if (!function_exists('faoxima_render_text')) {
    function faoxima_render_text($template, array $vars = [])
    {
        return zorvex_render_text($template, $vars);
    }
}

if (!function_exists('zorvex_symbolic_limit_label')) {
    function zorvex_symbolic_limit_label($row)
    {
        if (!is_array($row)) {
            return null;
        }
        $count = (int)($row['symbolic_limit_users'] ?? $row['symbolic_limit'] ?? 0);
        if ($count > 0) {
            return "{$count} کاربر";
        }
        return null;
    }
}
if (!function_exists('faoxima_symbolic_limit_label')) {
    function faoxima_symbolic_limit_label($row)
    {
        return zorvex_symbolic_limit_label($row);
    }
}

if (!function_exists('zorvex_cap_ip_summary')) {
    function zorvex_cap_ip_summary($summary, $limit = 0)
    {
        if (!is_array($summary) || empty($summary['raw']) || $limit <= 0) {
            return $summary;
        }
        if (count($summary['raw']) > $limit) {
            $summary['raw'] = array_slice($summary['raw'], 0, $limit);
            $summary['count'] = count($summary['raw']);
        }
        return $summary;
    }
}
if (!function_exists('faoxima_cap_ip_summary')) {
    function faoxima_cap_ip_summary($summary, $limit = 0)
    {
        return zorvex_cap_ip_summary($summary, $limit);
    }
}

if (!function_exists('zorvex_apply_curl_proxy')) {
    function zorvex_apply_curl_proxy($ch, $context = 'telegram')
    {
        if (!is_resource($ch) && !($ch instanceof \CurlHandle)) {
            return;
        }
    }
}
if (!function_exists('faoxima_apply_curl_proxy')) {
    function faoxima_apply_curl_proxy($ch, $context = 'telegram')
    {
        zorvex_apply_curl_proxy($ch, $context);
    }
}

if (!function_exists('zorvex_public_purchase_log_event')) {
    function zorvex_public_purchase_log_event($eventType, array $data, array $setting = null)
    {
        if ($setting === null) {
            if (function_exists('select')) {
                $setting = select("setting", "*", null, null);
            }
        }
        if (empty($setting) || empty($setting['PublicLog_Status']) || (string)$setting['PublicLog_Status'] !== '1') {
            return;
        }
        $channel = trim((string)($setting['PublicLog_Channel'] ?? ''));
        if ($channel === '') {
            return;
        }

        $map = [
            'new_sub'        => ['col' => 'PublicLog_NewSub',        'title' => '🛍 خرید اشتراک جدید'],
            'renewal'        => ['col' => 'PublicLog_Renewal',       'title' => '🔄 تمدید سرویس'],
            'volume_topup'   => ['col' => 'PublicLog_VolumeTopup',   'title' => '➕ افزایش حجم سرویس'],
            'time_extra'     => ['col' => 'PublicLog_TimeExtra',     'title' => '⏳ افزایش زمان سرویس'],
            'wallet_deposit' => ['col' => 'PublicLog_WalletDeposit', 'title' => '💳 شارژ کیف پول'],
        ];

        if (!isset($map[$eventType])) {
            return;
        }
        $cfg = $map[$eventType];
        if (!empty($cfg['col']) && empty($setting[$cfg['col']])) {
            return;
        }

        $userId = $data['user_id'] ?? $data['from_id'] ?? 'کاربر';
        $maskedUser = (string)$userId;
        if (strlen($maskedUser) > 4) {
            $maskedUser = substr($maskedUser, 0, 2) . '***' . substr($maskedUser, -2);
        }
        $amount = $data['amount'] ?? '';
        $price = $data['price'] ?? '';
        $panelName = $data['panel_name'] ?? '';

        $msg = "<b>" . $cfg['title'] . "</b>\n\n";
        $msg .= "👤 <b>کاربر:</b> <code>" . htmlspecialchars($maskedUser, ENT_QUOTES, 'UTF-8') . "</code>\n";
        if ($amount !== '') {
            $msg .= "📦 <b>پلن:</b> " . htmlspecialchars((string)$amount, ENT_QUOTES, 'UTF-8') . "\n";
        }
        if ($price !== '') {
            $msg .= "💰 <b>مبلغ:</b> " . htmlspecialchars((string)$price, ENT_QUOTES, 'UTF-8') . " تومان\n";
        }
        if ($panelName !== '') {
            $msg .= "🌐 <b>سرور:</b> " . htmlspecialchars((string)$panelName, ENT_QUOTES, 'UTF-8') . "\n";
        }
        $msg .= "⏰ <b>زمان:</b> " . (function_exists('jdate') ? jdate('Y/m/d H:i:s') : date('Y-m-d H:i:s')) . "\n\n";
        $msg .= "🔒 <i>خرید موفق در زوروکس پرو</i>";

        if (function_exists('telegram')) {
            telegram('sendmessage', [
                'chat_id'    => $channel,
                'text'       => $msg,
                'parse_mode' => 'HTML',
            ]);
        }
    }
}
if (!function_exists('faoxima_public_purchase_log_event')) {
    function faoxima_public_purchase_log_event($eventType, array $data, array $setting = null)
    {
        zorvex_public_purchase_log_event($eventType, $data, $setting);
    }
}

