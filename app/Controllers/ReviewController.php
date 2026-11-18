<?php
namespace App\Controllers;

use App\Core\{Auth, Request, Response};
use App\Models\{Product, Review};

class ReviewController
{
    public function store(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) { http_response_code(404); return; }
        $request = new Request();
        $rating = $request->post('rating', '');
        $body = $request->post('body', '');
        $itemId = $request->post('order_item_id', '');
        if (!is_string($rating) || !preg_match('/^[1-5]$/', $rating) || !is_string($body) || mb_strlen($body) > 2000 || !is_string($itemId) || !ctype_digit($itemId) || Review::eligibleItem((int)$product['id'], Auth::id()) !== (int)$itemId) {
            Response::flash('error', 'Không thể gửi đánh giá. Bạn cần mua và nhận sản phẩm, chọn từ 1 đến 5 sao.');
        } elseif (Review::create((int)$itemId, Auth::id(), (int)$rating, trim($body))) {
            Response::flash('success', 'Cảm ơn bạn đã đánh giá sản phẩm.');
        } else {
            Response::flash('error', 'Đánh giá đã được gửi hoặc đơn hàng không còn hợp lệ.');
        }
        Response::redirect('/products/' . rawurlencode($slug) . '#reviews');
    }
}
