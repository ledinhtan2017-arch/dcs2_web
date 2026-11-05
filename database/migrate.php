<?php
/**
 * Database Migration Runner
 * 
 * Reads SQL migration files from the migrations/ directory,
 * executes them in order, and tracks which have been run
 * in a `migrations` table.
 *
 * Usage: php database/migrate.php
 */

declare(strict_types=1);

// ─── Load environment ───────────────────────────────────────────────
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env.example';
}
if (!file_exists($envFile)) {
    fwrite(STDERR, "[LỖI] Không tìm thấy file .env hoặc .env.example\n");
    exit(1);
}

$envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($envLines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }
    if (str_contains($line, '=')) {
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Remove surrounding quotes
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

$dbHost     = $_ENV['DB_HOST']     ?? 'localhost';
$dbPort     = $_ENV['DB_PORT']     ?? '3306';
$dbName     = $_ENV['DB_DATABASE'] ?? 'sport_shop';
$dbUser     = $_ENV['DB_USERNAME'] ?? 'root';
$dbPassword = $_ENV['DB_PASSWORD'] ?? '';

// ─── Helper functions ───────────────────────────────────────────────
function info(string $msg): void
{
    echo "\033[32m[INFO]\033[0m $msg\n";
}

function warn(string $msg): void
{
    echo "\033[33m[CẢNH BÁO]\033[0m $msg\n";
}

function error(string $msg): void
{
    fwrite(STDERR, "\033[31m[LỖI]\033[0m $msg\n");
}

// ─── Connect without DB first (to create it if needed) ──────────────
try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    info("Đã kết nối MySQL server ($dbHost:$dbPort)");
} catch (PDOException $e) {
    error("Không thể kết nối MySQL: " . $e->getMessage());
    exit(1);
}

// ─── Read migration files ───────────────────────────────────────────
$migrationsDir = __DIR__ . '/migrations';
if (!is_dir($migrationsDir)) {
    error("Thư mục migrations không tồn tại: $migrationsDir");
    exit(1);
}

$files = glob($migrationsDir . '/*.sql');
if ($files === false || count($files) === 0) {
    warn("Không tìm thấy file migration nào trong $migrationsDir");
    exit(0);
}

// Sort files by name (numeric prefix ensures correct order)
sort($files, SORT_NATURAL);

info("Tìm thấy " . count($files) . " file migration");

// ─── Execute migration 000 first (creates DB) ──────────────────────
$firstFile = $files[0];
$firstName = basename($firstFile);
if (str_starts_with($firstName, '000_')) {
    $sql = file_get_contents($firstFile);
    if ($sql === false) {
        error("Không đọc được file: $firstName");
        exit(1);
    }
    try {
        // Execute the CREATE DATABASE / USE statements
        $pdo->exec($sql);
        info("✓ Đã thực thi: $firstName (tạo database)");
    } catch (PDOException $e) {
        error("Lỗi khi thực thi $firstName: " . $e->getMessage());
        exit(1);
    }
    // Remove from the list
    array_shift($files);
}

// ─── Reconnect to the specific database ─────────────────────────────
try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    info("Đã kết nối database: $dbName");
} catch (PDOException $e) {
    error("Không thể kết nối database $dbName: " . $e->getMessage());
    exit(1);
}

// ─── Create migrations tracking table ───────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT UNSIGNED NOT NULL DEFAULT 1,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
info("Bảng migrations tracking đã sẵn sàng");

// ─── Get already-executed migrations ────────────────────────────────
$stmt = $pdo->query("SELECT migration FROM migrations ORDER BY id");
$executed = $stmt->fetchAll(PDO::FETCH_COLUMN);
$executedSet = array_flip($executed);

// Determine next batch number
$stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 AS next_batch FROM migrations");
$nextBatch = (int) $stmt->fetch()['next_batch'];

// ─── Run pending migrations ─────────────────────────────────────────
$ran     = 0;
$skipped = 0;
$failed  = 0;

foreach ($files as $file) {
    $name = basename($file);

    // Skip if already executed
    if (isset($executedSet[$name])) {
        $skipped++;
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        error("Không đọc được file: $name");
        $failed++;
        continue;
    }

    // Trim and skip empty files
    $sql = trim($sql);
    if ($sql === '') {
        warn("File trống, bỏ qua: $name");
        $skipped++;
        continue;
    }

    try {
        // DDL statements (CREATE TABLE, ALTER, DROP) auto-commit in MySQL
        // so we cannot wrap them in a transaction. Execute SQL first,
        // then record the migration separately.
        $pdo->exec($sql);

        // Record the migration
        $insertStmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:name, :batch)");
        $insertStmt->execute([
            ':name'  => $name,
            ':batch' => $nextBatch,
        ]);

        info("✓ Migration thành công: $name");
        $ran++;
    } catch (PDOException $e) {
        error("✗ Migration thất bại [$name]: " . $e->getMessage());
        $failed++;
        // Stop on failure to prevent cascading errors
        error("Dừng migration do lỗi. Vui lòng sửa lỗi và chạy lại.");
        exit(1);
    }
}

// ─── Summary ────────────────────────────────────────────────────────
echo "\n";
echo "╔══════════════════════════════════════════╗\n";
echo "║        KẾT QUẢ MIGRATION                ║\n";
echo "╠══════════════════════════════════════════╣\n";
printf("║  Đã chạy thành công : %-18d ║\n", $ran);
printf("║  Đã bỏ qua (đã chạy): %-17d ║\n", $skipped);
printf("║  Thất bại           : %-18d ║\n", $failed);
echo "╚══════════════════════════════════════════╝\n";

if ($ran === 0 && $failed === 0) {
    info("Không có migration mới nào cần chạy. Database đã cập nhật.");
}

exit($failed > 0 ? 1 : 0);
