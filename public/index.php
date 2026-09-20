<?php
declare(strict_types=1);

/**
 * Zorvex Telegram Webhook Entry Point
 */

date_default_timezone_set('Asia/Tehran');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/error.log');

require_once dirname(__DIR__) . '/app/autoload.php';

use Zorvex\Core\TelegramBot;
use Zorvex\Core\Request;
use Zorvex\Core\Router;
use Zorvex\Database\MigrationRunner;

// Optional: Validate Secret Token Header from Telegram
$secretToken = (string)zorvex_config('telegram.webhook_secret');
if (!empty($secretToken)) {
    $headerSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
    if ($headerSecret !== $secretToken) {
        http_response_code(403);
        die('Forbidden');
    }
}

// Read raw body
$input = file_get_contents('php://input');
if (empty($input)) {
    echo "Zorvex Platform Online. Version: " . zorvex_config('app.version');
    exit;
}

$update = json_decode($input, true);
if (!is_array($update)) {
    http_response_code(400);
    exit;
}

try {
    // Ensure migrations have executed
    static $migrated = false;
    if (!$migrated) {
        MigrationRunner::run();
        $migrated = true;
    }

    $bot = new TelegramBot();
    $request = new Request($update);
    $router = new Router($bot);

    $router->dispatch($request);
} catch (Throwable $e) {
    error_log("Webhook Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}

http_response_code(200);
echo "OK";
