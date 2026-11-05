-- Migration 008: Create product_variants table
CREATE TABLE IF NOT EXISTS product_variants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    size_label VARCHAR(50) DEFAULT NULL,
    color_name VARCHAR(50) DEFAULT NULL,
    attributes_json JSON DEFAULT NULL,
    price DECIMAL(12,0) NOT NULL,
    sale_price DECIMAL(12,0) DEFAULT NULL,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_variants_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_sale_price CHECK (sale_price IS NULL OR sale_price < price),
    CONSTRAINT chk_stock_quantity CHECK (stock_quantity >= 0),
    INDEX idx_variants_product_id (product_id),
    INDEX idx_variants_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
