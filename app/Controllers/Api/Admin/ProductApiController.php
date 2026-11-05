<?php
namespace App\Controllers\Api\Admin;
use App\Core\{Response, Database};

class ProductApiController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $products = $pdo->query("
            SELECT p.*, b.name AS brand_name, c.name AS category_name,
                   (SELECT SUM(stock_quantity) FROM product_variants WHERE product_id=p.id) AS total_stock
            FROM products p LEFT JOIN brands b ON b.id=p.brand_id LEFT JOIN categories c ON c.id=p.category_id
            ORDER BY p.id DESC
        ")->fetchAll();
        Response::success($products);
    }

    public function show(string $id): void
    {
        $product = \App\Models\Product::findById((int)$id);
        if (!$product) Response::error('Không tìm thấy.', 'NOT_FOUND', 404);
        $product['variants'] = \App\Models\Product::getVariants((int)$id);
        $product['images'] = \App\Models\Product::getImages((int)$id);
        Response::success($product);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    private function save(?int $id): void
    {
        $request = new \App\Core\Request();
        $data = $request->json() ?: $request->all();
        $pdo = Database::getConnection();
        $created = $id === null;
        try {
            if ($id === null && (!isset($data['name'], $data['category_id']) || !isset($data['variants']))) {
                throw new \InvalidArgumentException('Vui lòng nhập tên, danh mục và ít nhất một biến thể.');
            }
            $fields = [];
            foreach (['name' => 255, 'short_description' => 500, 'description' => 65535] as $key => $max) {
                if (!array_key_exists($key, $data)) continue;
                if (!is_string($data[$key]) || mb_strlen($data[$key]) > $max || ($key === 'name' && trim($data[$key]) === '')) {
                    throw new \InvalidArgumentException('Thông tin sản phẩm không hợp lệ: ' . $key);
                }
                $fields[$key] = trim($data[$key]);
            }
            if (array_key_exists('category_id', $data)) $fields['category_id'] = $this->number($data['category_id'], 4294967295, 'Danh mục', false);
            if (array_key_exists('brand_id', $data)) $fields['brand_id'] = ($data['brand_id'] === null || $data['brand_id'] === '') ? null : $this->number($data['brand_id'], 4294967295, 'Thương hiệu', false);
            if (array_key_exists('status', $data)) {
                if (!in_array($data['status'], ['active', 'inactive', 'draft'], true)) throw new \InvalidArgumentException('Trạng thái sản phẩm không hợp lệ.');
                $fields['status'] = $data['status'];
            }
            if (array_key_exists('is_featured', $data)) $fields['is_featured'] = $this->boolean($data['is_featured']);
            $variants = array_key_exists('variants', $data) ? $this->validateVariants($data['variants']) : null;
            $sports = null;
            if (array_key_exists('sport_ids', $data)) {
                if (!is_array($data['sport_ids']) || count($data['sport_ids']) > 100) throw new \InvalidArgumentException('Danh sách môn thể thao không hợp lệ.');
                $sports = [];
                foreach ($data['sport_ids'] as $sport) $sports[] = $this->number($sport, 4294967295, 'Môn thể thao', false);
                $sports = array_unique($sports);
            }

            $pdo->beginTransaction();
            if ($id === null) {
                $slug = $this->generateSlug($fields['name']);
                $stmt = $pdo->prepare('INSERT INTO products (name, category_id, brand_id, slug, short_description, description, status, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$fields['name'], $fields['category_id'], $fields['brand_id'] ?? null, $slug, $fields['short_description'] ?? '', $fields['description'] ?? '', $fields['status'] ?? 'draft', $fields['is_featured'] ?? 0]);
                $id = (int)$pdo->lastInsertId();
            } else {
                $stmt = $pdo->prepare('SELECT id, slug FROM products WHERE id = ? FOR UPDATE');
                $stmt->execute([$id]);
                $product = $stmt->fetch();
                if (!$product) throw new \OutOfBoundsException('Không tìm thấy sản phẩm.');
                $slug = $product['slug'];
                if ($fields) {
                    $assignments = [];
                    foreach (array_keys($fields) as $key) $assignments[] = "$key = ?";
                    $stmt = $pdo->prepare('UPDATE products SET ' . implode(', ', $assignments) . ' WHERE id = ?');
                    $stmt->execute(array_merge(array_values($fields), [$id]));
                }
            }
            if ($sports !== null) {
                $pdo->prepare('DELETE FROM product_sports WHERE product_id = ?')->execute([$id]);
                $stmt = $pdo->prepare('INSERT INTO product_sports (product_id, sport_id) VALUES (?, ?)');
                foreach ($sports as $sport) $stmt->execute([$id, $sport]);
            }
            if ($variants !== null) {
                $insert = $pdo->prepare('INSERT INTO product_variants (product_id, sku, size_label, color_name, price, sale_price, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $update = $pdo->prepare('UPDATE product_variants SET sku = ?, size_label = ?, color_name = ?, price = ?, sale_price = ?, stock_quantity = ?, is_active = ? WHERE id = ? AND product_id = ?');
                $check = $pdo->prepare('SELECT stock_quantity FROM product_variants WHERE id = ? AND product_id = ? FOR UPDATE');
                foreach ($variants as $variant) {
                    $values = [$variant['sku'], $variant['size_label'], $variant['color_name'], $variant['price'], $variant['sale_price'], $variant['stock_quantity'], $variant['is_active']];
                    if ($variant['id'] !== null) {
                        $check->execute([$variant['id'], $id]);
                        $current = $check->fetch();
                        if (!$current) throw new \InvalidArgumentException('Biến thể không thuộc sản phẩm này.');
                        if ((int)$current['stock_quantity'] !== $variant['original_stock']) {
                            throw new \InvalidArgumentException('Tồn kho đã thay đổi. Vui lòng tải lại trang trước khi lưu.');
                        }
                        $update->execute(array_merge($values, [$variant['id'], $id]));
                    } else {
                        $insert->execute(array_merge([$id], $values));
                    }
                }
            }
            $pdo->commit();
            Response::success(['id' => $id, 'slug' => $slug], null, $created ? 201 : 200);
        } catch (\OutOfBoundsException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            Response::error($e->getMessage(), 'NOT_FOUND', 404);
        } catch (\InvalidArgumentException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            Response::error($e->getMessage(), 'VALIDATION_ERROR', 422);
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[Product save] ' . $e->getMessage());
            Response::error('Không thể lưu sản phẩm. Kiểm tra SKU trùng và các danh mục, thương hiệu, môn thể thao đã chọn.', 'SAVE_ERROR', 422);
        }
    }

    private function validateVariants(mixed $variants): array
    {
        if (!is_array($variants) || count($variants) === 0 || count($variants) > 100) throw new \InvalidArgumentException('Hãy nhập từ 1 đến 100 biến thể.');
        $result = []; $seenIds = []; $seenSkus = [];
        foreach ($variants as $variant) {
            if (!is_array($variant) || !isset($variant['sku']) || !is_string($variant['sku'])) throw new \InvalidArgumentException('SKU không hợp lệ.');
            $sku = trim($variant['sku']);
            if ($sku === '' || mb_strlen($sku) > 100 || isset($seenSkus[mb_strtolower($sku)])) throw new \InvalidArgumentException('SKU phải có giá trị và không trùng trong sản phẩm.');
            $seenSkus[mb_strtolower($sku)] = true;
            $variantId = empty($variant['id']) ? null : $this->number($variant['id'], 4294967295, 'ID biến thể', false);
            if ($variantId !== null && isset($seenIds[$variantId])) throw new \InvalidArgumentException('Biến thể bị lặp.');
            if ($variantId !== null) $seenIds[$variantId] = true;
            $size = $variant['size_label'] ?? ''; $color = $variant['color_name'] ?? '';
            if (!is_string($size) || mb_strlen($size) > 50 || !is_string($color) || mb_strlen($color) > 50) throw new \InvalidArgumentException('Kích thước hoặc màu sắc không hợp lệ.');
            $price = $this->number($variant['price'] ?? null, 999999999999, 'Giá', true);
            $sale = ($variant['sale_price'] ?? '') === '' || $variant['sale_price'] === null ? null : $this->number($variant['sale_price'], 999999999999, 'Giá khuyến mãi', false);
            if ($sale !== null && $sale >= $price) throw new \InvalidArgumentException('Giá khuyến mãi phải nhỏ hơn giá gốc.');
            $originalStock = $variantId === null ? null : $this->number($variant['original_stock'] ?? null, 4294967295, 'Tồn kho ban đầu', true);
            $result[] = ['id' => $variantId, 'original_stock' => $originalStock, 'sku' => $sku, 'size_label' => trim($size) ?: null, 'color_name' => trim($color) ?: null,
                'price' => $price, 'sale_price' => $sale, 'stock_quantity' => $this->number($variant['stock_quantity'] ?? null, 4294967295, 'Tồn kho', true),
                'is_active' => $this->boolean($variant['is_active'] ?? true)];
        }
        return $result;
    }

    private function number(mixed $value, int $max, string $label, bool $zeroAllowed): int
    {
        if ((!is_string($value) && !is_int($value)) || !preg_match('/^\d{1,12}$/D', (string)$value) || (int)$value > $max || (!$zeroAllowed && (int)$value === 0)) {
            throw new \InvalidArgumentException($label . ' không hợp lệ.');
        }
        return (int)$value;
    }

    private function boolean(mixed $value): int
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($result === null) throw new \InvalidArgumentException('Giá trị trạng thái không hợp lệ.');
        return $result ? 1 : 0;
    }

    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $map = ['à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a','đ'=>'d','è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e','ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i','ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o','ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u','ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y'];
        $slug = strtr($slug, $map);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-') . '-' . substr(uniqid(), -4);
    }
}

