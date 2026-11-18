<?php
/**
 * Order success page.
 * Variables: $order
 */
?>
<section class="container section" style="padding-top:var(--space-xl);max-width:700px;margin:0 auto;text-align:center;">
    <?php if (($order['order_status'] ?? '') === 'cancelled'): ?>
        <h1 style="margin-bottom:var(--space-md);">Đơn hàng đã hủy</h1>
        <p style="color:var(--color-mute);margin-bottom:var(--space-xl);font-size:18px;">Đơn hàng này đã được hủy và sẽ không được giao.</p>
    <?php else: ?>
        <div style="font-size:64px;margin-bottom:var(--space-lg);">✅</div>
        <h1 style="margin-bottom:var(--space-md);">Đặt hàng thành công!</h1>
        <p style="color:var(--color-mute);margin-bottom:var(--space-xl);font-size:18px;">Cảm ơn bạn đã đặt hàng tại Sport Shop.</p>
    <?php endif; ?>

    <div style="background:var(--color-soft-cloud);padding:var(--space-xl);text-align:left;margin-bottom:var(--space-xl);">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg);">
            <div>
                <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Mã đơn hàng</div>
                <div style="font-weight:700;font-size:18px;"><?= htmlspecialchars($order['order_code'] ?? '') ?></div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Trạng thái</div>
                <span class="badge badge-<?= htmlspecialchars($order['order_status'] ?? 'pending') ?>">
                    <?php
                    $statusMap = [
                        'pending'   => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'shipping'  => 'Đang giao',
                        'delivered' => 'Đã giao',
                        'cancelled' => 'Đã hủy',
                    ];
                    echo $statusMap[$order['order_status'] ?? 'pending'] ?? 'Chờ xác nhận';
                    ?>
                </span>
            </div>
            <div>
                <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Người nhận</div>
                <div style="font-weight:500;"><?= htmlspecialchars($order['customer_name'] ?? '') ?></div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Tổng tiền</div>
                <div style="font-weight:700;font-size:18px;"><?= number_format((int)($order['grand_total'] ?? 0), 0, ',', '.') ?> ₫</div>
            </div>
        </div>

        <?php if (!empty($order['shipping_address_json'])): ?>
        <div style="margin-top:var(--space-lg);padding-top:var(--space-lg);border-top:1px solid var(--color-hairline-soft);">
            <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Địa chỉ giao hàng</div>
            <?php
            $addr = is_string($order['shipping_address_json']) ? json_decode($order['shipping_address_json'], true) : $order['shipping_address_json'];
            $addrParts = array_filter([
                $addr['street'] ?? '',
                $addr['ward'] ?? '',
                $addr['district'] ?? '',
                $addr['province'] ?? '',
            ]);
            ?>
            <div><?= htmlspecialchars(implode(', ', $addrParts)) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <?php require VIEW_PATH . '/shop/bank-transfer.php'; ?>

    <!-- Order Items -->
    <?php if (!empty($order['items'])): ?>
    <div style="text-align:left;margin-bottom:var(--space-xl);">
        <h2 style="margin-bottom:var(--space-lg);">Chi tiết sản phẩm</h2>
        <?php foreach ($order['items'] as $item): ?>
            <div style="display:flex;gap:var(--space-md);padding:var(--space-md) 0;border-bottom:1px solid var(--color-hairline-soft);align-items:center;">
                <div style="width:60px;height:60px;background:var(--color-soft-cloud);flex-shrink:0;overflow:hidden;">
                    <img src="<?= htmlspecialchars('/' . ltrim(($item['product_image'] ?? '') ?: 'assets/images/products/placeholder.jpg', '/'), ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($item['name_snapshot'] ?? '') ?>"
                         style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div style="flex:1;">
                    <div style="font-weight:500;"><?= htmlspecialchars($item['name_snapshot'] ?? '') ?></div>
                    <?php if (!empty($item['options_snapshot'])): ?>
                        <div style="font-size:13px;color:var(--color-mute);"><?= htmlspecialchars($item['options_snapshot']) ?></div>
                    <?php endif; ?>
                    <div style="font-size:13px;color:var(--color-mute);">x<?= (int)$item['quantity'] ?></div>
                </div>
                <div style="font-weight:500;"><?= number_format((int)($item['line_total'] ?? $item['unit_price'] * $item['quantity']), 0, ',', '.') ?> ₫</div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:var(--space-md);justify-content:center;flex-wrap:wrap;">
        <?php if ($auth_check): ?>
            <a href="/account/orders" class="btn btn-primary">Xem đơn hàng của tôi</a>
        <?php endif; ?>
        <?php if (!$auth_check && ($order['user_id'] ?? null) === null
            && ($order['order_status'] ?? '') === 'pending'
            && !(($order['payment_method'] ?? '') === 'bank_transfer' && ($order['payment_status'] ?? '') === 'paid')
            && ($_SESSION['_guest_order_code'] ?? null) === ($order['order_code'] ?? '')): ?>
            <form action="/order-success/<?= rawurlencode($order['order_code']) ?>/cancel" method="post" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                <?= $csrf_field ?>
                <button type="submit" class="btn btn-outline">Hủy đơn hàng</button>
            </form>
        <?php endif; ?>
        <a href="/" class="btn btn-secondary">Tiếp tục mua sắm</a>
    </div>
</section>

