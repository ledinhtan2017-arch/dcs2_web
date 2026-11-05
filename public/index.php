<?php
/**
 * Sport Shop - Front Controller
 * All requests are routed through this file.
 */

// Strict error reporting in development
error_reporting(E_ALL);

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('VIEW_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'Views');
define('PUBLIC_PATH', __DIR__);
define('UPLOAD_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads');

// -------------------------------------------------------------------
// Simple autoloader – maps class names to file paths
// -------------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    // Map namespace prefixes to directories
    $prefixes = [
        'App\\Core\\'       => APP_PATH . '/Core/',
        'App\\Config\\'     => APP_PATH . '/Config/',
        'App\\Middleware\\'  => APP_PATH . '/Middleware/',
        'App\\Controllers\\' => APP_PATH . '/Controllers/',
        'App\\Models\\'     => APP_PATH . '/Models/',
        'App\\Helpers\\'    => APP_PATH . '/Helpers/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// -------------------------------------------------------------------
// Load configuration (.env → config array)
// -------------------------------------------------------------------
$config = require APP_PATH . '/Config/config.php';

// Set display_errors based on environment
$isDebug = ($config['app']['debug'] ?? false);
ini_set('display_errors', $isDebug ? '1' : '0');

// -------------------------------------------------------------------
// Start session with secure settings
// -------------------------------------------------------------------
$sessionLifetime = ($config['session']['lifetime'] ?? 120) * 60; // minutes → seconds

session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path'     => '/',
    'domain'   => '',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly'  => true,
    'samesite'  => 'Lax',
]);

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', (string)$sessionLifetime);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------------------
// Initialize database connection (lazy – just make config available)
// -------------------------------------------------------------------
\App\Core\Database::configure($config['db']);

// -------------------------------------------------------------------
// Bootstrap request, router and dispatch
// -------------------------------------------------------------------
try {
    $request = new \App\Core\Request();
    $router  = new \App\Core\Router($request);

    // Load route definitions
    require BASE_PATH . '/routes.php';

    // Dispatch the current request
    $router->dispatch();

} catch (\Throwable $e) {
    // -------------------------------------------------------------------
    // Global error handler
    // -------------------------------------------------------------------
    $request = $request ?? new \App\Core\Request();

    if ($request->isAjax() || $request->isJson()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error'   => [
                'code'    => 'SERVER_ERROR',
                'message' => $isDebug ? $e->getMessage() : 'Đã xảy ra lỗi hệ thống.',
            ],
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        if ($isDebug) {
            echo '<h1>Lỗi hệ thống</h1>';
            echo '<p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            echo '<h1>Đã xảy ra lỗi</h1>';
            echo '<p>Vui lòng thử lại sau.</p>';
        }
    }

    // Log error
    error_log('[SportShop] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}
