<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin - Sport Shop') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <?= $csrf_meta ?? '' ?>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2><a href="/admin" style="color:inherit;text-decoration:none;">⚡ Admin</a></h2>
            <a href="/admin" class="<?= ($_SERVER['REQUEST_URI'] ?? '') === '/admin' ? 'active' : '' ?>">📊 Dashboard</a>
            <a href="/admin/products" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/products') ? 'active' : '' ?>">📦 Sản phẩm</a>
            <a href="/admin/orders" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/orders') ? 'active' : '' ?>">📋 Đơn hàng</a>
            <a href="/admin/sports" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/sports') ? 'active' : '' ?>">🏅 Môn thể thao</a>
            <a href="/admin/categories" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/categories') ? 'active' : '' ?>">📁 Danh mục</a>
            <a href="/admin/brands" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/brands') ? 'active' : '' ?>">🏷️ Thương hiệu</a>
            <a href="/admin/users" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/users') ? 'active' : '' ?>">👥 Người dùng</a>
            <hr style="border-color:rgba(255,255,255,0.1);margin:18px 0;">
            <a href="/" target="_blank">🌐 Xem website</a>
            <a href="/account">👤 <?= htmlspecialchars($auth_user['name'] ?? 'Admin') ?></a>
        </aside>
        <div class="admin-main">
            <?= $content ?? '' ?>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>

