<?php

declare(strict_types=1);

namespace App\Config;

/**
 * Centralized configuration provider that reads environment variables with safe fallbacks.
 * Prevents hardcoded credentials, URLs, or magic numbers across application layers.
 */
final class AppConfig
{
    /** @var string Default application title */
    public const DEFAULT_APP_NAME = 'Discord Indonesia Simulator';

    /** @var string Current simulation server season version */
    public const SIMULATION_ERA = 'Kabinet Indonesia Emas — Musim IV';

    /** @var int Maximum concurrent players per parliamentary instance */
    public const MAX_SERVER_CAPACITY = 500;

    /** @var bool Tracks whether environment file has already been parsed */
    private static bool $envLoaded = false;

    /**
     * Retrieves a configuration value from environment variables or returns the provided default.
     *
     * @param string $key Environment variable identifier.
     * @param string $default Fallback value when the environment variable is not defined.
     * @return string Resolved configuration value.
     */
    public static function get(string $key, string $default = ''): string
    {
        self::loadEnvIfNeeded();

        $value = getenv($key);
        if ($value === false || $value === '') {
            return $_ENV[$key] ?? $default;
        }

        return (string) $value;
    }

    /**
     * Loads key-value pairs from a local .env file if present in the project root.
     *
     * @return void
     */
    private static function loadEnvIfNeeded(): void
    {
        if (self::$envLoaded) {
            return;
        }

        self::$envLoaded = true;
        $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

        if (!is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            // NOTE: Skip comment lines and malformed entries without assignment operator
            if ($trimmed === '' || strpos($trimmed, '#') === 0 || strpos($trimmed, '=') === false) {
                continue;
            }

            [$name, $rawVal] = explode('=', $trimmed, 2);
            $cleanName = trim($name);
            $cleanVal = trim($rawVal, " \t\n\r\0\x0B\"'");

            if ($cleanName !== '' && getenv($cleanName) === false) {
                putenv(sprintf('%s=%s', $cleanName, $cleanVal));
                $_ENV[$cleanName] = $cleanVal;
            }
        }
    }
}

