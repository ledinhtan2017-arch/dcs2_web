<?php
namespace App\Controllers\Api\Admin;
use App\Core\{Response, Auth, Database, Validator};
use App\Models\Order;

class OrderApiController
{
    public function confirmPayment(string $id): void
    {
        if (!Order::confirmBankPayment((int) $id)) Response::error('Không thể xác nhận thanh toán cho đơn này.', 'INVALID_PAYMENT', 409);
        Response::success(['message' => 'Đã xác nhận thanh toán.']);
    }
    public function index(): void
    {
        $pdo = Database::getConnection();
        $request = new \App\Core\Request();
        $status = $request->get('status');
        $where = $status ? "WHERE o.order_status = " . $pdo->quote($status) : "";
        $orders = $pdo->query("
            SELECT o.*, u.name AS user_name
            FROM orders o LEFT JOIN users u ON u.id = o.user_id $where
            ORDER BY o.created_at DESC LIMIT 200
        ")->fetchAll();
        Response::success($orders);
    }

    public function show(string $id): void
    {
        $order = Order::findById((int)$id);
        if (!$order) Response::error('Đơn không tồn tại.', 'NOT_FOUND', 404);
        $full = Order::findByCode($order['order_code']);
        Response::success($full);
    }

    public function updateStatus(string $id): void
    {
        $request = new \App\Core\Request();
        $data = $request->json() ?: $request->all();
        $newStatus = $data['status'] ?? '';
        $note = $data['note'] ?? '';

        $allowed = ['confirmed','shipping','delivered','cancelled'];
        if (!in_array($newStatus, $allowed)) {
            Response::error('Trạng thái không hợp lệ.', 'VALIDATION_ERROR', 422);
        }

        $ok = Order::updateStatus((int)$id, $newStatus, Auth::id(), $note);
        if (!$ok) Response::error('Không thể chuyển trạng thái. Kiểm tra trạng thái đơn và thanh toán; đơn chuyển khoản cần được xác nhận tiền trước khi giao.', 'INVALID_TRANSITION', 409);
        Response::success(['message' => 'Cập nhật trạng thái thành công.']);
    }
}

