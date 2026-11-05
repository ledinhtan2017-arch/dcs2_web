<?php
/**
 * Product Model – Handles product queries with filtering, sorting, pagination.
 */

namespace App\Models;

use App\Core\Database;

class Product
{
    /**
     * List products with filters, sorting and pagination.
     */
    public static function list(array $filters = [], int $page = 1, int $perPage = 12, string $sort = 'newest'): array
    {
        $pdo = Database::getConnection();
        $where = ["p.status = 'active'"];
        $params = [];

        // Filter: sport
        if (!empty($filters['sport'])) {
            $where[] = "ps.sport_id = :sport_id";
            $params[':sport_id'] = (int) $filters['sport'];
        }

        // Filter: category
        if (!empty($filters['category'])) {
            $where[] = "p.category_id = :category_id";
            $params[':category_id'] = (int) $filters['category'];
        }

        // Filter: brand
        if (!empty($filters['brand'])) {
            $where[] = "p.brand_id = :brand_id";
            $params[':brand_id'] = (int) $filters['brand'];
        }

        // Filter: search
        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.short_description LIKE :search2 OR pv.sku LIKE :search3 OR b.name LIKE :search4)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[':search'] = $searchTerm;
            $params[':search2'] = $searchTerm;
            $params[':search3'] = $searchTerm;
            $params[':search4'] = $searchTerm;
        }

        // Filter: price range
        if (!empty($filters['min_price'])) {
            $where[] = "COALESCE(pv_agg.min_effective_price, 0) >= :min_price";
            $params[':min_price'] = (int) $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = "COALESCE(pv_agg.min_effective_price, 0) <= :max_price";
            $params[':max_price'] = (int) $filters['max_price'];
        }

        // Filter: size
        if (!empty($filters['size'])) {
            $where[] = "EXISTS (SELECT 1 FROM product_variants pvsz WHERE pvsz.product_id = p.id AND pvsz.size_label = :size AND pvsz.is_active = 1 AND pvsz.stock_quantity > 0)";
            $params[':size'] = $filters['size'];
        }

        // Filter: in_stock
        if (!empty($filters['in_stock'])) {
            $where[] = "pv_agg.total_stock > 0";
        }

        $whereClause = implode(' AND ', $where);

        // Sorting
        $allowedSorts = [
            'newest'     => 'p.created_at DESC',
            'oldest'     => 'p.created_at ASC',
            'price_asc'  => 'pv_agg.min_effective_price ASC',
            'price_desc' => 'pv_agg.min_effective_price DESC',
            'name_asc'   => 'p.name ASC',
            'name_desc'  => 'p.name DESC',
        ];
        $orderBy = $allowedSorts[$sort] ?? $allowedSorts['newest'];

        // Aggregate subquery for price/stock
        $aggJoin = "
            LEFT JOIN (
                SELECT product_id,
                       MIN(COALESCE(sale_price, price)) AS min_effective_price,
                       MAX(price) AS max_price,
                       MIN(COALESCE(sale_price, price)) AS min_price,
                       SUM(stock_quantity) AS total_stock
                FROM product_variants
                WHERE is_active = 1
                GROUP BY product_id
            ) pv_agg ON pv_agg.product_id = p.id
        ";

        // Count total
        $countSql = "
            SELECT COUNT(DISTINCT p.id)
            FROM products p
            LEFT JOIN product_sports ps ON ps.product_id = p.id
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
            $aggJoin
            WHERE $whereClause
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Pagination
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        // Main query
        $sql = "
            SELECT DISTINCT p.id, p.name, p.slug, p.short_description, p.status, p.is_featured,
                   p.category_id, p.brand_id, p.created_at,
                   b.name AS brand_name, b.slug AS brand_slug,
                   c.name AS category_name, c.slug AS category_slug,
                   pv_agg.min_effective_price, pv_agg.max_price, pv_agg.min_price, pv_agg.total_stock,
                   pi.image_path AS primary_image, pi.alt_text AS primary_image_alt
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN product_sports ps ON ps.product_id = p.id
            LEFT JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
            $aggJoin
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE $whereClause
            ORDER BY $orderBy
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        // Check if any product has a sale
        foreach ($products as &$product) {
            $product['has_sale'] = $product['min_effective_price'] < $product['max_price'];
        }

        return [
            'products'     => $products,
            'total'        => $total,
            'page'         => $page,
            'per_page'     => $perPage,
            'total_pages'  => $totalPages,
        ];
    }

    /**
     * Get a single product by slug with all details.
     */
    public static function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, b.name AS brand_name, b.slug AS brand_slug,
                   c.name AS category_name, c.slug AS category_slug,
                   s.name AS sport_name, s.slug AS sport_slug
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN product_sports ps ON ps.product_id = p.id
            LEFT JOIN sports s ON s.id = ps.sport_id
            WHERE p.slug = :slug AND p.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $product = $stmt->fetch();
        if (!$product) return null;

        // Get variants
        $product['variants'] = self::getVariants($product['id']);

        // Get images
        $product['images'] = self::getImages($product['id']);

        // Price range
        $prices = array_column($product['variants'], 'effective_price');
        $product['min_price'] = $prices ? min($prices) : 0;
        $product['max_price'] = array_column($product['variants'], 'price') ? max(array_column($product['variants'], 'price')) : 0;
        $product['total_stock'] = array_sum(array_column($product['variants'], 'stock_quantity'));

        return $product;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getVariants(int $productId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT *, COALESCE(sale_price, price) AS effective_price
            FROM product_variants
            WHERE product_id = :pid AND is_active = 1
            ORDER BY size_label ASC, color_name ASC
        ");
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll();
    }

    public static function getImages(int $productId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM product_images
            WHERE product_id = :pid
            ORDER BY is_primary DESC, sort_order ASC
        ");
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll();
    }

    public static function getFeatured(int $limit = 8): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, b.name AS brand_name,
                   pi.image_path AS primary_image, pi.alt_text AS primary_image_alt,
                   MIN(COALESCE(pv.sale_price, pv.price)) AS min_effective_price,
                   MAX(pv.price) AS max_price,
                   SUM(pv.stock_quantity) AS total_stock
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            LEFT JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
            WHERE p.status = 'active' AND p.is_featured = 1
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();
        foreach ($products as &$p) {
            $p['has_sale'] = $p['min_effective_price'] < $p['max_price'];
        }
        return $products;
    }

    public static function getNewArrivals(int $limit = 8): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, b.name AS brand_name,
                   pi.image_path AS primary_image, pi.alt_text AS primary_image_alt,
                   MIN(COALESCE(pv.sale_price, pv.price)) AS min_effective_price,
                   MAX(pv.price) AS max_price
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            LEFT JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
            WHERE p.status = 'active'
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();
        foreach ($products as &$p) {
            $p['has_sale'] = $p['min_effective_price'] < $p['max_price'];
        }
        return $products;
    }

    public static function getBySport(int $sportId, int $limit = 8): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, b.name AS brand_name,
                   pi.image_path AS primary_image, pi.alt_text AS primary_image_alt,
                   MIN(COALESCE(pv.sale_price, pv.price)) AS min_effective_price,
                   MAX(pv.price) AS max_price
            FROM products p
            JOIN product_sports ps ON ps.product_id = p.id AND ps.sport_id = :sport_id
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            LEFT JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
            WHERE p.status = 'active'
            GROUP BY p.id
            ORDER BY p.is_featured DESC, p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':sport_id', $sportId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get available filter options for a given context.
     */
    public static function getFilterOptions(?int $sportId = null, ?int $categoryId = null): array
    {
        $pdo = Database::getConnection();

        // Sizes
        $sizeWhere = "pv.is_active = 1 AND pv.stock_quantity > 0 AND p.status = 'active'";
        $sizeParams = [];
        if ($sportId) {
            $sizeWhere .= " AND EXISTS (SELECT 1 FROM product_sports ps2 WHERE ps2.product_id = p.id AND ps2.sport_id = :s_sport)";
            $sizeParams[':s_sport'] = $sportId;
        }
        if ($categoryId) {
            $sizeWhere .= " AND p.category_id = :s_cat";
            $sizeParams[':s_cat'] = $categoryId;
        }
        $sizeStmt = $pdo->prepare("
            SELECT DISTINCT pv.size_label
            FROM product_variants pv
            JOIN products p ON p.id = pv.product_id
            WHERE $sizeWhere AND pv.size_label IS NOT NULL
            ORDER BY pv.size_label
        ");
        $sizeStmt->execute($sizeParams);
        $sizes = $sizeStmt->fetchAll(\PDO::FETCH_COLUMN);

        // Brands
        $brandWhere = "p.status = 'active' AND p.brand_id IS NOT NULL";
        $brandParams = [];
        if ($sportId) {
            $brandWhere .= " AND EXISTS (SELECT 1 FROM product_sports ps3 WHERE ps3.product_id = p.id AND ps3.sport_id = :b_sport)";
            $brandParams[':b_sport'] = $sportId;
        }
        if ($categoryId) {
            $brandWhere .= " AND p.category_id = :b_cat";
            $brandParams[':b_cat'] = $categoryId;
        }
        $brandStmt = $pdo->prepare("
            SELECT DISTINCT b.id, b.name, b.slug
            FROM brands b
            JOIN products p ON p.brand_id = b.id
            WHERE $brandWhere
            ORDER BY b.name
        ");
        $brandStmt->execute($brandParams);
        $brands = $brandStmt->fetchAll();

        // Price range
        $priceWhere = "pv.is_active = 1 AND p.status = 'active'";
        $priceParams = [];
        if ($sportId) {
            $priceWhere .= " AND EXISTS (SELECT 1 FROM product_sports ps4 WHERE ps4.product_id = p.id AND ps4.sport_id = :p_sport)";
            $priceParams[':p_sport'] = $sportId;
        }
        if ($categoryId) {
            $priceWhere .= " AND p.category_id = :p_cat";
            $priceParams[':p_cat'] = $categoryId;
        }
        $priceStmt = $pdo->prepare("
            SELECT MIN(COALESCE(pv.sale_price, pv.price)) AS min_price,
                   MAX(pv.price) AS max_price
            FROM product_variants pv
            JOIN products p ON p.id = pv.product_id
            WHERE $priceWhere
        ");
        $priceStmt->execute($priceParams);
        $priceRange = $priceStmt->fetch();

        return [
            'sizes'      => $sizes,
            'brands'     => $brands,
            'price_min'  => (int) ($priceRange['min_price'] ?? 0),
            'price_max'  => (int) ($priceRange['max_price'] ?? 0),
        ];
    }
}

