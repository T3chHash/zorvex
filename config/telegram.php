<?php
declare(strict_types=1);

return [
    'token' => getenv('TELEGRAM_BOT_TOKEN') ?: 'YOUR_BOT_TOKEN_HERE',
    'bot_username' => getenv('TELEGRAM_BOT_USERNAME') ?: 'zorvex_bot',
    'webhook_secret' => getenv('WEBHOOK_SECRET') ?: 'zorvex_random_secret_token',
    'admins' => array_filter(array_map('trim', explode(',', getenv('ADMIN_IDS') ?: '123456789'))),
    'log_channel' => getenv('LOG_CHANNEL') ?: '', // e.g. -1001234567890
    'report_topics' => [
        'general' => (int)(getenv('TOPIC_GENERAL') ?: 0),
        'orders' => (int)(getenv('TOPIC_ORDERS') ?: 0),
        'receipts' => (int)(getenv('TOPIC_RECEIPTS') ?: 0),
        'support' => (int)(getenv('TOPIC_SUPPORT') ?: 0),
        'errors' => (int)(getenv('TOPIC_ERRORS') ?: 0),
    ],
    'force_channel' => getenv('FORCE_CHANNEL') ?: '', // e.g. @ZorvexChannel
];
