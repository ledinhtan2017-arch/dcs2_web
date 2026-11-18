<?php
/** Integration checks on connection-local temporary tables. */
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
$_ENV['BANK_NAME'] = 'Test Bank';
$_ENV['BANK_ACCOUNT_NUMBER'] = '123456';
$_ENV['BANK_ACCOUNT_NAME'] = 'Test';
$config = require BASE_PATH . '/app/Config/config.php';
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Models/BankTransfer.php';
require BASE_PATH . '/app/Models/Order.php';
require BASE_PATH . '/app/Models/Review.php';
\App\Core\Database::configure($config['db']);
$pdo = \App\Core\Database::getConnection();
foreach (['products', 'product_variants', 'orders', 'order_items', 'order_status_history', 'payments', 'reviews', 'users'] as $table) {
    $pdo->exec("CREATE TEMPORARY TABLE `test_template` LIKE `$table`");
    $pdo->exec("CREATE TEMPORARY TABLE `$table` LIKE `test_template`");
    $pdo->exec('DROP TEMPORARY TABLE `test_template`');
}
$pdo->exec("ALTER TABLE orders MODIFY payment_method ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod'");
$pdo->exec("INSERT INTO users (id,name,email,password_hash) VALUES (1,'Buyer','test@example.test','x')");
$pdo->exec("INSERT INTO products (id,category_id,name,slug,status) VALUES (1,1,'Product','test','active')");
$pdo->exec("INSERT INTO product_variants (id,product_id,sku,price,stock_quantity) VALUES (1,1,'TEST-PAY',100000,5)");
function verify(bool $result, string $message): void { if (!$result) throw new RuntimeException($message); }
$data = ['user_id'=>1, 'customer_name'=>'Buyer', 'email'=>'test@example.test', 'phone'=>'0901234567', 'address'=>['street'=>'test'], 'payment_method'=>'bank_transfer'];
$order = \App\Models\Order::create($data, [['variant_id'=>1,'quantity'=>1]]);
verify($order !== null, 'Bank transfer order must be created.');
$id = $order['id'];
verify(\App\Models\Review::eligibleItem(1, 1) === null, 'Pending orders cannot be reviewed.');
verify(\App\Models\Order::updateStatus($id, 'confirmed'), 'Order confirmation failed.');
verify(!\App\Models\Order::updateStatus($id, 'shipping'), 'Unpaid bank transfer must not ship.');
verify(\App\Models\Order::confirmBankPayment($id), 'Payment confirmation failed.');
verify(!\App\Models\Order::confirmBankPayment($id), 'Payment cannot be confirmed twice.');
verify(!\App\Models\Order::updateStatus($id, 'cancelled'), 'Paid transfers require a refund process.');
verify(\App\Models\Order::updateStatus($id, 'shipping') && \App\Models\Order::updateStatus($id, 'delivered'), 'Paid order delivery failed.');
$item = \App\Models\Review::eligibleItem(1, 1);
verify($item !== null && \App\Models\Review::eligibleItem(1, 2) === null, 'Only the buyer may review.');
verify(!\App\Models\Review::create($item, 2, 5, 'Wrong buyer'), 'Other users cannot review this order item.');
verify(!\App\Models\Review::create($item, 1, 6, 'Invalid'), 'Invalid stars must be rejected.');
verify(\App\Models\Review::create($item, 1, 5, 'Good product'), 'Review submission failed.');
verify(!\App\Models\Review::create($item, 1, 5, 'Duplicate'), 'Duplicate review must be rejected.');
verify(count(\App\Models\Review::forProduct(1)) === 1, 'Review must be visible.');
echo "Bank transfer and review checks passed.\n";
