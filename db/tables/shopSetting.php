<?php

$agentCashback = json_encode(['n' => 0, 'n2' => 0]);

$values = [
    'customvolmef' => '4000',
    'customvolmen' => '4000',
    'customvolmen2' => '4000',
    'customtimepricef' => '4000',
    'customtimepricen' => '4000',
    'customtimepricen2' => '4000',
    'statusextra' => 'offextra',
    'statustimeextra' => 'ontimeextraa',
    'statusdirectpabuy' => 'ondirectbuy',
    'minbalancebuybulk' => '0',
    'statusdisorder' => 'offdisorder',
    'statuschangeservice' => 'onstatus',
    'statusshowprice' => 'offshowprice',
    'configshow' => 'onconfig',
    'backserviecstatus' => 'on',
    'chashbackextend' => '0',
    'chashbackextend_agent' => $agentCashback,
    'brand_name' => 'zorvex',
    'brand_mark' => 'Z',
    'brand_title' => 'Zorvex Pro',
    'brand_logo' => '',
    'brand_logo_state' => 'default',
    'brand_accent' => '#00f2fe',
    'brand_mode' => 'dark',
    'brand_force_dark' => '1',
];

$seed = [];
foreach ($values as $name => $value) {
    $seed[] = ['Namevalue' => $name, 'value' => $value];
}

return [
    'create' => <<<SQL
        Namevalue varchar(500) PRIMARY KEY NOT NULL,
        value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        SQL,
    'seed' => $seed,
];
