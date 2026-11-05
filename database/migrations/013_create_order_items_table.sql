-- Migration 013: Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    sku_snapshot VARCHAR(100) NOT NULL,
    name_snapshot VARCHAR(255) NOT NULL,
    options_snapshot VARCHAR(255) DEFAULT NULL,
    unit_price DECIMAL(12,0) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    line_total DECIMAL(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_order_items_order_id (order_id),
    INDEX idx_order_items_variant_id (variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
