<?php
namespace App\Controllers\Api;
use App\Core\{Response, Auth, Validator};
use App\Models\{Cart, Order};

class CheckoutApiController
{
    public function process(): void
    {
        $request = new \App\Core\Request();
        $data = $request->json() ?: $request->all();

        $v = Validator::validate($data, [
            'customer_name' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|min:9|max:20',
            'province' => 'required|string',
            'district' => 'required|string',
            'ward' => 'required|string',
            'street' => 'required|string|min:5',
        ], [
            'customer_name' => 'Họ tên', 'email' => 'Email', 'phone' => 'SĐT',
            'province' => 'Tỉnh/TP', 'district' => 'Quận/Huyện',
            'ward' => 'Phường/Xã', 'street' => 'Địa chỉ'
        ]);
        if (!$v['valid']) Response::error(implode(' ', $v['errors']), 'VALIDATION_ERROR', 422);

        $cart = Cart::getOrCreate();
        $items = Cart::getItems((int)$cart['id']);
        if (empty($items)) Response::error('Giỏ hàng trống.', 'EMPTY_CART', 400);

        $orderItems = [];
        foreach ($items as $item) {
            $orderItems[] = ['variant_id' => (int)$item['variant_id'], 'quantity' => (int)$item['quantity']];
        }

        $ikey = $data['idempotency_key'] ?? bin2hex(random_bytes(16));
        $orderData = [
            'user_id' => Auth::id(),
            'customer_name' => $data['customer_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => [
                'province' => $data['province'], 'district' => $data['district'],
                'ward' => $data['ward'], 'street' => $data['street'],
            ],
            'note' => $data['note'] ?? '',
            'idempotency_key' => $ikey,
        ];

        try {
            $order = Order::create($orderData, $orderItems);
            if (!$order) Response::error('Không thể tạo đơn. Sản phẩm có thể đã hết hàng.', 'ORDER_FAILED', 409);
            Cart::clear((int)$cart['id']);
            Response::success($order, null, 201);
        } catch (\Throwable $e) {
            error_log('[API Checkout] ' . $e->getMessage());
            Response::error('Lỗi hệ thống khi tạo đơn hàng.', 'SERVER_ERROR', 500);
        }
    }
}

