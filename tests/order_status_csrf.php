<?php
/** Simulates an authenticated admin PATCH to a non-existent order (no data is changed).
 * Run with: php tests/order_status_csrf.php [missing|valid]
 */
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/Views');
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) require_once $file;
    }
});
$config = require APP_PATH . '/Config/config.php';
\App\Core\Database::configure($config['db']);
$_SESSION = ['_auth_user' => ['id' => 1, 'role' => 'admin'], '_csrf_token' => 'test-token'];
$_SERVER['REQUEST_METHOD'] = 'PATCH';
$_SERVER['REQUEST_URI'] = '/api/admin/orders/0/status';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
$_POST = ['status' => 'confirmed'];
if (($argv[1] ?? 'missing') === 'valid') $_SERVER['HTTP_X_CSRF_TOKEN'] = 'test-token';
$router = new \App\Core\Router(new \App\Core\Request());
require BASE_PATH . '/routes.php';
$router->dispatch();
