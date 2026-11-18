<?php
namespace App\Models;

use App\Core\Database;

class Review
{
    public static function forProduct(int $productId): array
    {
        $stmt = Database::getConnection()->prepare("SELECT r.rating, r.body, r.created_at, u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? AND r.status='approved' ORDER BY r.created_at DESC, r.id DESC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public static function eligibleItem(int $productId, int $userId): ?int
    {
        $stmt = Database::getConnection()->prepare("SELECT oi.id FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN product_variants pv ON pv.id=oi.variant_id LEFT JOIN reviews r ON r.order_item_id=oi.id WHERE o.user_id=? AND o.order_status='delivered' AND pv.product_id=? AND r.id IS NULL ORDER BY oi.id DESC LIMIT 1");
        $stmt->execute([$userId, $productId]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int)$id;
    }

    public static function create(int $itemId, int $userId, int $rating, string $body): bool
    {
        if ($rating < 1 || $rating > 5 || mb_strlen($body) > 2000) return false;
        $stmt = Database::getConnection()->prepare("INSERT INTO reviews (user_id, product_id, order_item_id, rating, body, status) SELECT o.user_id, pv.product_id, oi.id, ?, ?, 'approved' FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN product_variants pv ON pv.id=oi.variant_id WHERE oi.id=? AND o.user_id=? AND o.order_status='delivered' AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.order_item_id=oi.id)");
        try {
            $stmt->execute([$rating, $body, $itemId, $userId]);
            return $stmt->rowCount() === 1;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') return false;
            throw $e;
        }
    }
}
