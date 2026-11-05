-- Migration 014: Create order_status_history table
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    from_status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'cancelled') DEFAULT NULL,
    to_status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'cancelled') NOT NULL,
    actor_user_id INT UNSIGNED DEFAULT NULL,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_history_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_status_history_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_status_history_order_id (order_id),
    INDEX idx_status_history_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
