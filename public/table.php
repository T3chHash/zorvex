<?php
declare(strict_types=1);

/**
 * Zorvex Database Initializer & Health Probe
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/app/autoload.php';

use Zorvex\Core\Database;
use Zorvex\Database\MigrationRunner;

try {
    MigrationRunner::run();
    $db = Database::getInstance();

    $tables = $db->select("SHOW TABLES");
    $tableNames = array_map(fn($t) => reset($t), $tables);

    echo json_encode([
        'ok' => true,
        'system' => 'Zorvex Pro',
        'version' => zorvex_config('app.version'),
        'message' => 'Database tables checked and verified successfully!',
        'tables_count' => count($tableNames),
        'tables' => $tableNames,
        'timestamp' => time(),
        'jalali_date' => jalali_now(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
