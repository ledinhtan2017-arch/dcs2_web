<?php
namespace App\Controllers\Api;
use App\Core\{Response, Auth};
use App\Models\Order;

class OrderApiController
{
    public function show(string $code): void
    {
        $order = Order::findByCode($code);
        if (!$order) Response::error('Đơn hàng không tồn tại.', 'NOT_FOUND', 404);

        $canView = false;
        if (Auth::check() && (int)$order['user_id'] === Auth::id()) $canView = true;
        if (Auth::isAdmin()) $canView = true;
        if (!Auth::check() && isset($_SESSION['_guest_order_code']) && $_SESSION['_guest_order_code'] === $code) $canView = true;

        if (!$canView) Response::error('Bạn không có quyền xem đơn hàng này.', 'FORBIDDEN', 403);

        // Remove sensitive fields
        unset($order['idempotency_key']);
        Response::success($order);
    }
}

