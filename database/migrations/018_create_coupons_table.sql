-- Migration 018: Create coupons table (P1)
CREATE TABLE IF NOT EXISTS coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(12,0) NOT NULL,
    min_order_amount DECIMAL(12,0) NOT NULL DEFAULT 0,
    max_discount DECIMAL(12,0) DEFAULT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    usage_limit INT UNSIGNED DEFAULT NULL,
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_coupons_code (code),
    INDEX idx_coupons_active_dates (is_active, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
