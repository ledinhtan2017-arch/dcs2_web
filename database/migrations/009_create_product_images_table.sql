-- Migration 009: Create product_images table
CREATE TABLE IF NOT EXISTS product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    image_path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_images_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_images_product_id (product_id),
    INDEX idx_images_variant_id (variant_id),
    INDEX idx_images_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
