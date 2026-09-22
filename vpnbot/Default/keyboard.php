<?php

if (!function_exists('faoxima_is_in_json_list')) {
    function faoxima_is_in_json_list($needle, $jsonOrArray): bool {
        if (empty($jsonOrArray)) {
            return false;
        }
        $data = is_array($jsonOrArray) ? $jsonOrArray : json_decode((string)$jsonOrArray, true);
        return is_array($data) && in_array($needle, $data);
    }
}

if (!function_exists('is_buy_command')) {
    function is_buy_command(?string $text, ?string $datain, array $textbotlang = []): bool {
        if (in_array($datain, ['buy', 'buybacktow', 'buyback'], true)) {
            return true;
        }
        $t = trim((string)$text);
        if ($t === '') {
            return false;
        }
        if (in_array($t, ['/buy', 'buy', '!buy', 'خرید', 'خرید اشتراک', 'خرید سرویس', '🛍 خرید اشتراک', '🔐 خرید اشتراک', '🛒 خرید اشتراک', 'خرید اشتراک 🛍', 'سفارش اشتراک', 'خرید سرویس جدید'], true)) {
            return true;
        }
        $configuredSell = trim((string)($textbotlang['textbot']['sell'] ?? ''));
        if ($configuredSell !== '' && ($t === $configuredSell || mb_stripos($t, $configuredSell) !== false)) {
            return true;
        }
        if (preg_match('/(خرید|سفارش)\s*(اشتراک|سرویس|کانفیگ)/u', $t)) {
            return true;
        }
        return false;
    }
}

$botinfo = select("botsaz", "*", "bot_token", $ApiToken, "select");
$userbot = select("user", "*", "id", $botinfo['id_user'], "select");
$hide_panel = !empty($botinfo['hide_panel']) ? json_decode($botinfo['hide_panel'], true) : [];
$text_bot_var =  json_decode(file_get_contents('text.json'), true);
// keyboard bot 
$keyboarddate = array(
    'text_sell' => $text_bot_var['btn_keyboard']['buy'],
    'text_usertest' => $text_bot_var['btn_keyboard']['test'],
    'text_Purchased_services' => $text_bot_var['btn_keyboard']['my_service'],
    'accountwallet' => $text_bot_var['btn_keyboard']['wallet'],
    'text_support' => $text_bot_var['btn_keyboard']['support'],
    'text_Admin' => "👨‍💼 پنل مدیریت",
);
$list_admin = select("botsaz", "*", "bot_token", $ApiToken, "select");
$admin_idsmain = select("admin", "id_admin", null, null, "FETCH_COLUMN");
$admin_ids_decoded = json_decode($list_admin['admin_ids'] ?? '[]', true);
if (!is_array($admin_ids_decoded)) {
    $admin_ids_decoded = [];
}

if (!is_array($admin_idsmain)) {
    $admin_idsmain = [];
}

if (!in_array($from_id, $admin_ids_decoded) && !in_array($from_id, $admin_idsmain)) {
    unset($keyboarddate['text_Admin']);
}
$keyboard = ['keyboard' => [], 'resize_keyboard' => true];
$tempArray = [];

foreach ($keyboarddate as $keyboardtext) {
    $tempArray[] = ['text' => $keyboardtext];
    if (count($tempArray) == 2) {
        $keyboard['keyboard'][] = $tempArray;
        $tempArray = [];
    }
}
if (count($tempArray) > 0) {
    $keyboard['keyboard'][] = $tempArray;
}
$keyboard  = json_encode($keyboard);

$backuser = json_encode([
    'keyboard' => [
        [['text' => "🏠 بازگشت به منوی اصلی"]]
    ],
    'resize_keyboard' => true,
    'input_field_placeholder' => "برای بازگشت روی دکمه زیر کلیک کنید"
]);

// keyboard list panel for test 

$userbotAgent = !empty($userbot['agent']) ? $userbot['agent'] : 'f';
$stmt = $pdo->prepare("SELECT * FROM marzban_panel WHERE TestAccount = 'ONTestAccount' AND (agent = :mp1 OR agent IN ('all', 'allusers', '') OR agent IS NULL OR FIND_IN_SET(:mp1, agent) > 0)");
$stmt->execute([':mp1' => $userbotAgent]);
$list_marzban_panel_usertest = ['inline_keyboard' => []];
while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (faoxima_is_in_json_list($from_id, $result['hide_user'] ?? null)) continue;
    if (faoxima_is_in_json_list($result['name_panel'], $hide_panel)) continue;
    $list_marzban_panel_usertest['inline_keyboard'][] = [
        ['text' => $result['name_panel'], 'callback_data' => "locationtest_{$result['code_panel']}"]
    ];
}
$list_marzban_panel_usertest['inline_keyboard'][] = [
    ['text' => "🏠 بازگشت به منوی اصلی", 'callback_data' => "backuser"],
];
$list_marzban_usertest = json_encode($list_marzban_panel_usertest);


$keyboardadmin = json_encode([
    'keyboard' => [
        [
            ['text' => "📊 آمار ربات"]
        ],
        [
            ['text' => "💰 تنظیمات فروشگاه"],
            ['text' => "⚙️ وضعیت قابلیت ها"],
        ],
        [
            ['text' => "🔍 جستجو کاربر"],
            ['text' => "👨‍🔧  مدیریت ادمین ها"]
        ],
        [
            ['text' => "📝 تنظیم متون"],
            ['text' => "🆕 آپدیت ربات"]
        ],
        [
            ['text' => "📞 تنظیم نام کاربری پشتیبانی"],
            ['text' => "📬 گزارش ربات"],
        ],
        [
            ['text' => "📣 جوین اجباری"]
        ],
        [
            ['text' => "🏠 بازگشت به منوی اصلی"]
        ],
    ],
    'resize_keyboard' =>  true
]);

$keyboardprice = json_encode([
    'keyboard' => [
        [
            ['text' => "🔋 قیمت حجم"],
            ['text' => "⌛️ قیمت زمان"],
        ],
        [
            ['text' => "💰 تنظیم قیمت محصول"],
            ['text' => "✏️ تنظیم نام محصول"],
        ],
        [
            ['text' => "بازگشت به منوی ادمین"]
        ],
    ],
    'resize_keyboard' =>  true
]);

$keyboard_change_price = json_encode([
    'keyboard' => [
        [
            ['text' => "💎 متن کارت"],
            ['text' => "🛍 دکمه خرید"]
        ],
        [
            ['text' => "🔑 دکمه تست"],
            ['text' => "🛒 دکمه سرویس های من"]
        ],
        [
            ['text' => "👤 دکمه حساب کاربری"],
            ['text' => "☎️ متن دکمه پشتیبانی"]
        ],
        [
            ['text' => "💸 متن مرحله افزایش موجودی"]
        ],
        [
            ['text' => "بازگشت به منوی ادمین"]
        ]
    ],
    'resize_keyboard' =>  true
]);

$backadmin = json_encode([
    'keyboard' => [
        [
            ['text' => "بازگشت به منوی ادمین"]
        ],
    ],
    'resize_keyboard' =>  true
]);

//------------------  [ listpanelusers ]----------------//
$userbotAgent = !empty($userbot['agent']) ? $userbot['agent'] : 'f';
$stmt = $pdo->prepare("SELECT * FROM marzban_panel WHERE status = 'active' AND (agent = :mp2 OR agent IN ('all', 'allusers', '') OR agent IS NULL OR FIND_IN_SET(:mp2, agent) > 0)");
$stmt->execute([':mp2' => $userbotAgent]);
$list_marzban_panel_users = ['inline_keyboard' => []];
while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (faoxima_is_in_json_list($from_id, $result['hide_user'] ?? null)) continue;
    if (faoxima_is_in_json_list($result['name_panel'], $hide_panel)) continue;
    $list_marzban_panel_users['inline_keyboard'][] = [
        ['text' => $result['name_panel'], 'callback_data' => "location_{$result['code_panel']}"]
    ];
}
$list_marzban_panel_users['inline_keyboard'][] = [
    ['text' => "🏠 بازگشت به منوی اصلی", 'callback_data' => "backuser"],
];
$list_marzban_panel_user = json_encode($list_marzban_panel_users);

$payment = json_encode([
    'inline_keyboard' => [
        [['text' => "💰 پرداخت و دریافت سرویس", 'callback_data' => "confirmandgetservice"]],
        [['text' => "🏠 بازگشت به منوی اصلی",  'callback_data' => "backuser"]]
    ]
]);
$KeyboardBalance = json_encode([
    'inline_keyboard' => [
        [['text' => "💸 افزایش موجودی", 'callback_data' => "AddBalance"]],
        [['text' => "🏠 بازگشت به منوی اصلی",  'callback_data' => "backuser"]]
    ]
]);

function KeyboardProduct($location, $query, $pricediscount, $datakeyboard, $statuscustom = false, $backuser = "backuser", $valuetow = null, $customvolume = "customsellvolume", $queryParams = [])
{
    global $pdo, $textbotlang;
    $product = ['inline_keyboard' => []];
    $statusshowprice = 'offshowprice';
    try {
        $showPriceRow = select("shopSetting", "*", "Namevalue", "statusshowprice", "select");
        if (is_array($showPriceRow) && isset($showPriceRow['value'])) {
            $statusshowprice = (string)$showPriceRow['value'];
        }
    } catch (\Throwable $e) {}

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($queryParams);
    } catch (\Throwable $e) {
        error_log("[KeyboardProduct default] query error: " . $e->getMessage());
        return json_encode([
            'inline_keyboard' => [
                [['text' => $textbotlang['users']['status']['backinfo'] ?? 'بازگشت', 'callback_data' => $backuser]]
            ]
        ]);
    }

    $valuetow = $valuetow != null ? "-$valuetow" : "";
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productlist = readJsonFileIfExists('product.json');
        $productlist_name = readJsonFileIfExists('product_name.json');
        if (isset($productlist[$result['code_product']])) $result['price_product'] = $productlist[$result['code_product']];
        $result['name_product'] = empty($productlist_name[$result['code_product']]) ? $result['name_product'] : $productlist_name[$result['code_product']];
        if (faoxima_is_in_json_list($location, $result['hide_panel'] ?? null)) continue;

        $rawPrice = (string)($result['price_product'] ?? '0');
        $cleanPrice = floatval(preg_replace('/[^\d.]/', '', $rawPrice) ?: 0);
        $discountVal = intval($pricediscount ?? 0);
        if ($discountVal != 0) {
            $resultper = ($cleanPrice * $discountVal) / 100;
            $cleanPrice = max(0, $cleanPrice - $resultper);
        }

        $prodName = trim((string)($result['name_product'] ?? 'سرویس'));
        $vol = intval($result['Volume_constraint'] ?? 0);
        $days = intval($result['Service_time'] ?? 0);
        $volText = ($vol > 0) ? "{$vol}GB" : "نامحدود";
        $dayText = ($days > 0) ? "{$days} روز" : "دائمی";
        $priceText = number_format($cleanPrice) . " ت";

        if ($statusshowprice === "onshowprice")
            $displayName = "💎 {$prodName} | {$priceText}";
        else
            $displayName = "💎 {$prodName} ({$volText} • {$dayText})";

        $codeProd = !empty($result['code_product']) ? (string)$result['code_product'] : (string)($result['id'] ?? '');
        $product['inline_keyboard'][] = [
            ['text' =>  $displayName, 'callback_data' => "{$datakeyboard}{$codeProd}{$valuetow}"]
        ];
    }

    if (empty($product['inline_keyboard'])) {
        try {
            $fbStmt = $pdo->query("SELECT * FROM product ORDER BY id DESC LIMIT 25");
            if ($fbStmt) {
                while ($fbRow = $fbStmt->fetch(PDO::FETCH_ASSOC)) {
                    $fbCode = !empty($fbRow['code_product']) ? (string)$fbRow['code_product'] : (string)$fbRow['id'];
                    $fbPrice = floatval(preg_replace('/[^\d.]/', '', (string)($fbRow['price_product'] ?? '0')) ?: 0);
                    $fbName = trim((string)($fbRow['name_product'] ?? 'سرویس'));
                    $product['inline_keyboard'][] = [
                        ['text' => "💎 {$fbName} | " . number_format($fbPrice) . " ت", 'callback_data' => "{$datakeyboard}{$fbCode}{$valuetow}"]
                    ];
                }
            }
        } catch (\Throwable $e) {}
    }

    if (empty($product['inline_keyboard'])) {
        $product['inline_keyboard'][] = [
            ['text' => '❌ محصولی یافت نشد', 'callback_data' => 'none_product']
        ];
    }

    if ($statuscustom) $product['inline_keyboard'][] = [['text' => $textbotlang['users']['customSellVolume']['title'] ?? 'خرید حجم دلخواه', 'callback_data' => $customvolume]];
    $product['inline_keyboard'][] = [
        ['text' => $textbotlang['users']['status']['backinfo'] ?? 'بازگشت', 'callback_data' => $backuser],
    ];
    return json_encode($product);
}
function KeyboardCategory($location, $agent, $backuser = "backuser")
{
    global $pdo, $textbotlang;
    $stmts = $pdo->prepare("SELECT category FROM product WHERE (Location = :location OR Location = '/all' OR FIND_IN_SET(:location, Location) > 0) AND (agent = :agent OR agent IN ('all', 'allusers') OR FIND_IN_SET(:agent, agent) > 0)");
    $stmts->bindValue(':location', (string)$location, PDO::PARAM_STR);
    $stmts->bindValue(':agent', (string)$agent, PDO::PARAM_STR);
    $stmts->execute();
    $activeCategories = [];
    foreach ($stmts->fetchAll(PDO::FETCH_COLUMN) as $catRaw) {
        $parts = explode(',', (string)$catRaw);
        foreach ($parts as $p) {
            $cleaned = mb_strtolower(trim($p), 'UTF-8');
            if ($cleaned !== '') $activeCategories[$cleaned] = true;
        }
    }
    $stmt = $pdo->prepare("SELECT * FROM category ORDER BY id ASC");
    $stmt->execute();
    $list_category = ['inline_keyboard' => []];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $remarkLower = mb_strtolower(trim((string)$row['remark']), 'UTF-8');
        if (empty($activeCategories[$remarkLower])) continue;
        $list_category['inline_keyboard'][] = [['text' => $row['remark'], 'callback_data' => "categorynames_" . $row['id']]];
    }
    $list_category['inline_keyboard'][] = [
        ['text' => "▶️ بازگشت به منوی قبل", "callback_data" => $backuser],
    ];
    return json_encode($list_category);
}
