<?php
/**
 * CheckoutController – Checkout page and order processing.
 */

namespace App\Controllers;

use App\Core\{View, Auth, Response, Validator};
use App\Models\{Cart, Order};

class CheckoutController
{
    public function show(): void
    {
        $cart  = Cart::getOrCreate();
        $items = Cart::getItems((int) $cart['id']);

        if (empty($items)) {
            Response::flash('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm.');
            Response::redirect('/cart');
            return;
        }

        $totals = Cart::calculateTotals($items);
        $user = Auth::user();

        View::page('shop.checkout', [
            'title'  => 'Thanh toán - Sport Shop',
            'items'  => $items,
            'totals' => $totals,
            'user'   => $user,
        ]);
    }

    public function process(): void
    {
        $request = new \App\Core\Request();
        $cart  = Cart::getOrCreate();
        $items = Cart::getItems((int) $cart['id']);

        if (empty($items)) {
            Response::flash('error', 'Giỏ hàng trống.');
            Response::redirect('/cart');
            return;
        }

        $data = [
            'customer_name' => trim($request->post('customer_name', '')),
            'email'         => trim($request->post('email', '')),
            'phone'         => trim($request->post('phone', '')),
            'province'      => trim($request->post('province', '')),
            'district'      => trim($request->post('district', '')),
            'ward'          => trim($request->post('ward', '')),
            'street'        => trim($request->post('street', '')),
            'note'          => trim($request->post('note', '')),
        ];

        $validation = Validator::validate($data, [
            'customer_name' => 'required|string|min:2|max:100',
            'email'         => 'required|email',
            'phone'         => 'required|string|min:9|max:20',
            'province'      => 'required|string',
            'district'      => 'required|string',
            'ward'          => 'required|string',
            'street'        => 'required|string|min:5',
        ], [
            'customer_name' => 'Họ tên người nhận',
            'email'         => 'Email',
            'phone'         => 'Số điện thoại',
            'province'      => 'Tỉnh/Thành phố',
            'district'      => 'Quận/Huyện',
            'ward'          => 'Phường/Xã',
            'street'        => 'Địa chỉ chi tiết',
        ]);

        if (!$validation['valid']) {
            Response::flash('error', implode('<br>', $validation['errors']));
            Response::redirect('/checkout');
            return;
        }

        $paymentMethod = $request->post('payment_method', 'cod');
        if (!in_array($paymentMethod, ['cod', 'bank_transfer'], true) || ($paymentMethod === 'bank_transfer' && !\App\Models\BankTransfer::available())) {
            Response::flash('error', 'Phương thức thanh toán không khả dụng.');
            Response::redirect('/checkout');
        }

        // Generate idempotency key from session
        $idempotencyKey = $request->post('_idempotency_key', '');
        if (empty($idempotencyKey)) {
            $idempotencyKey = bin2hex(random_bytes(16));
        }

        $orderData = [
            'user_id'         => Auth::id(),
            'customer_name'   => $data['customer_name'],
            'email'           => $data['email'],
            'phone'           => $data['phone'],
            'address'         => [
                'province' => $data['province'],
                'district' => $data['district'],
                'ward'     => $data['ward'],
                'street'   => $data['street'],
            ],
            'note'            => $data['note'],
            'idempotency_key' => $idempotencyKey,
            'payment_method'  => $paymentMethod,
        ];

        $orderItems = [];
        foreach ($items as $item) {
            $orderItems[] = [
                'variant_id' => (int) $item['variant_id'],
                'quantity'   => (int) $item['quantity'],
            ];
        }

        try {
            $order = Order::create($orderData, $orderItems);
            if (!$order) {
                Response::flash('error', 'Không thể tạo đơn hàng. Một số sản phẩm có thể đã hết hàng.');
                Response::redirect('/checkout');
                return;
            }

            // Clear cart
            Cart::clear((int) $cart['id']);

            // Store order code for guest tracking
            if (!Auth::check()) {
                $_SESSION['_guest_order_code'] = $order['order_code'];
            }

            Response::redirect('/order-success/' . $order['order_code']);
        } catch (\Throwable $e) {
            error_log('[Checkout Error] ' . $e->getMessage());
            Response::flash('error', 'Đã xảy ra lỗi khi xử lý đơn hàng. Vui lòng thử lại.');
            Response::redirect('/checkout');
        }
    }

    public function cancelGuestOrder(string $code): void
    {
        // A guest may only manage the order recorded in their current session.
        if (Auth::check() || !isset($_SESSION['_guest_order_code']) || $_SESSION['_guest_order_code'] !== $code) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404']);
            return;
        }

        $order = Order::findByCode($code);
        if (!$order || $order['user_id'] !== null) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404']);
            return;
        }

        try {
            if (Order::cancelByGuest($code)) {
                Response::flash('success', 'Đơn hàng đã được hủy.');
            } else {
                Response::flash('error', 'Không thể hủy đơn. Chỉ đơn hàng đang chờ xác nhận mới có thể được hủy.');
            }
        } catch (\Throwable $e) {
            error_log('[Guest order cancellation] ' . $e->getMessage());
            Response::flash('error', 'Không thể hủy đơn hàng lúc này. Vui lòng thử lại.');
        }

        Response::redirect('/order-success/' . rawurlencode($code));
    }

    public function success(string $code): void
    {
        $order = Order::findByCode($code);
        if (!$order) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404']);
            return;
        }

        // Verify access: owner or guest who just placed it
        $canView = false;
        if (Auth::check() && (int) $order['user_id'] === Auth::id()) {
            $canView = true;
        }
        if (!Auth::check() && isset($_SESSION['_guest_order_code']) && $_SESSION['_guest_order_code'] === $code) {
            $canView = true;
        }
        if (Auth::isAdmin()) {
            $canView = true;
        }

        if (!$canView) {
            http_response_code(403);
            View::page('errors.403', ['title' => '403']);
            return;
        }

        View::page('shop.order-success', [
            'title' => 'Đặt hàng thành công - Sport Shop',
            'order' => $order,
        ]);
    }
}

