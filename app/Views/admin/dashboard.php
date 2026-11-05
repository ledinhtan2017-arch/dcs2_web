<?php
/**
 * Admin Dashboard.
 * Variables: $stats, $recentOrders
 */
$statusMap = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
];
?>
<h1>Dashboard</h1>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value"><?= number_format((int)($stats['total_orders'] ?? 0)) ?></div>
        <div class="stat-label">Tổng đơn hàng</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--color-sale);"><?= (int)($stats['pending_orders'] ?? 0) ?></div>
        <div class="stat-label">Chờ xác nhận</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format((int)($stats['total_revenue'] ?? 0), 0, ',', '.') ?> ₫</div>
        <div class="stat-label">Doanh thu</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)($stats['total_products'] ?? 0) ?></div>
        <div class="stat-label">Sản phẩm</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)($stats['total_users'] ?? 0) ?></div>
        <div class="stat-label">Khách hàng</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:<?= ((int)($stats['low_stock'] ?? 0) > 0) ? 'var(--color-sale)' : 'var(--color-success)' ?>;">
            <?= (int)($stats['low_stock'] ?? 0) ?>
        </div>
        <div class="stat-label">Sắp hết hàng</div>
    </div>
</div>

<h2 style="margin-bottom:var(--space-lg);">Đơn hàng gần đây</h2>

<?php if (empty($recentOrders)): ?>
    <div class="empty-state"><p>Chưa có đơn hàng nào.</p></div>
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
            <?php foreach ($recentOrders as $o): ?>
            <tr>
                <td style="font-weight:500;"><?= htmlspecialchars($o['order_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($o['customer_name'] ?? '') ?></td>
                <td><?= number_format((int)($o['grand_total'] ?? 0), 0, ',', '.') ?> ₫</td>
                <td>
                    <span class="badge badge-<?= htmlspecialchars($o['order_status'] ?? 'pending') ?>">
                        <?= $statusMap[$o['order_status'] ?? 'pending'] ?? $o['order_status'] ?>
                    </span>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($o['created_at'] ?? 'now')) ?></td>
                <td><a href="/admin/orders/<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline">Xem</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

