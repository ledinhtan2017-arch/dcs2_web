<?php
/**
 * Order history page.
 * Variables: $orders, $pagination
 */
$statusMap = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
];
?>
<section class="container section" style="padding-top:var(--space-xl);">
    <div style="max-width:900px;margin:0 auto;">
        <h1>Đơn hàng của tôi</h1>

        <div style="display:flex;gap:var(--space-lg);margin-bottom:var(--space-xl);padding-bottom:var(--space-lg);border-bottom:1px solid var(--color-hairline-soft);">
            <a href="/account" style="color:var(--color-mute);">Thông tin</a>
            <a href="/account/orders" style="font-weight:700;border-bottom:2px solid var(--color-ink);padding-bottom:4px;">Đơn hàng</a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>Chưa có đơn hàng nào</h3>
                <p>Hãy bắt đầu mua sắm ngay!</p>
                <a href="/" class="btn btn-primary">Khám phá sản phẩm</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td style="font-weight:500;"><?= htmlspecialchars($o['order_code'] ?? '') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($o['created_at'] ?? 'now')) ?></td>
                        <td style="font-weight:500;"><?= number_format((int)($o['grand_total'] ?? 0), 0, ',', '.') ?> ₫</td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($o['order_status'] ?? 'pending') ?>">
                                <?= $statusMap[$o['order_status'] ?? 'pending'] ?? $o['order_status'] ?>
                            </span>
                        </td>
                        <td><a href="/account/orders/<?= htmlspecialchars($o['order_code']) ?>" class="btn btn-sm btn-outline">Chi tiết</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (isset($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i === ($pagination['current_page'] ?? 1)): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/account/orders?page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

