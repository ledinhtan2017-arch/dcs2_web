<?php
/**
 * Cart page.
 * Variables: $items, $totals, $cartId
 */
?>
<section class="container section" style="padding-top:var(--space-xl);">
    <h1>Giỏ hàng</h1>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">🛒</div>
            <h3>Giỏ hàng trống</h3>
            <p>Hãy khám phá các sản phẩm thể thao của chúng tôi.</p>
            <a href="/" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item" id="cart-item-<?= (int)$item['id'] ?>">
                        <div class="cart-item-image">
                            <a href="/products/<?= htmlspecialchars($item['product_slug'] ?? '#') ?>">
                                <img src="<?= htmlspecialchars('/' . ltrim(($item['product_image'] ?? '') ?: 'assets/images/products/placeholder.jpg', '/'), ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($item['product_name'] ?? '') ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="cart-item-info">
                            <h3>
                                <a href="/products/<?= htmlspecialchars($item['product_slug'] ?? '#') ?>" style="color:var(--color-ink);">
                                    <?= htmlspecialchars($item['product_name'] ?? 'Sản phẩm') ?>
                                </a>
                            </h3>
                            <p style="color:var(--color-mute);font-size:14px;margin-top:4px;">
                                <?php if (!empty($item['variant_name'])): ?>
                                    <?= htmlspecialchars($item['variant_name']) ?>
                                <?php endif; ?>
                            </p>
                            <div class="product-card-price" style="margin-top:8px;">
                                <?php
                                $unitPrice = (int)($item['sale_price'] ?? 0) > 0 && (int)$item['sale_price'] < (int)$item['price']
                                    ? (int)$item['sale_price']
                                    : (int)$item['price'];
                                ?>
                                <?= number_format($unitPrice, 0, ',', '.') ?> ₫
                            </div>
                            <div class="qty-selector" style="margin-top:12px;margin-bottom:0;">
                                <button type="button" class="qty-btn" onclick="updateCartItem(<?= (int)$item['id'] ?>, <?= (int)$item['quantity'] - 1 ?>)">−</button>
                                <input type="number" class="qty-input" value="<?= (int)$item['quantity'] ?>" min="1" max="99"
                                       onchange="updateCartItem(<?= (int)$item['id'] ?>, this.value)" aria-label="Số lượng">
                                <button type="button" class="qty-btn" onclick="updateCartItem(<?= (int)$item['id'] ?>, <?= (int)$item['quantity'] + 1 ?>)">+</button>
                            </div>
                        </div>
                        <div class="cart-item-actions" style="text-align:right;">
                            <div style="font-weight:500;font-size:16px;margin-bottom:12px;">
                                <?= number_format($unitPrice * (int)$item['quantity'], 0, ',', '.') ?> ₫
                            </div>
                            <button type="button" class="btn btn-sm btn-outline" onclick="removeCartItem(<?= (int)$item['id'] ?>)">
                                Xóa
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <h2>Tóm tắt đơn hàng</h2>
                <div class="summary-row">
                    <span>Tạm tính</span>
                    <span id="subtotal"><?= number_format((int)($totals['subtotal'] ?? 0), 0, ',', '.') ?> ₫</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển</span>
                    <span id="shipping"><?= number_format((int)($totals['shipping_fee'] ?? 0), 0, ',', '.') ?> ₫</span>
                </div>
                <?php if (!empty($totals['discount']) && (int)$totals['discount'] > 0): ?>
                <div class="summary-row">
                    <span>Giảm giá</span>
                    <span style="color:var(--color-sale);">-<?= number_format((int)$totals['discount'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Tổng cộng</span>
                    <span id="grand-total"><?= number_format((int)($totals['grand_total'] ?? 0), 0, ',', '.') ?> ₫</span>
                </div>
                <a href="/checkout" class="btn btn-primary" style="width:100%;margin-top:var(--space-xl);">
                    Tiến hành thanh toán
                </a>
                <a href="/" class="btn btn-secondary" style="width:100%;margin-top:var(--space-sm);">
                    Tiếp tục mua sắm
                </a>
            </div>
        </div>
    <?php endif; ?>
</section>

<script>
function updateCartItem(itemId, qty) {
    qty = parseInt(qty, 10);
    if (qty < 1) { removeCartItem(itemId); return; }

    apiFetch('/api/cart/items/' + itemId, {
        method: 'PATCH',
        body: JSON.stringify({ quantity: qty })
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            location.reload();
        } else {
            showToast(json.message || 'Cập nhật thất bại', 'error');
        }
    })
    .catch(function() { showToast('Lỗi kết nối', 'error'); });
}

function removeCartItem(itemId) {
    apiFetch('/api/cart/items/' + itemId, { method: 'DELETE' })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            location.reload();
        } else {
            showToast(json.message || 'Xóa thất bại', 'error');
        }
    })
    .catch(function() { showToast('Lỗi kết nối', 'error'); });
}
</script>

