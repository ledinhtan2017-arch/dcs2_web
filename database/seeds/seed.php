<?php
/**
 * Database Seeder
 * 
 * Seeds the database with demo data.
 * Usage: php database/seeds/seed.php
 */

declare(strict_types=1);

// ─── Load environment ───────────────────────────────────────────────
$envFile = __DIR__ . '/../../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../../.env.example';
}
if (!file_exists($envFile)) {
    fwrite(STDERR, "[LỖI] Không tìm thấy file .env\n");
    exit(1);
}

$envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($envLines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$key, $value] = explode('=', $line, 2);
    $key   = trim($key);
    $value = trim(trim($value), '"\'');
    $_ENV[$key] = $value;
    putenv("$key=$value");
}

$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbPort = $_ENV['DB_PORT'] ?? '3306';
$dbName = $_ENV['DB_DATABASE'] ?? 'sport_shop';
$dbUser = $_ENV['DB_USERNAME'] ?? 'root';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';

function info(string $msg): void { echo "\033[32m[INFO]\033[0m $msg\n"; }
function warn(string $msg): void { echo "\033[33m[CẢNH BÁO]\033[0m $msg\n"; }
function err(string $msg): void  { fwrite(STDERR, "\033[31m[LỖI]\033[0m $msg\n"); }

// ─── Connect ────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
        $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    info("Đã kết nối database: $dbName");
} catch (PDOException $e) {
    err("Không thể kết nối: " . $e->getMessage());
    exit(1);
}

// ─── Check if data already exists ───────────────────────────────────
$count = (int) $pdo->query("SELECT COUNT(*) FROM sports")->fetchColumn();
if ($count > 0) {
    warn("Database đã có $count môn thể thao. Bỏ qua seed data.");
    warn("Nếu muốn seed lại, hãy xóa dữ liệu trước: TRUNCATE các bảng hoặc drop database rồi migrate lại.");
} else {
    // ─── Run seed SQL ───────────────────────────────────────────────
    $seedFile = __DIR__ . '/seed_data.sql';
    if (!file_exists($seedFile)) {
        err("Không tìm thấy file: $seedFile");
        exit(1);
    }

    $sql = file_get_contents($seedFile);
    if ($sql === false) {
        err("Không đọc được file seed_data.sql");
        exit(1);
    }

    try {
        $pdo->exec($sql);
        info("✓ Đã seed dữ liệu mẫu thành công!");
        
        // Count results
        $sports     = (int) $pdo->query("SELECT COUNT(*) FROM sports")->fetchColumn();
        $categories = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $brands     = (int) $pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn();
        $products   = (int) $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $variants   = (int) $pdo->query("SELECT COUNT(*) FROM product_variants")->fetchColumn();
        $images     = (int) $pdo->query("SELECT COUNT(*) FROM product_images")->fetchColumn();
        
        echo "\n";
        echo "╔══════════════════════════════════════════╗\n";
        echo "║        DỮ LIỆU ĐÃ SEED                 ║\n";
        echo "╠══════════════════════════════════════════╣\n";
        printf("║  Môn thể thao     : %-19d ║\n", $sports);
        printf("║  Danh mục         : %-19d ║\n", $categories);
        printf("║  Thương hiệu      : %-19d ║\n", $brands);
        printf("║  Sản phẩm         : %-19d ║\n", $products);
        printf("║  Biến thể         : %-19d ║\n", $variants);
        printf("║  Ảnh sản phẩm     : %-19d ║\n", $images);
        echo "╚══════════════════════════════════════════╝\n";
    } catch (PDOException $e) {
        err("Lỗi khi seed: " . $e->getMessage());
        exit(1);
    }
}

// ─── Seed Admin User ────────────────────────────────────────────────
$adminExists = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

if ($adminExists > 0) {
    info("Admin user đã tồn tại, bỏ qua.");
} else {
    $adminPassword = $_ENV['ADMIN_SEED_PASSWORD'] ?? '';
    
    if ($adminPassword === '') {
        // Prompt for password in CLI
        if (PHP_SAPI === 'cli') {
            echo "Nhập mật khẩu cho admin (tối thiểu 6 ký tự): ";
            $adminPassword = trim(fgets(STDIN));
        }
    }
    
    if (strlen($adminPassword) < 6) {
        warn("Mật khẩu admin không hợp lệ (< 6 ký tự). Sử dụng mật khẩu mặc định dev: 'admin123'");
        warn("⚠️  HÃY ĐỔI MẬT KHẨU NGAY SAU KHI ĐĂNG NHẬP!");
        $adminPassword = 'admin123';
    }
    
    $hash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password_hash, phone, role, status)
        VALUES (:name, :email, :hash, :phone, 'admin', 'active')
    ");
    $stmt->execute([
        ':name'  => 'Admin Sport Shop',
        ':email' => 'admin@sportshop.local',
        ':hash'  => $hash,
        ':phone' => '0901234567',
    ]);
    
    info("✓ Admin user đã tạo:");
    info("  Email: admin@sportshop.local");
    info("  Password: " . ($adminPassword === 'admin123' ? 'admin123 (MẬC ĐỊNH - HÃY ĐỔI!)' : '(từ env/input)'));
}

// ─── Create demo customer ───────────────────────────────────────────
$custExists = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
if ($custExists === 0) {
    $custHash = password_hash('customer123', PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password_hash, phone, role, status)
        VALUES (:name, :email, :hash, :phone, 'customer', 'active')
    ");
    $stmt->execute([
        ':name'  => 'Nguyễn Văn Khách',
        ':email' => 'customer@sportshop.local',
        ':hash'  => $custHash,
        ':phone' => '0909876543',
    ]);
    info("✓ Demo customer đã tạo:");
    info("  Email: customer@sportshop.local");
    info("  Password: customer123");
}

echo "\n";
info("🏆 Seed hoàn tất! Bạn có thể đăng nhập và sử dụng hệ thống.");
exit(0);

