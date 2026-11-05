<?php
/**
 * Admin Sport API Controller – CRUD for sports.
 */

namespace App\Controllers\Api\Admin;

use App\Core\{Response, Database, Validator};

class SportApiController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $sports = $pdo->query("SELECT * FROM sports ORDER BY sort_order, name")->fetchAll();
        Response::success($sports);
    }

    public function store(): void
    {
        $request = new \App\Core\Request();
        $data = [
            'name'       => trim($request->json('name', '')),
            'slug'       => trim($request->json('slug', '')),
            'sort_order' => (int) $request->json('sort_order', 0),
        ];

        $v = Validator::validate($data, [
            'name' => 'required|string|min:2|max:100',
        ], ['name' => 'Tên môn thể thao']);

        if (!$v['valid']) {
            Response::error(implode(' ', $v['errors']), 'VALIDATION_ERROR', 422);
            return;
        }

        if (empty($data['slug'])) {
            $data['slug'] = self::slugify($data['name']);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO sports (name, slug, sort_order) VALUES (:name, :slug, :sort_order)");
        $stmt->execute([
            ':name'       => $data['name'],
            ':slug'       => $data['slug'],
            ':sort_order' => $data['sort_order'],
        ]);

        Response::success(['id' => (int) $pdo->lastInsertId()], 'Thêm thành công.', 201);
    }

    public function update(string $id): void
    {
        $request = new \App\Core\Request();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM sports WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            Response::error('Không tìm thấy.', 'NOT_FOUND', 404);
            return;
        }

        $fields = [];
        $params = [':id' => $id];

        $name = $request->json('name');
        if ($name !== null) {
            $fields[] = 'name = :name';
            $params[':name'] = trim($name);
        }

        $slug = $request->json('slug');
        if ($slug !== null) {
            $fields[] = 'slug = :slug';
            $params[':slug'] = trim($slug);
        }

        $sortOrder = $request->json('sort_order');
        if ($sortOrder !== null) {
            $fields[] = 'sort_order = :sort_order';
            $params[':sort_order'] = (int) $sortOrder;
        }

        $isActive = $request->json('is_active');
        if ($isActive !== null) {
            $fields[] = 'is_active = :is_active';
            $params[':is_active'] = (int) $isActive;
        }

        if (empty($fields)) {
            Response::error('Không có dữ liệu cập nhật.', 'NO_DATA', 400);
            return;
        }

        $sql = "UPDATE sports SET " . implode(', ', $fields) . " WHERE id = :id";
        $pdo->prepare($sql)->execute($params);

        Response::success(null, 'Cập nhật thành công.');
    }

    private static function slugify(string $text): string
    {
        $map = ['à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a','è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e','ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i','ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o','ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u','ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d','Đ'=>'d'];
        $text = strtr(mb_strtolower($text, 'UTF-8'), $map);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}

