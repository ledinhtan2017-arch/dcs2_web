<?php
/**
 * Admin Order Detail.
 * Variables: $order
 */
$statusMap = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
];
$transitions = [
    'pending'   => ['confirmed', 'cancelled'],
    'confirmed' => ['shipping', 'cancelled'],
    'shipping'  => ['delivered'],
    'delivered' => [],
    'cancelled' => [],
];
$curStatus = $order['order_status'] ?? 'pending';
$nextStatuses = $transitions[$curStatus] ?? [];
?>
<div class="breadcrumb" style="margin-bottom:var(--space-xl);">
    <a href="/admin/orders">Đơn hàng</a>
    <span class="breadcrumb-sep">›</span>
    <span><?= htmlspecialchars($order['order_code'] ?? '') ?></span>
</div>

<h1>Đơn hàng <?= htmlspecialchars($order['order_code'] ?? '') ?></h1>

<!-- Status update -->
<?php if (!empty($nextStatuses)): ?>
<div style="display:flex;gap:var(--space-sm);margin-bottom:var(--space-xl);align-items:center;">
    <span style="font-weight:500;margin-right:var(--space-sm);">Cập nhật trạng thái:</span>
    <?php foreach ($nextStatuses as $ns): ?>
        <button type="button" class="btn btn-sm <?= $ns === 'cancelled' ? 'btn-danger' : 'btn-primary' ?>"
                onclick="updateOrderStatus(<?= (int)$order['id'] ?>, '<?= $ns ?>')">
            <?= $statusMap[$ns] ?? $ns ?>
        </button>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Order Info -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-xl);margin-bottom:var(--space-xl);">
    <div style="background:var(--color-canvas);padding:var(--space-xl);border-radius:var(--space-sm);">
        <h3 style="margin-bottom:var(--space-lg);">Thông tin đơn hàng</h3>
        <div style="display:grid;gap:var(--space-md);">
            <div><span style="color:var(--color-mute);font-size:14px;">Trạng thái:</span>
                <span class="badge badge-<?= htmlspecialchars($curStatus) ?>"><?= $statusMap[$curStatus] ?? $curStatus ?></span></div>
            <div><span style="color:var(--color-mute);font-size:14px;">Ngày đặt:</span>
                <?= date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')) ?></div>
            <div><span style="color:var(--color-mute);font-size:14px;">Thanh toán:</span> COD</div>
        </div>
    </div>
    <div style="background:var(--color-canvas);padding:var(--space-xl);border-radius:var(--space-sm);">
        <h3 style="margin-bottom:var(--space-lg);">Thông tin giao hàng</h3>
        <div style="display:grid;gap:var(--space-md);">
            <div><span style="color:var(--color-mute);font-size:14px;">Người nhận:</span>
                <?= htmlspecialchars($order['customer_name'] ?? '') ?></div>
            <div><span style="color:var(--color-mute);font-size:14px;">Email:</span>
                <?= htmlspecialchars($order['email'] ?? '') ?></div>
            <div><span style="color:var(--color-mute);font-size:14px;">Điện thoại:</span>
                <?= htmlspecialchars($order['phone'] ?? '') ?></div>
            <?php if (!empty($order['shipping_address_json'])): ?>
                <?php
                $addr = is_string($order['shipping_address_json']) ? json_decode($order['shipping_address_json'], true) : $order['shipping_address_json'];
                $addrParts = array_filter([$addr['street'] ?? '', $addr['ward'] ?? '', $addr['district'] ?? '', $addr['province'] ?? '']);
                ?>
                <div><span style="color:var(--color-mute);font-size:14px;">Địa chỉ:</span>
                    <?= htmlspecialchars(implode(', ', $addrParts)) ?></div>
            <?php endif; ?>
            <?php if (!empty($order['note'])): ?>
                <div><span style="color:var(--color-mute);font-size:14px;">Ghi chú:</span>
                    <?= htmlspecialchars($order['note']) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Items -->
<div style="background:var(--color-canvas);padding:var(--space-xl);border-radius:var(--space-sm);margin-bottom:var(--space-xl);">
    <h3 style="margin-bottom:var(--space-lg);">Sản phẩm</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th style="text-align:right;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($order['items'] ?? []) as $item): ?>
            <tr>
                <td>
                    <div style="font-weight:500;"><?= htmlspecialchars($item['name_snapshot'] ?? '') ?></div>
                    <?php if (!empty($item['options_snapshot'])): ?>
                        <div style="font-size:13px;color:var(--color-mute);"><?= htmlspecialchars($item['options_snapshot']) ?></div>
                    <?php endif; ?>
                </td>
                <td><?= number_format((int)($item['unit_price'] ?? 0), 0, ',', '.') ?> ₫</td>
                <td><?= (int)$item['quantity'] ?></td>
                <td style="text-align:right;font-weight:500;"><?= number_format((int)($item['line_total'] ?? 0), 0, ',', '.') ?> ₫</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="max-width:300px;margin-left:auto;margin-top:var(--space-lg);">
        <div class="summary-row"><span>Tạm tính</span><span><?= number_format((int)($order['subtotal'] ?? 0), 0, ',', '.') ?> ₫</span></div>
        <div class="summary-row"><span>Phí vận chuyển</span><span><?= number_format((int)($order['shipping_fee'] ?? 0), 0, ',', '.') ?> ₫</span></div>
        <?php if ((int)($order['discount_total'] ?? 0) > 0): ?>
        <div class="summary-row"><span>Giảm giá</span><span style="color:var(--color-sale);">-<?= number_format((int)$order['discount_total'], 0, ',', '.') ?> ₫</span></div>
        <?php endif; ?>
        <div class="summary-row total"><span>Tổng cộng</span><span><?= number_format((int)($order['grand_total'] ?? 0), 0, ',', '.') ?> ₫</span></div>
    </div>
</div>

<!-- Status History -->
<?php if (!empty($order['status_history'])): ?>
<div style="background:var(--color-canvas);padding:var(--space-xl);border-radius:var(--space-sm);">
    <h3 style="margin-bottom:var(--space-lg);">Lịch sử trạng thái</h3>
    <?php foreach ($order['status_history'] as $h): ?>
        <div style="display:flex;gap:var(--space-md);align-items:center;padding:var(--space-sm) 0;border-bottom:1px solid var(--color-hairline-soft);">
            <span class="badge badge-<?= htmlspecialchars($h['to_status'] ?? '') ?>"><?= htmlspecialchars($statusMap[$h['to_status'] ?? ''] ?? ($h['to_status'] ?? '')) ?></span>
            <span style="font-size:13px;color:var(--color-mute);"><?= date('d/m/Y H:i', strtotime($h['created_at'] ?? 'now')) ?></span>
            <?php if (!empty($h['note'])): ?>
                <span style="font-size:13px;">— <?= htmlspecialchars($h['note']) ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function updateOrderStatus(orderId, newStatus) {
    var label = {pending:'Chờ xác nhận',confirmed:'Đã xác nhận',shipping:'Đang giao',delivered:'Đã giao',cancelled:'Đã hủy'};
    if (!confirm('Chuyển sang trạng thái: ' + (label[newStatus] || newStatus) + '?')) return;

    apiFetch('/api/admin/orders/' + orderId + '/status', {
        method: 'PATCH',
        body: JSON.stringify({ status: newStatus })
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            showToast('Cập nhật thành công!');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            showToast((json.error && json.error.message) || 'Cập nhật thất bại', 'error');
        }
    })
    .catch(function() { showToast('Lỗi kết nối', 'error'); });
}
</script>

