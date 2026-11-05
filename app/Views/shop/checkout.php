<?php
/**
 * Checkout page.
 * Variables: $items, $totals, $user
 */
$idempotencyKey = bin2hex(random_bytes(16));
?>
<section class="container section" style="padding-top:var(--space-xl);">
    <h1>Thanh toán</h1>

    <div class="cart-layout">
        <!-- Checkout Form -->
        <div>
            <form action="/checkout" method="POST" id="checkout-form">
                <?= $csrf_field ?>
                <input type="hidden" name="_idempotency_key" value="<?= htmlspecialchars($idempotencyKey) ?>">

                <h2 style="margin-bottom:var(--space-xl);">Thông tin giao hàng</h2>

                <div class="form-group">
                    <label class="form-label" for="customer_name">Họ tên người nhận *</label>
                    <input type="text" name="customer_name" id="customer_name" class="form-input"
                           value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="email">Email *</label>
                        <input type="email" name="email" id="email" class="form-input"
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Số điện thoại *</label>
                        <input type="tel" name="phone" id="phone" class="form-input"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="province">Tỉnh/Thành phố *</label>
                        <input type="text" name="province" id="province" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="district">Quận/Huyện *</label>
                        <input type="text" name="district" id="district" class="form-input" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="ward">Phường/Xã *</label>
                        <input type="text" name="ward" id="ward" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="street">Địa chỉ chi tiết *</label>
                        <input type="text" name="street" id="street" class="form-input"
                               placeholder="Số nhà, tên đường" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="note">Ghi chú đơn hàng</label>
                    <textarea name="note" id="note" class="form-input" rows="3"
                              placeholder="Ghi chú thêm cho đơn hàng (tùy chọn)"></textarea>
                </div>

                <h2 style="margin:var(--space-xl) 0;">Phương thức thanh toán</h2>
                <div style="padding:var(--space-lg);border:1.5px solid var(--color-ink);border-radius:var(--rounded-md);margin-bottom:var(--space-xl);display:flex;align-items:center;gap:var(--space-md);">
                    <input type="radio" name="payment_method" value="cod" checked id="pm-cod">
                    <label for="pm-cod" style="font-weight:500;">💵 Thanh toán khi nhận hàng (COD)</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;font-size:18px;height:56px;" id="btn-submit">
                    Đặt hàng
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="cart-summary">
            <h2>Đơn hàng của bạn</h2>
            <?php foreach ($items as $item): ?>
                <div style="display:flex;gap:var(--space-md);padding:var(--space-md) 0;border-bottom:1px solid var(--color-hairline-soft);align-items:center;">
                    <div style="width:60px;height:60px;background:var(--color-soft-cloud);flex-shrink:0;overflow:hidden;">
                        <img src="<?= htmlspecialchars('/' . ltrim(($item['product_image'] ?? '') ?: 'assets/images/products/placeholder.jpg', '/'), ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($item['product_name'] ?? '') ?>"
                             style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:500;font-size:14px;"><?= htmlspecialchars($item['product_name'] ?? '') ?></div>
                        <?php if (!empty($item['variant_name'])): ?>
                            <div style="font-size:12px;color:var(--color-mute);"><?= htmlspecialchars($item['variant_name']) ?></div>
                        <?php endif; ?>
                        <div style="font-size:12px;color:var(--color-mute);">x<?= (int)$item['quantity'] ?></div>
                    </div>
                    <div style="font-weight:500;font-size:14px;white-space:nowrap;">
                        <?php
                        $up = (int)($item['sale_price'] ?? 0) > 0 && (int)$item['sale_price'] < (int)$item['price']
                            ? (int)$item['sale_price'] : (int)$item['price'];
                        ?>
                        <?= number_format($up * (int)$item['quantity'], 0, ',', '.') ?> ₫
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="summary-row" style="margin-top:var(--space-lg);">
                <span>Tạm tính</span>
                <span><?= number_format((int)($totals['subtotal'] ?? 0), 0, ',', '.') ?> ₫</span>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển</span>
                <span><?= number_format((int)($totals['shipping_fee'] ?? 0), 0, ',', '.') ?> ₫</span>
            </div>
            <div class="summary-row total">
                <span>Tổng cộng</span>
                <span><?= number_format((int)($totals['grand_total'] ?? 0), 0, ',', '.') ?> ₫</span>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    var btn = document.getElementById('btn-submit');
    if (btn.disabled) { e.preventDefault(); return; }
    btn.disabled = true;
    btn.textContent = 'Đang xử lý...';
});
</script>

