<?php
/** Integration test using connection-local temporary tables; existing data is untouched. */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/Config/config.php';
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Models/Order.php';
\App\Core\Database::configure($config['db']);
$pdo = \App\Core\Database::getConnection();

foreach (['products', 'product_variants', 'product_images', 'orders', 'order_items', 'order_status_history', 'payments', 'users'] as $table) {
    $pdo->exec("CREATE TEMPORARY TABLE `test_template` LIKE `$table`");
    $pdo->exec("CREATE TEMPORARY TABLE `$table` LIKE `test_template`");
    $pdo->exec('DROP TEMPORARY TABLE `test_template`');
}
$pdo->exec("INSERT INTO products (id, category_id, name, slug, status) VALUES (1, 1, 'Test product', 'test-product', 'active')");
$pdo->exec("INSERT INTO product_variants (id, product_id, sku, price, stock_quantity) VALUES (1, 1, 'TEST-1', 100000, 5)");
$pdo->exec("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (1, 'assets/images/test.jpg', 1)");

function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
function stock(PDO $pdo): int {
    return (int) $pdo->query('SELECT stock_quantity FROM product_variants WHERE id = 1')->fetchColumn();
}

$data = ['customer_name' => 'Test Customer', 'email' => 'test@example.com', 'phone' => '0901234567',
    'address' => ['street' => '123 Test Street'], 'idempotency_key' => 'test-order-one'];
$items = [['variant_id' => 1, 'quantity' => 2]];
$order = \App\Models\Order::create($data, $items);
check($order !== null && stock($pdo) === 3, 'Checkout must create an order and deduct stock.');
$details = \App\Models\Order::findByCode($order['order_code']);
check($details['items'][0]['name_snapshot'] === 'Test product', 'Order item snapshot is missing.');
check($details['items'][0]['product_image'] === 'assets/images/test.jpg', 'Primary image is missing.');
check(\App\Models\Order::updateStatus($order['id'], 'confirmed'), 'Confirmation failed.');
check(\App\Models\Order::updateStatus($order['id'], 'cancelled'), 'Cancellation failed.');
check(stock($pdo) === 5, 'Cancellation must restore stock.');
check(!\App\Models\Order::updateStatus($order['id'], 'cancelled') && stock($pdo) === 5, 'Repeated cancellation restored stock twice.');
check(\App\Models\Order::create($data, [['variant_id' => 1, 'quantity' => 0]]) === null, 'Zero quantity must be rejected.');
check(\App\Models\Order::create($data, [['variant_id' => 1, 'quantity' => 3], ['variant_id' => 1, 'quantity' => 3]]) === null, 'Duplicate variants must be rejected.');

$data['idempotency_key'] = 'test-order-two';
$second = \App\Models\Order::create($data, [['variant_id' => 1, 'quantity' => 1]]);
check($second !== null, 'Second checkout failed.');
check(\App\Models\Order::updateStatus($second['id'], 'confirmed'), 'Second confirmation failed.');
check(\App\Models\Order::updateStatus($second['id'], 'shipping'), 'Shipping failed.');
check(\App\Models\Order::updateStatus($second['id'], 'delivered'), 'Delivery failed.');
$payment = $pdo->query('SELECT payment_status FROM orders WHERE id = ' . (int) $second['id'])->fetchColumn();
check($payment === 'paid' && stock($pdo) === 4, 'Delivery must mark COD paid and retain the stock deduction.');
echo "Order lifecycle checks passed.\n";
