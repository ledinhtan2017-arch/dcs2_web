<?php
namespace App\Controllers\Api;
use App\Core\Response;
use App\Models\{Sport, Category, Product};

class ShopApiController
{
    public function sports(): void
    {
        Response::success(Sport::all());
    }

    public function categories(): void
    {
        $request = new \App\Core\Request();
        $sportId = $request->get('sport_id');
        if ($sportId) {
            Response::success(Category::getBySport((int)$sportId));
        }
        Response::success(Category::all());
    }

    public function products(): void
    {
        $request = new \App\Core\Request();
        $page = max(1, (int)$request->get('page', 1));
        $sort = $request->get('sort', 'newest');
        $perPage = min(48, max(1, (int)$request->get('per_page', 12)));

        $filters = array_filter([
            'sport'     => $request->get('sport'),
            'category'  => $request->get('category'),
            'brand'     => $request->get('brand'),
            'search'    => $request->get('search'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'size'      => $request->get('size'),
            'in_stock'  => $request->get('in_stock'),
        ]);

        $result = Product::list($filters, $page, $perPage, $sort);
        Response::success($result['products'], [
            'total' => $result['total'],
            'page'  => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
        ]);
    }

    public function product(string $id): void
    {
        if (is_numeric($id)) {
            $product = Product::findById((int)$id);
            if ($product) {
                $product['variants'] = Product::getVariants((int)$id);
                $product['images'] = Product::getImages((int)$id);
            }
        } else {
            $product = Product::findBySlug($id);
        }
        if (!$product) {
            Response::error('Sản phẩm không tồn tại.', 'NOT_FOUND', 404);
        }
        Response::success($product);
    }
}

