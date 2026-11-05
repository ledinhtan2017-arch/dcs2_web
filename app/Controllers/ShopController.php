<?php
/**
 * ShopController – Public shop pages (home, sport, category, product, search).
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\Request;
use App\Models\Sport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class ShopController
{
    public function home(): void
    {
        $sports   = Sport::all();
        $brands   = Brand::active();
        $featured = Product::getFeatured(8);
        $newArrivals = Product::getNewArrivals(8);

        View::page('shop.home', [
            'title'       => 'Sport Shop - Đồ thể thao chính hãng',
            'sports'      => $sports,
            'brands'      => $brands,
            'featured'    => $featured,
            'newArrivals' => $newArrivals,
        ]);
    }

    public function sport(string $slug): void
    {
        $sport = Sport::findBySlug($slug);
        if (!$sport) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404']);
            return;
        }

        $categories = Sport::getCategories($sport['id']);
        $products   = Product::getBySport($sport['id'], 12);

        View::page('shop.sport', [
            'title'      => $sport['name'] . ' - Sport Shop',
            'sport'      => $sport,
            'categories' => $categories,
            'products'   => $products,
        ]);
    }

    public function category(string $slug): void
    {
        $request = new Request();
        $category = Category::findBySlug($slug);
        if (!$category) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404']);
            return;
        }

        $page    = max(1, (int) $request->get('page', 1));
        $sort    = $request->get('sort', 'newest');
        $perPage = min(48, max(1, (int) $request->get('per_page', 12)));

        $filters = [
            'category'  => $category['id'],
            'brand'     => $request->get('brand'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'size'      => $request->get('size'),
            'in_stock'  => $request->get('in_stock'),
        ];

        $result = Product::list($filters, $page, $perPage, $sort);
        $filterOptions = Product::getFilterOptions((int) $category['sport_id'], (int) $category['id']);

        View::page('shop.category', [
            'title'         => $category['name'] . ' - Sport Shop',
            'category'      => $category,
            'products'      => $result['products'],
            'pagination'    => $result,
            'filters'       => $filters,
            'filterOptions' => $filterOptions,
            'sort'          => $sort,
            'currentPage'   => $page,
        ]);
    }

    public function product(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404']);
            return;
        }

        View::page('shop.product', [
            'title'   => $product['name'] . ' - Sport Shop',
            'product' => $product,
        ]);
    }

    public function search(): void
    {
        $request = new Request();
        $query   = trim($request->get('q', ''));
        $page    = max(1, (int) $request->get('page', 1));
        $sort    = $request->get('sort', 'newest');

        $filters = ['search' => $query];
        $result  = Product::list($filters, $page, 12, $sort);

        View::page('shop.search', [
            'title'      => ($query ? "Kết quả tìm kiếm: $query" : 'Tìm kiếm') . ' - Sport Shop',
            'query'      => $query,
            'products'   => $result['products'],
            'pagination' => $result,
            'sort'       => $sort,
        ]);
    }
}

