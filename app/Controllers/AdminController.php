<?php
/**
 * AdminController – Admin page rendering.
 */
namespace App\Controllers;
use App\Core\{View, Database};

class AdminController
{
    public function dashboard(): void
    {
        $pdo = Database::getConnection();
        $stats = [
            'total_orders'    => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'pending_orders'  => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetchColumn(),
            'total_revenue'   => (int)$pdo->query("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE payment_status='paid' AND order_status <> 'cancelled'")->fetchColumn(),
            'total_products'  => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
            'total_users'     => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
            'low_stock'       => (int)$pdo->query("SELECT COUNT(*) FROM product_variants WHERE stock_quantity <= 5 AND is_active=1")->fetchColumn(),
        ];
        $recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();
        $start = (new \DateTimeImmutable('today'))->modify('-29 days');
        $stmt = $pdo->prepare("SELECT DATE(created_at) AS day, SUM(grand_total) AS revenue FROM orders WHERE payment_status='paid' AND order_status <> 'cancelled' AND created_at >= :start GROUP BY DATE(created_at)");
        $stmt->execute([':start' => $start->format('Y-m-d 00:00:00')]);
        $amounts = [];
        foreach ($stmt->fetchAll() as $row) $amounts[$row['day']] = (int) $row['revenue'];
        $sales = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $start->modify('+' . $i . ' days')->format('Y-m-d');
            $sales[] = ['date' => $day, 'amount' => $amounts[$day] ?? 0];
        }
        View::page('admin.dashboard', ['title' => 'Admin Dashboard', 'stats' => $stats, 'recentOrders' => $recentOrders, 'sales' => $sales], 'layouts.admin');
    }

    public function products(): void
    {
        $pdo = Database::getConnection();
        $products = $pdo->query("
            SELECT p.*, b.name AS brand_name, c.name AS category_name,
                   (SELECT SUM(stock_quantity) FROM product_variants WHERE product_id=p.id AND is_active=1) AS total_stock,
                   (SELECT MIN(price) FROM product_variants WHERE product_id=p.id AND is_active=1) AS price
            FROM products p LEFT JOIN brands b ON b.id=p.brand_id LEFT JOIN categories c ON c.id=p.category_id
            ORDER BY p.created_at DESC
        ")->fetchAll();
        View::page('admin.products', ['title' => 'Quản lý sản phẩm', 'products' => $products], 'layouts.admin');
    }

    public function productCreate(): void
    {
        $pdo = Database::getConnection();
        View::page('admin.product-form', [
            'title' => 'Thêm sản phẩm',
            'product' => null,
            'categories' => $pdo->query("SELECT c.*, s.name AS sport_name FROM categories c JOIN sports s ON s.id=c.sport_id WHERE c.is_active=1 ORDER BY s.name, c.name")->fetchAll(),
            'brands' => $pdo->query("SELECT * FROM brands WHERE is_active=1 ORDER BY name")->fetchAll(),
            'sports' => $pdo->query("SELECT * FROM sports WHERE is_active=1 ORDER BY sort_order")->fetchAll(),
        ], 'layouts.admin');
    }

    public function productEdit(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id=:id"); $stmt->execute([':id'=>$id]);
        $product = $stmt->fetch();
        if (!$product) { http_response_code(404); View::page('errors.404', ['title'=>'404']); return; }
        $stmt = $pdo->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY id');
        $stmt->execute([(int)$id]);
        $product['variants'] = $stmt->fetchAll();
        $stmt = $pdo->prepare('SELECT sport_id FROM product_sports WHERE product_id = ?');
        $stmt->execute([(int)$id]);
        $product['sport_ids'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $product['images'] = \App\Models\Product::getImages((int)$id);
        View::page('admin.product-form', [
            'title' => 'Sửa: '.$product['name'],
            'product' => $product,
            'categories' => $pdo->query("SELECT c.*, s.name AS sport_name FROM categories c JOIN sports s ON s.id=c.sport_id WHERE c.is_active=1 ORDER BY s.name, c.name")->fetchAll(),
            'brands' => $pdo->query("SELECT * FROM brands WHERE is_active=1 ORDER BY name")->fetchAll(),
            'sports' => $pdo->query("SELECT * FROM sports WHERE is_active=1 ORDER BY sort_order")->fetchAll(),
        ], 'layouts.admin');
    }

    public function sports(): void
    {
        $sports = \App\Models\Sport::all(false);
        View::page('admin.sports', ['title' => 'Quản lý môn thể thao', 'sports' => $sports], 'layouts.admin');
    }

    public function categories(): void
    {
        $categories = \App\Models\Category::all(false);
        View::page('admin.categories', ['title' => 'Quản lý danh mục', 'categories' => $categories], 'layouts.admin');
    }

    public function brands(): void
    {
        $pdo = Database::getConnection();
        $brands = $pdo->query("SELECT * FROM brands ORDER BY name")->fetchAll();
        View::page('admin.brands', ['title' => 'Quản lý thương hiệu', 'brands' => $brands], 'layouts.admin');
    }

    public function orders(): void
    {
        $pdo = Database::getConnection();
        $request = new \App\Core\Request();
        $status = $request->get('status', '');
        $sql = "SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON u.id=o.user_id";
        if ($status) { $sql .= " WHERE o.order_status = " . $pdo->quote($status); }
        $sql .= " ORDER BY o.created_at DESC LIMIT 100";
        $orders = $pdo->query($sql)->fetchAll();
        View::page('admin.orders', ['title' => 'Quản lý đơn hàng', 'orders' => $orders, 'currentStatus' => $status], 'layouts.admin');
    }

    public function orderDetail(string $id): void
    {
        $order = \App\Models\Order::findById((int)$id);
        if (!$order) { http_response_code(404); View::page('errors.404', ['title'=>'404']); return; }
        $order = \App\Models\Order::findByCode($order['order_code']);
        View::page('admin.order-detail', ['title' => 'Đơn hàng '.$order['order_code'], 'order' => $order], 'layouts.admin');
    }

    public function users(): void
    {
        $pdo = Database::getConnection();
        $users = $pdo->query("SELECT id,name,email,phone,role,status,created_at FROM users ORDER BY created_at DESC")->fetchAll();
        View::page('admin.users', ['title' => 'Quản lý người dùng', 'users' => $users], 'layouts.admin');
    }
}

