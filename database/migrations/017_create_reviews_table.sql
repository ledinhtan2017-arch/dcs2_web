-- Migration 017: Create reviews table (P1)
CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    order_item_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    title VARCHAR(200) DEFAULT NULL,
    body TEXT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reviews_order_item (order_item_id),
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_order_item FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_rating_range CHECK (rating >= 1 AND rating <= 5),
    INDEX idx_reviews_product_id (product_id),
    INDEX idx_reviews_user_id (user_id),
    INDEX idx_reviews_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
