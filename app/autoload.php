<?php
declare(strict_types=1);

/**
 * Zorvex Autonomous PSR-4 Autoloader
 * Ensures zero-dependency execution if composer vendor has not been loaded.
 */

spl_autoload_register(function (string $class): void {
    $prefix = 'Zorvex\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load global helpers
require_once __DIR__ . '/Helpers/jdf.php';
require_once __DIR__ . '/Helpers/functions.php';
