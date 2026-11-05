<?php
/**
 * Category Model
 */

namespace App\Models;

use App\Core\Database;

class Category
{
    public static function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, s.name AS sport_name, s.slug AS sport_slug
            FROM categories c
            JOIN sports s ON s.id = c.sport_id
            WHERE c.slug = :slug AND c.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function all(bool $activeOnly = true): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT c.*, s.name AS sport_name FROM categories c JOIN sports s ON s.id = c.sport_id"
             . ($activeOnly ? " WHERE c.is_active = 1" : "")
             . " ORDER BY c.sport_id, c.sort_order ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function getBySport(int $sportId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM categories
            WHERE sport_id = :sport_id AND is_active = 1
            ORDER BY sort_order ASC
        ");
        $stmt->execute([':sport_id' => $sportId]);
        return $stmt->fetchAll();
    }
}

