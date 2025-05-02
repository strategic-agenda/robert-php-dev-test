<?php
/**
 * Bootstrap Application
 *
 * This file loads environment variables and initializes the application.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

/**
 * Gets the value of an environment variable
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    return match (strtolower($value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        'empty', '(empty)' => '',
        default => $value,
    };
}

/**
 * Create database connection
 *
 * @return PDO
 */

function db_connect(): PDO
{
    return new PDO(
        sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=%s",
            env('DB_HOST', 'localhost'),
            env('DB_PORT', '3306'),
            env('DB_DATABASE', 'robert_cat'),
            env('DB_CHARSET', 'utf8mb4')
        ),
        env('DB_USERNAME', 'root'),
        env('DB_PASSWORD', ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}


// Define application environment
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', env('APP_DEBUG', true));


// Set error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
