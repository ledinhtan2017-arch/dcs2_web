<?php
/**
 * Search results page.
 * Variables: $query, $products, $pagination
 */
$q = htmlspecialchars($query ?? '');
?>
<section class="container section" style="padding-top:var(--space-xl);">
    <div class="plp-toolbar">
        <div class="breadcrumb">
            <a href="/">Trang chủ</a>
            <span class="breadcrumb-sep">›</span>
            <span>Tìm kiếm</span>
        </div>
    </div>

    <h1>Kết quả tìm kiếm cho "<?= $q ?>"</h1>
    <p style="color:var(--color-mute);margin-bottom:var(--space-xl);">
        Tìm thấy <?= (int)($pagination['total'] ?? count($products ?? [])) ?> sản phẩm
    </p>

    <?php if (!empty($products)): ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <a href="/products/<?= htmlspecialchars($p['slug']) ?>" class="product-card">
                    <div class="product-card-image">
                        <img src="<?= htmlspecialchars($p['primary_image'] ?? '/assets/images/products/placeholder.jpg') ?>"
                             alt="<?= htmlspecialchars($p['primary_image_alt'] ?? $p['name']) ?>" loading="lazy">
                    </div>
                    <div class="product-card-info">
                        <div class="product-card-category"><?= htmlspecialchars($p['brand_name'] ?? '') ?></div>
                        <div class="product-card-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="product-card-price">
                            <?php if (!empty($p['has_sale'])): ?>
                                <span class="price-sale"><?= number_format((int)($p['min_effective_price'] ?? 0), 0, ',', '.') ?> ₫</span>
                                <span class="price-original"><?= number_format((int)($p['max_price'] ?? 0), 0, ',', '.') ?> ₫</span>
                            <?php else: ?>
                                <?= number_format((int)($p['min_effective_price'] ?? $p['min_price'] ?? 0), 0, ',', '.') ?> ₫
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (isset($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i === ($pagination['current_page'] ?? 1)): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="/search?q=<?= urlencode($query ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>Không tìm thấy sản phẩm nào</h3>
            <p>Hãy thử tìm kiếm với từ khóa khác.</p>
            <a href="/" class="btn btn-primary">Quay lại trang chủ</a>
        </div>
    <?php endif; ?>
</section>

