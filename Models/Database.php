<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\AppConfig;
use PDO;
use PDOException;

/**
 * Manages PDO database connections with defensive fallback support.
 * Ensures prepared statements and strict error modes are configured by default.
 */
final class Database
{
    /** @var PDO|null Singleton PDO connection instance */
    private static ?PDO $connection = null;

    /** @var bool Indicates whether a connection attempt has already failed */
    private static bool $connectionFailed = false;

    /**
     * Establishes or returns an active PDO database connection.
     * Returns null gracefully when the database server is unreachable so read-only
     * landing pages can degrade cleanly to institutional snapshot repositories.
     *
     * @return PDO|null Active PDO instance or null if database is offline.
     */
    public static function getConnection(): ?PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        if (self::$connectionFailed) {
            return null;
        }

        $host = AppConfig::get('DB_HOST', '127.0.0.1');
        $port = AppConfig::get('DB_PORT', '3306');
        $dbName = AppConfig::get('DB_DATABASE', 'nusantara_rp');
        $username = AppConfig::get('DB_USERNAME', 'root');
        $password = AppConfig::get('DB_PASSWORD', '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);

        try {
            // NOTE: Short 1-second timeout prevents blocking page render when local MySQL is not started
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 1,
            ]);

            return self::$connection;
        } catch (PDOException $exception) {
            self::$connectionFailed = true;
            error_log('[Database::getConnection] Falling back to static snapshot repository: ' . $exception->getMessage());
            return null;
        }
    }
}

