<?php
/** Run with create, update, or rollback; uses connection-local temporary tables. */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/Config/config.php';
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Core/Request.php';
require BASE_PATH . '/app/Core/Response.php';
require BASE_PATH . '/app/Controllers/Api/Admin/ProductApiController.php';
\App\Core\Database::configure($config['db']);
$pdo = \App\Core\Database::getConnection();
foreach (['products', 'product_variants', 'product_sports', 'order_items'] as $table) {
    $pdo->exec("CREATE TEMPORARY TABLE `product_test_template` LIKE `$table`");
    $pdo->exec("CREATE TEMPORARY TABLE `$table` LIKE `product_test_template`");
    $pdo->exec('DROP TEMPORARY TABLE `product_test_template`');
}
$pdo->exec("INSERT INTO products (id, category_id, name, slug, status) VALUES (1, 1, 'Before', 'before', 'active')");
$pdo->exec("INSERT INTO product_variants (id, product_id, sku, price, stock_quantity) VALUES (1, 1, 'OLD-1', 100, 5), (2, 1, 'OLD-2', 200, 6)");
$pdo->exec("INSERT INTO product_sports (product_id, sport_id) VALUES (1, 1)");
$pdo->exec("INSERT INTO order_items (order_id, variant_id, sku_snapshot, name_snapshot, unit_price, quantity, line_total) VALUES (1, 1, 'OLD-1', 'Before', 100, 1, 100)");
$scenario = $argv[1] ?? '';
$variant = ['id' => 1, 'original_stock' => '5', 'sku' => 'EDIT-1', 'size_label' => '42', 'color_name' => 'Đỏ', 'price' => '150', 'sale_price' => '120', 'stock_quantity' => '8', 'is_active' => false];
$_POST = ['name' => 'After', 'category_id' => 1, 'brand_id' => null, 'status' => 'active', 'sport_ids' => [2], 'variants' => [$variant]];
if ($scenario === 'create') {
    $_POST['variants'][0]['id'] = null;
    $_POST['variants'][0]['is_active'] = true;
} elseif ($scenario === 'rollback') {
    $_POST['variants'][] = ['sku' => 'OLD-2', 'price' => '50', 'stock_quantity' => '3'];
} elseif ($scenario === 'stale') {
    $_POST['variants'][0]['original_stock'] = '6';
} elseif ($scenario !== 'update') {
    fwrite(STDERR, "Expected create, update, rollback or stale.\n");
    exit(2);
}

ob_start();
register_shutdown_function(function () use ($pdo, $scenario): void {
    $payload = json_decode(ob_get_clean(), true);
    $product = $pdo->query('SELECT * FROM products WHERE id = 1')->fetch();
    $variant = $pdo->query('SELECT * FROM product_variants WHERE id = 1')->fetch();
    $orderVariant = (int)$pdo->query('SELECT variant_id FROM order_items LIMIT 1')->fetchColumn();
    $valid = $orderVariant === 1;
    if ($scenario === 'create') {
        $created = $pdo->query("SELECT * FROM products WHERE name = 'After'")->fetch();
        $newVariant = $pdo->query("SELECT * FROM product_variants WHERE sku = 'EDIT-1'")->fetch();
        $sport = $pdo->query('SELECT sport_id FROM product_sports WHERE product_id = ' . (int)($created['id'] ?? 0))->fetchColumn();
        $valid = $valid && ($payload['success'] ?? false) && $created && $newVariant && (int)$newVariant['product_id'] === (int)$created['id']
            && (int)$newVariant['price'] === 150 && (int)$newVariant['sale_price'] === 120 && (int)$newVariant['stock_quantity'] === 8 && (int)$sport === 2;
    } elseif ($scenario === 'update') {
        $sport = $pdo->query('SELECT sport_id FROM product_sports WHERE product_id = 1')->fetchColumn();
        $other = $pdo->query('SELECT id FROM product_variants WHERE id = 2')->fetchColumn();
        $valid = $valid && ($payload['success'] ?? false) && $product['name'] === 'After' && $product['slug'] === 'before'
            && $variant['sku'] === 'EDIT-1' && $variant['size_label'] === '42' && $variant['color_name'] === 'Đỏ'
            && (int)$variant['price'] === 150 && (int)$variant['sale_price'] === 120 && (int)$variant['stock_quantity'] === 8
            && (int)$variant['is_active'] === 0 && (int)$sport === 2 && (int)$other === 2;
    } else {
        $sport = $pdo->query('SELECT sport_id FROM product_sports WHERE product_id = 1')->fetchColumn();
        $valid = $valid && !($payload['success'] ?? true) && $product['name'] === 'Before' && $variant['sku'] === 'OLD-1' && (int)$sport === 1;
    }
    if (!$valid) {
        fwrite(STDERR, "Product $scenario test failed: " . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n");
        throw new RuntimeException('Product management test failed.');
    }
    echo "Product $scenario checks passed.\n";
});

$controller = new \App\Controllers\Api\Admin\ProductApiController();
if ($scenario === 'create') $controller->store();
else $controller->update('1');
