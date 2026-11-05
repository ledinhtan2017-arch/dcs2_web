<?php
/**
 * Sport Model – Database queries for sports.
 */

namespace App\Models;

use App\Core\Database;

class Sport
{
    public static function all(bool $activeOnly = true): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM sports" . ($activeOnly ? " WHERE is_active = 1" : "") . " ORDER BY sort_order ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM sports WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM sports WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getCategories(int $sportId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, COUNT(p.id) AS product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
            WHERE c.sport_id = :sport_id AND c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ");
        $stmt->execute([':sport_id' => $sportId]);
        return $stmt->fetchAll();
    }
}

