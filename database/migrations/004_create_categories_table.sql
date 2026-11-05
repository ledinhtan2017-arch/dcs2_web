-- Migration 004: Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sport_id INT UNSIGNED NOT NULL,
    parent_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    image_path VARCHAR(500) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categories_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_categories_sport_id (sport_id),
    INDEX idx_categories_parent_id (parent_id),
    INDEX idx_categories_slug (slug),
    INDEX idx_categories_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
