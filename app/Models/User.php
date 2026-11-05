<?php
/**
 * User Model
 */

namespace App\Models;

use App\Core\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, phone, role, status, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, phone, role, status)
            VALUES (:name, :email, :hash, :phone, :role, 'active')
        ");
        $stmt->execute([
            ':name'  => $data['name'],
            ':email' => $data['email'],
            ':hash'  => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':phone' => $data['phone'] ?? null,
            ':role'  => $data['role'] ?? 'customer',
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getConnection();
        $sets = [];
        $params = [':id' => $id];

        foreach (['name', 'phone', 'email'] as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (isset($data['password']) && $data['password'] !== '') {
            $sets[] = "password_hash = :hash";
            $params[':hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (empty($sets)) return;

        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id";
        $pdo->prepare($sql)->execute($params);
    }
}

