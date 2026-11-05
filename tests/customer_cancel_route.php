<?php
/** Run one scenario per process. Uses connection-local temporary tables only. */
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
$pdo = \App\Core\Database::getConnection();
foreach (['products', 'product_variants', 'product_images', 'orders', 'order_items', 'order_status_history', 'payments', 'users'] as $table) {
    $pdo->exec("CREATE TEMPORARY TABLE `test_template` LIKE `$table`");
    $pdo->exec("CREATE TEMPORARY TABLE `$table` LIKE `test_template`");
    $pdo->exec('DROP TEMPORARY TABLE `test_template`');
}
$pdo->exec("INSERT INTO products (id, category_id, name, slug, status) VALUES (1, 1, 'Test product', 'test-product', 'active')");
$pdo->exec("INSERT INTO product_variants (id, product_id, sku, price, stock_quantity) VALUES (1, 1, 'TEST-ROUTE', 100000, 5)");

$scenario = $argv[1] ?? '';
$cases = [
    'guest_missing_token' => [true, 'missing', 403, false],
    'guest_wrong_session' => [true, 'wrong', 404, false],
    'guest_valid' => [true, 'valid', 302, true],
    'owner_missing_token' => [false, 'missing', 403, false],
    'owner_wrong_user' => [false, 'wrong', 404, false],
    'owner_valid' => [false, 'valid', 302, true],
];
if (!isset($cases[$scenario])) {
    fwrite(STDERR, "Unknown scenario.\n");
    exit(2);
}
[$guest, $mode, $expectedStatus, $shouldCancel] = $cases[$scenario];
$data = ['user_id' => $guest ? null : 11, 'customer_name' => 'Test', 'email' => 'test@example.com',
    'phone' => '0901234567', 'address' => ['street' => 'Test'], 'idempotency_key' => 'test-route'];
$order = \App\Models\Order::create($data, [['variant_id' => 1, 'quantity' => 2]]);
if (!$order) throw new RuntimeException('Could not create test order.');

$_SESSION = ['_csrf_token' => 'test-token'];
if ($guest) {
    $_SESSION['_guest_order_code'] = $mode === 'wrong' ? 'OTHER-ORDER' : $order['order_code'];
    $uri = '/order-success/' . $order['order_code'] . '/cancel';
} else {
    $_SESSION['_auth_user'] = ['id' => $mode === 'wrong' ? 12 : 11, 'role' => 'customer'];
    $uri = '/account/orders/' . $order['order_code'] . '/cancel';
}
$_POST = $mode === 'missing' ? [] : ['_token' => 'test-token'];
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';

ob_start();
register_shutdown_function(function () use ($pdo, $order, $scenario, $expectedStatus, $shouldCancel): void {
    $output = ob_get_clean();
    $status = http_response_code();
    $stmt = $pdo->prepare('SELECT order_status FROM orders WHERE id = ?');
    $stmt->execute([$order['id']]);
    $orderStatus = $stmt->fetchColumn();
    $stock = (int) $pdo->query('SELECT stock_quantity FROM product_variants WHERE id = 1')->fetchColumn();
    $ok = $status === $expectedStatus && $orderStatus === ($shouldCancel ? 'cancelled' : 'pending') && $stock === ($shouldCancel ? 5 : 3);
    if (!$ok) {
        fwrite(STDERR, "$scenario failed: HTTP " . var_export($status, true) . ", order=$orderStatus, stock=$stock\n$output\n");
        exit(1);
    }
    echo "$scenario passed.\n";
});

$router = new \App\Core\Router(new \App\Core\Request());
require BASE_PATH . '/routes.php';
$router->dispatch();
