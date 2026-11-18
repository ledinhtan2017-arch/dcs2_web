<?php
/** Verify dashboard totals and daily chart using temporary tables. */
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/Views');
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $path = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($path)) require_once $path;
    }
});
$config = require APP_PATH . '/Config/config.php';
\App\Core\Database::configure($config['db']);
$pdo = \App\Core\Database::getConnection();
foreach (['products', 'product_variants', 'orders', 'users'] as $table) {
    $pdo->exec("CREATE TEMPORARY TABLE `test_template` LIKE `$table`");
    $pdo->exec("CREATE TEMPORARY TABLE `$table` LIKE `test_template`");
    $pdo->exec('DROP TEMPORARY TABLE `test_template`');
}
$pdo->exec("INSERT INTO users (id,name,email,password_hash) VALUES (1,'Buyer','sales@example.test','x')");
$pdo->exec("INSERT INTO products (id,category_id,name,slug,status) VALUES (1,1,'Product','sales-test','active')");
$pdo->exec("INSERT INTO product_variants (id,product_id,sku,price,stock_quantity) VALUES (1,1,'TEST-SALES',100000,8)");
$insert = $pdo->prepare("INSERT INTO orders (order_code,user_id,customer_name,email,phone,shipping_address_json,subtotal,shipping_fee,grand_total,payment_method,payment_status,order_status,created_at) VALUES (?,1,'Buyer','sales@example.test','0123456789','{}',?,30000,?,'cod',?,?,NOW())");
$insert->execute(['PAID',100000,130000,'paid','delivered']);
$insert->execute(['UNPAID',999000,1029000,'unpaid','confirmed']);
$insert->execute(['CANCEL',888000,918000,'paid','cancelled']);
$_SESSION = [];
ob_start();
(new \App\Controllers\AdminController())->dashboard();
$html = ob_get_clean();
if (substr_count($html, '130.000 ₫') < 2 || str_contains($html, '1.048.000 ₫')) throw new RuntimeException('Dashboard must count paid, non-cancelled orders in both total and chart.');
if (substr_count($html, 'class="sales-chart-column"') !== 30) throw new RuntimeException('Dashboard must show 30 days.');
echo "Sales dashboard checks passed.\n";
