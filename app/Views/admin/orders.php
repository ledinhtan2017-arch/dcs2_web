<?php
/**
 * Admin Orders list.
 * Variables: $orders, $currentStatus
 */
$statusMap = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
];
$statuses = ['', 'pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
$statusLabels = ['Tất cả', 'Chờ xác nhận', 'Đã xác nhận', 'Đang giao', 'Đã giao', 'Đã hủy'];
?>
<h1>Quản lý đơn hàng</h1>

<!-- Status filter tabs -->
<div style="display:flex;gap:var(--space-sm);margin-bottom:var(--space-xl);flex-wrap:wrap;">
    <?php foreach ($statuses as $i => $s): ?>
        <a href="/admin/orders<?= $s ? '?status=' . $s : '' ?>"
           class="btn btn-sm <?= ($currentStatus ?? '') === $s ? 'btn-primary' : 'btn-outline' ?>">
            <?= $statusLabels[$i] ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state"><p>Không có đơn hàng nào.</p></div>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td style="font-weight:500;"><?= htmlspecialchars($o['order_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($o['customer_name'] ?? $o['user_name'] ?? '') ?></td>
                <td><?= number_format((int)($o['grand_total'] ?? 0), 0, ',', '.') ?> ₫</td>
                <td>
                    <span class="badge badge-<?= htmlspecialchars($o['order_status'] ?? 'pending') ?>">
                        <?= $statusMap[$o['order_status'] ?? 'pending'] ?? $o['order_status'] ?>
                    </span>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($o['created_at'] ?? 'now')) ?></td>
                <td><a href="/admin/orders/<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline">Chi tiết</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

