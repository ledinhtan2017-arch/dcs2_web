-- Migration 019: Create banners table (P1)
CREATE TABLE IF NOT EXISTS banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    subtitle VARCHAR(500) DEFAULT NULL,
    image_path VARCHAR(500) NOT NULL,
    link_url VARCHAR(500) NOT NULL,
    position ENUM('hero', 'sport', 'promo') NOT NULL DEFAULT 'hero',
    sort_order INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_banners_position (position),
    INDEX idx_banners_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
