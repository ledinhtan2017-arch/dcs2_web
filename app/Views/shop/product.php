<?php
/** @var array $product */
$formatPrice = function($p) { return number_format((int)$p, 0, ',', '.') . ' ₫'; };
$variants = $product['variants'] ?? [];
$images = $product['images'] ?? [];
$mainImage = $images[0] ?? null;
?>
<div class="container" style="padding-top:var(--space-xl);">
    <div class="breadcrumb" style="margin-bottom:var(--space-xl);">
        <a href="/">Trang chủ</a><span class="breadcrumb-sep">/</span>
        <?php if (!empty($product['sport_slug'])): ?>
            <a href="/sports/<?= htmlspecialchars($product['sport_slug']) ?>"><?= htmlspecialchars($product['sport_name'] ?? '') ?></a><span class="breadcrumb-sep">/</span>
        <?php endif; ?>
        <?php if (!empty($product['category_slug'])): ?>
            <a href="/categories/<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name'] ?? '') ?></a><span class="breadcrumb-sep">/</span>
        <?php endif; ?>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <div class="pdp-layout">
        <!-- Gallery -->
        <div class="pdp-gallery">
            <div class="pdp-main-image" id="main-image">
                <?php if ($mainImage): ?>
                    <img src="/<?= htmlspecialchars($mainImage['image_path']) ?>" alt="<?= htmlspecialchars($mainImage['alt_text'] ?? $product['name']) ?>" id="main-img">
                <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:120px;color:var(--color-hairline);">📦</div>
                <?php endif; ?>
            </div>
            <?php if (count($images) > 1): ?>
            <div class="pdp-thumbnails">
                <?php foreach ($images as $i => $img): ?>
                <div class="pdp-thumbnail <?= $i===0?'active':'' ?>" onclick="switchImage(this, '<?= htmlspecialchars($img['image_path']) ?>')">
                    <img src="/<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($img['alt_text'] ?? '') ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="pdp-info">
            <?php if (!empty($product['brand_name'])): ?>
                <div class="pdp-brand"><?= htmlspecialchars($product['brand_name']) ?></div>
            <?php endif; ?>
            <h1 class="pdp-title"><?= htmlspecialchars($product['name']) ?></h1>

            <div class="pdp-price" id="pdp-price">
                <?php
                $minPrice = $product['min_price'] ?? 0;
                $maxPrice = $product['max_price'] ?? 0;
                if ($minPrice < $maxPrice): ?>
                    <span class="price-sale"><?= $formatPrice($minPrice) ?></span>
                    <span class="price-original"><?= $formatPrice($maxPrice) ?></span>
                <?php else: ?>
                    <?= $formatPrice($minPrice) ?>
                <?php endif; ?>
            </div>

            <!-- Variant Selection -->
            <?php
            $sizes = array_unique(array_filter(array_column($variants, 'size_label')));
            $colors = array_unique(array_filter(array_column($variants, 'color_name')));
            ?>

            <form id="add-to-cart-form">
                <?php if (!empty($sizes)): ?>
                <div class="variant-group">
                    <div class="variant-label">Size</div>
                    <div class="variant-options" id="size-options">
                        <?php foreach ($sizes as $size): ?>
                            <button type="button" class="variant-option" data-size="<?= htmlspecialchars($size) ?>" onclick="selectVariant('size', this)"><?= htmlspecialchars($size) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($colors)): ?>
                <div class="variant-group">
                    <div class="variant-label">Màu sắc</div>
                    <div class="variant-options" id="color-options">
                        <?php foreach ($colors as $color): ?>
                            <button type="button" class="variant-option" data-color="<?= htmlspecialchars($color) ?>" onclick="selectVariant('color', this)"><?= htmlspecialchars($color) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div id="stock-info" class="pdp-stock" style="display:none;"></div>

                <div class="qty-selector">
                    <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                    <input type="number" class="qty-input" id="qty-input" value="1" min="1" max="99" aria-label="Số lượng">
                    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                </div>

                <input type="hidden" id="selected-variant-id" name="variant_id" value="">
                <button type="button" class="btn btn-primary" id="add-to-cart-btn" onclick="addToCart()" style="width:100%;" disabled>
                    <?= empty($sizes) && empty($colors) ? 'Thêm vào giỏ hàng' : 'Vui lòng chọn biến thể' ?>
                </button>
            </form>

            <!-- Description -->
            <?php if (!empty($product['short_description'])): ?>
                <p style="margin-top:var(--space-xl);color:var(--color-charcoal);"><?= htmlspecialchars($product['short_description']) ?></p>
            <?php endif; ?>

            <div style="margin-top:var(--space-xl);">
                <div class="disclosure-row" onclick="toggleDisclosure(this)">
                    <span style="font-weight:500;">Mô tả sản phẩm</span>
                    <span>▾</span>
                </div>
                <div class="disclosure-content">
                    <?= nl2br(htmlspecialchars($product['description'] ?? '')) ?>
                </div>

                <div class="disclosure-row" onclick="toggleDisclosure(this)">
                    <span style="font-weight:500;">Vận chuyển & Đổi trả</span>
                    <span>▾</span>
                </div>
                <div class="disclosure-content">
                    <p>• Giao hàng toàn quốc qua các đơn vị vận chuyển uy tín.</p>
                    <p>• Phí vận chuyển: <?= $formatPrice(\App\Config\env('SHIPPING_FEE', 30000)) ?></p>
                    <p>• Đổi trả trong vòng 7 ngày nếu sản phẩm lỗi từ nhà sản xuất.</p>
                    <p>• Thanh toán: COD<?php if (\App\Models\BankTransfer::available()): ?> hoặc chuyển khoản ngân hàng<?php endif; ?>.</p>
                </div>
            </div>
        </div>
    </div>
    <section id="reviews" class="section" style="margin-top:var(--space-xl);">
        <h2 class="section-title">Đánh giá của khách hàng</h2>
        <?php if (!empty($reviews)): ?>
            <?php $average = array_sum(array_column($reviews, 'rating')) / count($reviews); ?>
            <p style="margin-bottom:var(--space-lg);"><strong><?= number_format($average, 1) ?>/5 ★</strong> · <?= count($reviews) ?> đánh giá</p>
            <?php foreach ($reviews as $review): ?>
                <article style="padding:var(--space-md) 0;border-bottom:1px solid var(--color-hairline-soft);">
                    <strong><?= htmlspecialchars($review['name']) ?></strong>
                    <span aria-label="<?= (int)$review['rating'] ?> trên 5 sao" style="color:#a76700;margin-left:8px;"><?= str_repeat('★', (int)$review['rating']) . str_repeat('☆', 5-(int)$review['rating']) ?></span>
                    <p><?= nl2br(htmlspecialchars($review['body'] ?? '')) ?></p>
                </article>
            <?php endforeach; ?>
        <?php else: ?><p>Chưa có đánh giá nào.</p><?php endif; ?>

        <?php if (!empty($reviewItem)): ?>
            <form method="post" action="/products/<?= rawurlencode($product['slug']) ?>/reviews" style="margin-top:var(--space-xl);max-width:550px;">
                <?= $csrf_field ?>
                <input type="hidden" name="order_item_id" value="<?= (int)$reviewItem ?>">
                <label class="form-label" for="review-rating">Đánh giá của bạn</label>
                <select class="form-input" id="review-rating" name="rating" required><option value="">Chọn số sao</option><?php for ($i=5; $i>=1; $i--): ?><option value="<?= $i ?>"><?= $i ?> sao</option><?php endfor; ?></select>
                <label class="form-label" for="review-body" style="margin-top:12px;">Nhận xét (không bắt buộc)</label>
                <textarea class="form-input" id="review-body" name="body" rows="3" maxlength="2000"></textarea>
                <button class="btn btn-primary" type="submit" style="margin-top:12px;">Gửi đánh giá</button>
            </form>
        <?php elseif (!$auth_check): ?><p style="margin-top:var(--space-lg);"><a href="/login">Đăng nhập</a> để đánh giá sản phẩm đã mua.</p>
        <?php else: ?><p style="margin-top:var(--space-lg);">Bạn có thể đánh giá sau khi nhận sản phẩm.</p><?php endif; ?>
    </section>
</div>

<script>
const variants = <?= json_encode($variants, JSON_UNESCAPED_UNICODE) ?>;
let selectedSize = null, selectedColor = null;
const hasSizes = <?= !empty($sizes) ? 'true' : 'false' ?>;
const hasColors = <?= !empty($colors) ? 'true' : 'false' ?>;

// Auto-select if only one variant
if (variants.length === 1) {
    document.getElementById('selected-variant-id').value = variants[0].id;
    updateUI(variants[0]);
}

function selectVariant(type, btn) {
    btn.parentElement.querySelectorAll('.variant-option').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    if (type === 'size') selectedSize = btn.dataset.size;
    if (type === 'color') selectedColor = btn.dataset.color;
    findMatchingVariant();
}

function findMatchingVariant() {
    let match = variants.find(v => {
        let sizeMatch = !hasSizes || v.size_label === selectedSize;
        let colorMatch = !hasColors || v.color_name === selectedColor;
        return sizeMatch && colorMatch;
    });
    if (match) {
        document.getElementById('selected-variant-id').value = match.id;
        updateUI(match);
    } else {
        document.getElementById('selected-variant-id').value = '';
        document.getElementById('add-to-cart-btn').disabled = true;
        document.getElementById('add-to-cart-btn').textContent = 'Không có biến thể phù hợp';
    }
}

function updateUI(v) {
    const price = document.getElementById('pdp-price');
    const ep = parseInt(v.effective_price || v.sale_price || v.price);
    const op = parseInt(v.price);
    if (v.sale_price && parseInt(v.sale_price) < op) {
        price.innerHTML = `<span class="price-sale">${formatVND(ep)}</span> <span class="price-original">${formatVND(op)}</span>`;
    } else {
        price.textContent = formatVND(op);
    }

    const stock = document.getElementById('stock-info');
    stock.style.display = 'block';
    const sq = parseInt(v.stock_quantity);
    if (sq > 0) {
        stock.className = 'pdp-stock';
        stock.textContent = `Còn ${sq} sản phẩm`;
        document.getElementById('qty-input').max = sq;
        document.getElementById('add-to-cart-btn').disabled = false;
        document.getElementById('add-to-cart-btn').textContent = 'Thêm vào giỏ hàng';
    } else {
        stock.className = 'pdp-stock out';
        stock.textContent = 'Hết hàng';
        document.getElementById('add-to-cart-btn').disabled = true;
        document.getElementById('add-to-cart-btn').textContent = 'Hết hàng';
    }
}

function formatVND(n) { return new Intl.NumberFormat('vi-VN').format(n) + ' ₫'; }

function changeQty(d) {
    const inp = document.getElementById('qty-input');
    let val = parseInt(inp.value) + d;
    val = Math.max(1, Math.min(val, parseInt(inp.max) || 99));
    inp.value = val;
}

function addToCart() {
    const vid = document.getElementById('selected-variant-id').value;
    const qty = parseInt(document.getElementById('qty-input').value);
    if (!vid) { showToast('Vui lòng chọn biến thể.', 'error'); return; }

    const btn = document.getElementById('add-to-cart-btn');
    btn.disabled = true; btn.textContent = 'Đang thêm...';

    fetch('/api/cart/items', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''},
        body: JSON.stringify({variant_id: parseInt(vid), quantity: qty})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Đã thêm vào giỏ hàng!', 'success');
            updateCartBadge(data.data.items.length);
        } else {
            showToast(data.error?.message || 'Lỗi', 'error');
        }
        btn.disabled = false; btn.textContent = 'Thêm vào giỏ hàng';
    })
    .catch(() => { showToast('Lỗi kết nối.', 'error'); btn.disabled = false; btn.textContent = 'Thêm vào giỏ hàng'; });
}

function switchImage(thumb, path) {
    document.querySelectorAll('.pdp-thumbnail').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
    const img = document.getElementById('main-img');
    if (img) img.src = '/' + path;
}

function toggleDisclosure(el) {
    el.nextElementSibling.classList.toggle('open');
    el.querySelector('span:last-child').textContent = el.nextElementSibling.classList.contains('open') ? '▴' : '▾';
}
</script>

