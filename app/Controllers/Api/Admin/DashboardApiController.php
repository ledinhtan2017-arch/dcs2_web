<?php
namespace App\Controllers\Api\Admin;
use App\Core\{Response, Database};

class DashboardApiController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        Response::success([
            'total_orders'   => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'pending_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetchColumn(),
            'revenue'        => (int)$pdo->query("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE order_status IN ('confirmed','shipping','delivered')")->fetchColumn(),
            'total_products' => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
            'total_users'    => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
            'low_stock'      => (int)$pdo->query("SELECT COUNT(*) FROM product_variants WHERE stock_quantity<=5 AND is_active=1")->fetchColumn(),
        ]);
    }
}

