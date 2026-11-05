<?php
namespace App\Controllers\Api\Admin;

use App\Core\Database;
use App\Core\Response;

class ProductImageApiController
{
    public function store(string $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT name FROM products WHERE id = ?');
        $stmt->execute([(int) $id]);
        $product = $stmt->fetch();
        if (!$product) Response::error('Không tìm thấy sản phẩm.', 'NOT_FOUND', 404);

        $file = $_FILES['image'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Response::error('Không tải được ảnh. Hãy chọn ảnh phù hợp với giới hạn tải lên của máy chủ.', 'UPLOAD_ERROR', 422);
        }
        if ($file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
            Response::error('Ảnh phải có dung lượng tối đa 5 MB.', 'INVALID_IMAGE', 422);
        }
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $imageInfo = @getimagesize($file['tmp_name']);
        if (!isset($types[$mime]) || !$imageInfo || ($imageInfo['mime'] ?? '') !== $mime) {
            Response::error('Chỉ chấp nhận ảnh JPEG, PNG hoặc WebP hợp lệ.', 'INVALID_IMAGE', 422);
        }

        $directory = PUBLIC_PATH . '/assets/images/products/uploads';
        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            Response::error('Không thể tạo thư mục lưu ảnh.', 'UPLOAD_ERROR', 500);
        }
        $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
        $path = $directory . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            Response::error('Không thể lưu ảnh.', 'UPLOAD_ERROR', 500);
        }

        try {
            $pdo->beginTransaction();
            $lock = $pdo->prepare('SELECT id FROM products WHERE id = ? FOR UPDATE');
            $lock->execute([(int) $id]);
            if (!$lock->fetch()) throw new \RuntimeException('Sản phẩm không còn tồn tại.');
            $current = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1');
            $current->execute([(int) $id]);
            $primary = !empty($_POST['is_primary']) || (int) $current->fetchColumn() === 0;
            if ($primary) {
                $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ?')->execute([(int) $id]);
            }
            $stmt = $pdo->prepare('INSERT INTO product_images (product_id, image_path, alt_text, is_primary) VALUES (?, ?, ?, ?)');
            $stmt->execute([(int) $id, 'assets/images/products/uploads/' . $name, $product['name'], $primary ? 1 : 0]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            @unlink($path);
            error_log('[ProductImage] ' . $e->getMessage());
            Response::error('Không thể lưu thông tin ảnh.', 'UPLOAD_ERROR', 500);
        }
        Response::success(['message' => 'Đã tải ảnh lên.'], null, 201);
    }

    public function setPrimary(string $id, string $imageId): void
    {
        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();
            $lock = $pdo->prepare('SELECT id FROM products WHERE id = ? FOR UPDATE');
            $lock->execute([(int) $id]);
            $stmt = $pdo->prepare('SELECT id FROM product_images WHERE id = ? AND product_id = ?');
            $stmt->execute([(int) $imageId, (int) $id]);
            if (!$lock->fetch() || !$stmt->fetch()) {
                $pdo->rollBack();
                Response::error('Không tìm thấy ảnh sản phẩm.', 'NOT_FOUND', 404);
            }
            $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ?')->execute([(int) $id]);
            $pdo->prepare('UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?')->execute([(int) $imageId, (int) $id]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[ProductImage] ' . $e->getMessage());
            Response::error('Không thể cập nhật ảnh chính.', 'UPDATE_ERROR', 500);
        }
        Response::success(['message' => 'Đã chọn ảnh chính.']);
    }
}
