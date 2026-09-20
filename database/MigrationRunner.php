<?php
declare(strict_types=1);

namespace Zorvex\Database;

use Zorvex\Core\Database;
use PDOException;
use Exception;

class MigrationRunner
{
    public static function run(): bool
    {
        $db = Database::getInstance();
        $schemaFile = __DIR__ . '/schema.sql';

        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found at: {$schemaFile}");
        }

        $sql = file_get_contents($schemaFile);
        $queries = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($queries as $query) {
            if (!empty($query)) {
                try {
                    $db->getPdo()->exec($query);
                } catch (PDOException $e) {
                    error_log("Migration error on query: {$query} -> " . $e->getMessage());
                }
            }
        }

        return true;
    }
}
