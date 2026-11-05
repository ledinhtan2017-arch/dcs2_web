<?php
/**
 * AccountController – Customer account pages.
 */

namespace App\Controllers;

use App\Core\{View, Auth, Response, Validator};
use App\Models\{User, Order};

class AccountController
{
    public function profile(): void
    {
        $user = User::findById(Auth::id());
        View::page('account.profile', [
            'title' => 'Tài khoản - Sport Shop',
            'user'  => $user,
        ]);
    }

    public function update(): void
    {
        $request = new \App\Core\Request();
        $data = [
            'name'  => trim($request->post('name', '')),
            'phone' => trim($request->post('phone', '')),
        ];

        $validation = Validator::validate($data, [
            'name'  => 'required|string|min:2|max:100',
            'phone' => 'string|max:20',
        ], ['name' => 'Họ tên', 'phone' => 'Số điện thoại']);

        if (!$validation['valid']) {
            Response::flash('error', implode(' ', $validation['errors']));
            Response::redirect('/account');
            return;
        }

        // Password change (optional)
        $password = $request->post('password', '');
        if ($password !== '') {
            if (strlen($password) < 6) {
                Response::flash('error', 'Mật khẩu mới phải có ít nhất 6 ký tự.');
                Response::redirect('/account');
                return;
            }
            $data['password'] = $password;
        }

        User::update(Auth::id(), $data);
        Auth::set('name', $data['name']);
        Auth::set('phone', $data['phone']);

        Response::flash('success', 'Cập nhật thông tin thành công.');
        Response::redirect('/account');
    }

    public function orders(): void
    {
        $request = new \App\Core\Request();
        $page = max(1, (int) $request->get('page', 1));
        $result = Order::getUserOrders(Auth::id(), $page);

        View::page('account.orders', [
            'title'      => 'Đơn hàng của tôi - Sport Shop',
            'orders'     => $result['orders'],
            'pagination' => $result,
        ]);
    }

    public function orderDetail(string $code): void
    {
        $order = Order::findByCode($code);
        if (!$order || (int) $order['user_id'] !== Auth::id()) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404']);
            return;
        }

        View::page('account.order-detail', [
            'title' => 'Đơn hàng ' . $order['order_code'] . ' - Sport Shop',
            'order' => $order,
        ]);
    }

    public function cancelOrder(string $code): void
    {
        $order = Order::findByCode($code);
        if (!$order || $order['user_id'] === null || (int) $order['user_id'] !== Auth::id()) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404']);
            return;
        }

        try {
            if (Order::cancelByCustomer($code, Auth::id())) {
                Response::flash('success', 'Đơn hàng đã được hủy.');
            } else {
                Response::flash('error', 'Không thể hủy đơn. Chỉ đơn chờ xác nhận mới có thể hủy.');
            }
        } catch (\Throwable $e) {
            error_log('[Customer order cancellation] ' . $e->getMessage());
            Response::flash('error', 'Không thể hủy đơn hàng lúc này. Vui lòng thử lại.');
        }
        Response::redirect('/account/orders/' . rawurlencode($code));
    }
}

