<?php
/**
 * Sport Shop – Application Configuration
 *
 * Parses .env file and returns a structured config array.
 */

namespace App\Config;

// -------------------------------------------------------------------
// Lightweight .env parser
// -------------------------------------------------------------------
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Must contain '='
        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Remove surrounding quotes
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        // Only set if not already defined by the real environment
        if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}

/**
 * Retrieve an environment variable with an optional default.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === '') {
        return $default;
    }

    // Cast common string values
    return match (strtolower($value)) {
        'true', '(true)'   => true,
        'false', '(false)' => false,
        'null', '(null)'   => null,
        'empty', '(empty)' => '',
        default             => $value,
    };
}

// -------------------------------------------------------------------
// Load .env from project root
// -------------------------------------------------------------------
loadEnv(BASE_PATH . DIRECTORY_SEPARATOR . '.env');

// -------------------------------------------------------------------
// Build and return config array
// -------------------------------------------------------------------
return [
    'app' => [
        'name'  => env('APP_NAME', 'Sport Shop'),
        'env'   => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
        'url'   => env('APP_URL', 'http://localhost'),
    ],

    'db' => [
        'host'     => env('DB_HOST', 'localhost'),
        'port'     => (int) env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'sport_shop'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset'  => 'utf8mb4',
    ],

    'session' => [
        'lifetime' => (int) env('SESSION_LIFETIME', 120), // minutes
    ],

    'admin' => [
        'seed_password' => env('ADMIN_SEED_PASSWORD', ''),
    ],
];
