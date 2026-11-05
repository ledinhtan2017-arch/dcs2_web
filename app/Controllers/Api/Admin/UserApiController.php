<?php
/**
 * Admin User API Controller – List users and lock/unlock.
 */

namespace App\Controllers\Api\Admin;

use App\Core\{Response, Database};

class UserApiController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $users = $pdo->query("SELECT id, name, email, phone, role, status, created_at FROM users ORDER BY created_at DESC")->fetchAll();
        Response::success($users);
    }

    public function update(string $id): void
    {
        $request = new \App\Core\Request();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::error('Không tìm thấy người dùng.', 'NOT_FOUND', 404);
            return;
        }

        // Prevent locking admin accounts via API
        if ($user['role'] === 'admin') {
            Response::error('Không thể cập nhật tài khoản admin.', 'FORBIDDEN', 403);
            return;
        }

        $status = $request->json('status');
        if ($status === null || !in_array($status, ['active', 'locked'], true)) {
            Response::error('Trạng thái không hợp lệ. Chỉ chấp nhận: active, locked.', 'VALIDATION_ERROR', 422);
            return;
        }

        $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);

        $label = $status === 'locked' ? 'khóa' : 'mở khóa';
        Response::success(null, "Đã {$label} người dùng thành công.");
    }
}

