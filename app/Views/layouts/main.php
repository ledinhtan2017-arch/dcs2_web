<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Sport Shop') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <?= $csrf_meta ?? '' ?>
</head>
<body>
    <!-- Utility Bar -->
    <div class="utility-bar">
        <div class="container" style="display:flex;justify-content:flex-end;align-items:center;gap:18px;">
            <?php if ($auth_check): ?>
                <span>Xin chào, <?= htmlspecialchars($auth_user['name'] ?? '') ?></span>
                <?php if ($auth_admin): ?>
                    <a href="/admin">Admin</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="/login">Đăng nhập</a>
                <a href="/register">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Primary Navigation -->
    <nav class="primary-nav">
        <button class="nav-hamburger" onclick="toggleMobileMenu()" aria-label="Menu">☰</button>
        <a href="/" class="nav-logo">Sport Shop</a>
        <ul class="nav-links">
            <li><a href="/">Trang chủ</a></li>
            <li><a href="/sports/cau-long">Cầu lông</a></li>
            <li><a href="/sports/bong-da">Bóng đá</a></li>
            <li><a href="/sports/bong-ro">Bóng rổ</a></li>
            <li><a href="/sports/chay-bo-fitness">Chạy bộ</a></li>
        </ul>
        <div class="nav-actions">
            <form action="/search" method="GET" style="margin:0;">
                <input type="search" name="q" class="search-pill" placeholder="Tìm kiếm..." aria-label="Tìm kiếm sản phẩm" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </form>
            <?php if ($auth_check): ?>
                <a href="/account" class="nav-icon" title="Tài khoản">👤</a>
            <?php endif; ?>
            <a href="/cart" class="nav-icon" title="Giỏ hàng">
                🛒
                <span class="cart-badge" id="cart-count" style="display:none;">0</span>
            </a>
        </div>
    </nav>

    <!-- Mobile Drawer -->
    <div class="mobile-overlay" id="mobile-overlay" onclick="toggleMobileMenu()"></div>
    <div class="mobile-drawer" id="mobile-drawer">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <strong style="font-size:20px;">Menu</strong>
            <button onclick="toggleMobileMenu()" style="background:none;border:none;font-size:24px;cursor:pointer;">✕</button>
        </div>
        <ul class="nav-links">
            <li><a href="/">Trang chủ</a></li>
            <li><a href="/sports/cau-long">Cầu lông</a></li>
            <li><a href="/sports/bong-da">Bóng đá</a></li>
            <li><a href="/sports/bong-ro">Bóng rổ</a></li>
            <li><a href="/sports/chay-bo-fitness">Chạy bộ / Fitness</a></li>
            <?php if ($auth_check): ?>
                <li><a href="/account">Tài khoản</a></li>
                <li><a href="/account/orders">Đơn hàng</a></li>
            <?php else: ?>
                <li><a href="/login">Đăng nhập</a></li>
                <li><a href="/register">Đăng ký</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Flash Messages -->
    <div class="toast-container" id="toast-container">
        <?php if (!empty($flash)):
            foreach ($flash as $type => $msg): ?>
                <div class="toast toast-<?= $type ?>">
                    <span><?= htmlspecialchars($msg) ?></span>
                    <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
                </div>
            <?php endforeach;
        endif; ?>
    </div>

    <!-- Main Content -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>Sport Shop</h4>
                    <p style="font-size:14px;color:var(--color-mute);line-height:1.6;">Đồ thể thao chính hãng cho mọi môn: Cầu lông, Bóng đá, Bóng rổ, Chạy bộ & Fitness.</p>
                </div>
                <div class="footer-col">
                    <h4>Môn thể thao</h4>
                    <a href="/sports/cau-long">Cầu lông</a>
                    <a href="/sports/bong-da">Bóng đá</a>
                    <a href="/sports/bong-ro">Bóng rổ</a>
                    <a href="/sports/chay-bo-fitness">Chạy bộ / Fitness</a>
                </div>
                <div class="footer-col">
                    <h4>Hỗ trợ</h4>
                    <a href="/support/huong-dan-mua-hang">Hướng dẫn mua hàng</a>
                    <a href="/support/doi-tra">Chính sách đổi trả</a>
                    <a href="/support/van-chuyen">Vận chuyển</a>
                    <a href="/support/lien-he">Liên hệ</a>
                </div>
                <div class="footer-col">
                    <h4>Tài khoản</h4>
                    <a href="/login">Đăng nhập</a>
                    <a href="/register">Đăng ký</a>
                    <a href="/account/orders">Đơn hàng</a>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> Sport Shop. Dự án portfolio – không phải website thương mại thật.
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
    <script>
    function toggleMobileMenu() {
        document.getElementById('mobile-drawer').classList.toggle('open');
        document.getElementById('mobile-overlay').classList.toggle('open');
    }
    // Auto-dismiss toasts
    document.querySelectorAll('.toast').forEach(t => setTimeout(() => t.remove(), 5000));
    </script>
</body>
</html>

