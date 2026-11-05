<?php
/**
 * Order detail page (customer account).
 * Variables: $order
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
        <div class="breadcrumb" style="margin-bottom:var(--space-xl);">
            <a href="/account">Tài khoản</a>
            <span class="breadcrumb-sep">›</span>
            <a href="/account/orders">Đơn hàng</a>
            <span class="breadcrumb-sep">›</span>
            <span><?= htmlspecialchars($order['order_code'] ?? '') ?></span>
        </div>

        <h1>Đơn hàng <?= htmlspecialchars($order['order_code'] ?? '') ?></h1>

        <?php if (($order['order_status'] ?? '') === 'pending'): ?>
        <form method="post" action="/account/orders/<?= rawurlencode($order['order_code']) ?>/cancel"
              onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');"
              style="margin:var(--space-lg) 0;">
            <?= $csrf_field ?>
            <button type="submit" class="btn btn-danger">Hủy đơn hàng</button>
            <span style="color:var(--color-mute);font-size:14px;margin-left:8px;">Có thể hủy khi đơn đang chờ xác nhận.</span>
        </form>
        <?php endif; ?>

        <!-- Order Info -->
        <div style="background:var(--color-soft-cloud);padding:var(--space-xl);margin-bottom:var(--space-xl);">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:var(--space-lg);">
                <div>
                    <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Trạng thái</div>
                    <span class="badge badge-<?= htmlspecialchars($order['order_status'] ?? 'pending') ?>">
                        <?= $statusMap[$order['order_status'] ?? 'pending'] ?? $order['order_status'] ?>
                    </span>
                </div>
                <div>
                    <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Ngày đặt</div>
                    <div style="font-weight:500;"><?= date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')) ?></div>
                </div>
                <div>
                    <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Người nhận</div>
                    <div style="font-weight:500;"><?= htmlspecialchars($order['customer_name'] ?? '') ?></div>
                </div>
                <div>
                    <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Điện thoại</div>
                    <div style="font-weight:500;"><?= htmlspecialchars($order['phone'] ?? '') ?></div>
                </div>
            </div>
            <?php if (!empty($order['shipping_address_json'])): ?>
                <div style="margin-top:var(--space-lg);padding-top:var(--space-lg);border-top:1px solid var(--color-hairline-soft);">
                    <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Địa chỉ giao hàng</div>
                    <?php
                    $addr = is_string($order['shipping_address_json']) ? json_decode($order['shipping_address_json'], true) : $order['shipping_address_json'];
                    $addrParts = array_filter([
                        $addr['street'] ?? '', $addr['ward'] ?? '', $addr['district'] ?? '', $addr['province'] ?? '',
                    ]);
                    ?>
                    <div><?= htmlspecialchars(implode(', ', $addrParts)) ?></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($order['note'])): ?>
                <div style="margin-top:var(--space-md);">
                    <div style="font-size:12px;color:var(--color-mute);text-transform:uppercase;margin-bottom:4px;">Ghi chú</div>
                    <div><?= htmlspecialchars($order['note']) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Order Items -->
        <h2 style="margin-bottom:var(--space-lg);">Sản phẩm</h2>
        <table class="data-table" style="margin-bottom:var(--space-xl);">
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

        <!-- Totals -->
        <div style="max-width:350px;margin-left:auto;">
            <div class="summary-row">
                <span>Tạm tính</span>
                <span><?= number_format((int)($order['subtotal'] ?? 0), 0, ',', '.') ?> ₫</span>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển</span>
                <span><?= number_format((int)($order['shipping_fee'] ?? 0), 0, ',', '.') ?> ₫</span>
            </div>
            <?php if ((int)($order['discount_total'] ?? 0) > 0): ?>
            <div class="summary-row">
                <span>Giảm giá</span>
                <span style="color:var(--color-sale);">-<?= number_format((int)$order['discount_total'], 0, ',', '.') ?> ₫</span>
            </div>
            <?php endif; ?>
            <div class="summary-row total">
                <span>Tổng cộng</span>
                <span><?= number_format((int)($order['grand_total'] ?? 0), 0, ',', '.') ?> ₫</span>
            </div>
        </div>

        <!-- Status History -->
        <?php if (!empty($order['status_history'])): ?>
        <h2 style="margin:var(--space-xl) 0 var(--space-lg);">Lịch sử trạng thái</h2>
        <div style="border-left:2px solid var(--color-hairline);padding-left:var(--space-xl);margin-bottom:var(--space-xl);">
            <?php foreach ($order['status_history'] as $h): ?>
                <div style="margin-bottom:var(--space-lg);position:relative;">
                    <div style="position:absolute;left:calc(-1 * var(--space-xl) - 5px);top:4px;width:10px;height:10px;border-radius:50%;background:var(--color-ink);"></div>
                    <div style="font-weight:500;">
                        <span class="badge badge-<?= htmlspecialchars($h['to_status'] ?? '') ?>">
                            <?= htmlspecialchars($statusMap[$h['to_status'] ?? ''] ?? ($h['to_status'] ?? '')) ?>
                        </span>
                    </div>
                    <div style="font-size:13px;color:var(--color-mute);margin-top:4px;">
                        <?= date('d/m/Y H:i', strtotime($h['created_at'] ?? 'now')) ?>
                        <?php if (!empty($h['note'])): ?> — <?= htmlspecialchars($h['note']) ?><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <a href="/account/orders" class="btn btn-outline">← Quay lại danh sách đơn hàng</a>
    </div>
</section>

