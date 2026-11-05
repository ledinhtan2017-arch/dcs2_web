<?php
/** Integration test: all writes are confined to connection-local MySQL temporary tables. */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/Config/config.php';
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Models/Order.php';
\App\Core\Database::configure($config['db']);
$pdo = \App\Core\Database::getConnection();

foreach (['products', 'product_variants', 'orders', 'order_items', 'order_status_history', 'payments'] as $table) {
    $pdo->exec("CREATE TEMPORARY TABLE `test_template` LIKE `$table`");
    $pdo->exec("CREATE TEMPORARY TABLE `$table` LIKE `test_template`");
    $pdo->exec('DROP TEMPORARY TABLE `test_template`');
}
$pdo->exec("INSERT INTO products (id, category_id, name, slug, status) VALUES (1, 1, 'Test product', 'test-product', 'active')");
$pdo->exec("INSERT INTO product_variants (id, product_id, sku, price, stock_quantity) VALUES (1, 1, 'TEST-CANCEL', 100000, 10)");

function checkCancel(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

function currentStock(PDO $pdo): int
{
    return (int) $pdo->query('SELECT stock_quantity FROM product_variants WHERE id = 1')->fetchColumn();
}

function historyCount(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM order_status_history WHERE order_id = ?');
    $stmt->execute([$id]);
    return (int) $stmt->fetchColumn();
}

function createTestOrder(?int $ownerId, string $key, int $quantity = 2): array
{
    $data = ['user_id' => $ownerId, 'customer_name' => 'Test Customer', 'email' => 'test@example.com',
        'phone' => '0901234567', 'address' => ['street' => 'Test Street'], 'idempotency_key' => $key];
    $order = \App\Models\Order::create($data, [['variant_id' => 1, 'quantity' => $quantity]]);
    if ($order === null) throw new RuntimeException('Test setup could not create order.');
    return $order;
}

$order = createTestOrder(11, 'cancel-test-owner');
checkCancel(currentStock($pdo) === 8, 'Setup must deduct stock.');
checkCancel(!\App\Models\Order::cancelByCustomer($order['order_code'], 12), 'Another user must not cancel this order.');
checkCancel(currentStock($pdo) === 8 && historyCount($pdo, $order['id']) === 1, 'A rejected cancellation must not change stock or history.');
checkCancel(\App\Models\Order::cancelByCustomer($order['order_code'], 11), 'Owner must be able to cancel a pending order.');
checkCancel(currentStock($pdo) === 10, 'Cancellation must restore the stock exactly once.');
$stmt = $pdo->prepare('SELECT from_status, to_status, actor_user_id FROM order_status_history WHERE order_id = ? AND to_status = ?');
$stmt->execute([$order['id'], 'cancelled']);
$history = $stmt->fetch();
checkCancel($history && $history['from_status'] === 'pending' && (int) $history['actor_user_id'] === 11, 'Cancellation history must identify the owner and prior status.');
checkCancel(!\App\Models\Order::cancelByCustomer($order['order_code'], 11), 'An already cancelled order must not be cancelled twice.');
checkCancel(currentStock($pdo) === 10 && historyCount($pdo, $order['id']) === 2, 'Repeated cancellation must not change stock or history.');

$confirmed = createTestOrder(11, 'cancel-test-confirmed');
checkCancel(\App\Models\Order::updateStatus($confirmed['id'], 'confirmed', 99), 'Setup must confirm the order.');
checkCancel(!\App\Models\Order::cancelByCustomer($confirmed['order_code'], 11), 'Customer must not cancel a confirmed order.');
checkCancel(currentStock($pdo) === 8 && historyCount($pdo, $confirmed['id']) === 2, 'Rejected cancellation must leave confirmed order unchanged.');

$guest = createTestOrder(null, 'cancel-test-guest', 1);
checkCancel(!\App\Models\Order::cancelByCustomer($guest['order_code'], 11), 'A customer must not cancel a guest order.');
checkCancel(!\App\Models\Order::cancelByCustomer('NO-SUCH-ORDER', 11), 'An unknown order must not be cancelled.');
checkCancel(currentStock($pdo) === 7 && historyCount($pdo, $guest['id']) === 1, 'Invalid requests must leave other orders and stock unchanged.');

$owned = createTestOrder(11, 'cancel-test-registered', 1);
checkCancel(!\App\Models\Order::cancelByGuest($owned['order_code']), 'Guest cancellation must not affect an account order.');
checkCancel(currentStock($pdo) === 6 && historyCount($pdo, $owned['id']) === 1, 'Rejected guest cancellation must not change the order.');
checkCancel(\App\Models\Order::cancelByGuest($guest['order_code']), 'A pending guest order can be cancelled by the guest cancellation flow.');
checkCancel(currentStock($pdo) === 7, 'Guest cancellation must restore stock.');
$stmt->execute([$guest['id'], 'cancelled']);
$history = $stmt->fetch();
checkCancel($history && $history['from_status'] === 'pending' && $history['actor_user_id'] === null, 'Guest cancellation history must identify no account actor.');
checkCancel(!\App\Models\Order::cancelByGuest($guest['order_code']), 'Guest cancellation must not run twice.');
checkCancel(currentStock($pdo) === 7 && historyCount($pdo, $guest['id']) === 2, 'Repeated guest cancellation must not restore stock twice.');

$confirmedGuest = createTestOrder(null, 'cancel-test-confirmed-guest', 1);
checkCancel(\App\Models\Order::updateStatus($confirmedGuest['id'], 'confirmed', 99), 'Setup must confirm the guest order.');
checkCancel(!\App\Models\Order::cancelByGuest($confirmedGuest['order_code']), 'Guest must not cancel a confirmed order.');
checkCancel(currentStock($pdo) === 6 && historyCount($pdo, $confirmedGuest['id']) === 2, 'Rejected guest cancellation must not change stock or history.');

echo "Customer order cancellation checks passed.\n";
