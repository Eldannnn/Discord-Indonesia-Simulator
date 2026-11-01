<?php

declare(strict_types=1);

/**
 * Automated MySQL Database Migration & Seeder Runner (`database/migrate.php`).
 * Reads `.env` configuration, connects to the MySQL server, and imports `database/schema.sql`.
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'AppConfig.php';

use App\Config\AppConfig;

$host = AppConfig::get('DB_HOST', '127.0.0.1');
$port = AppConfig::get('DB_PORT', '3306');
$username = AppConfig::get('DB_USERNAME', 'root');
$password = AppConfig::get('DB_PASSWORD', '');
$schemaPath = __DIR__ . DIRECTORY_SEPARATOR . 'schema.sql';

if (!is_readable($schemaPath)) {
    fwrite(STDERR, "[ERROR] File schema.sql tidak ditemukan di: {$schemaPath}\n");
    exit(1);
}

try {
    $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $sqlContent = (string) file_get_contents($schemaPath);
    $pdo->exec($sqlContent);

    fwrite(STDOUT, "[SUCCESS] Database `nusantara_rp` berhasil dibuat dan di-seed untuk User Presiden & Wakil Presiden RI.\n");
} catch (PDOException $exception) {
    fwrite(
        STDERR,
        "[WARNING] Tidak dapat terhubung ke server MySQL di {$host}:{$port} (" . $exception->getMessage() . ").\n" .
        "Pastikan layanan MySQL/MariaDB (Laragon/XAMPP) telah dinyalakan, lalu jalankan kembali: php database/migrate.php\n"
    );
    exit(1);
}

