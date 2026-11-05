-- Migration 007: Create product_sports pivot table (many-to-many)
CREATE TABLE IF NOT EXISTS product_sports (
    product_id INT UNSIGNED NOT NULL,
    sport_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, sport_id),
    CONSTRAINT fk_product_sports_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_product_sports_sport FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
