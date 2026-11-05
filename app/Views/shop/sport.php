<?php
/** @var array $sport */
/** @var array $categories */
/** @var array $products */
$formatPrice = function($p) { return number_format((int)$p, 0, ',', '.') . ' ₫'; };
?>
<div class="container" style="padding-top:var(--space-xl);">
    <div class="breadcrumb" style="margin-bottom:var(--space-xl);">
        <a href="/">Trang chủ</a>
        <span class="breadcrumb-sep">/</span>
        <span><?= htmlspecialchars($sport['name']) ?></span>
    </div>

    <h1 style="margin-bottom:var(--space-md);"><?= htmlspecialchars($sport['name']) ?></h1>
    <?php if ($sport['description']): ?>
        <p style="color:var(--color-mute);margin-bottom:var(--space-xl);"><?= htmlspecialchars($sport['description']) ?></p>
    <?php endif; ?>

    <!-- Categories -->
    <?php if (!empty($categories)): ?>
    <section class="section">
        <h2 class="section-title" style="font-size:18px;">Danh mục</h2>
        <div style="display:flex;flex-wrap:wrap;gap:var(--space-sm);margin-bottom:var(--space-xl);">
            <?php foreach ($categories as $cat): ?>
                <a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="btn btn-outline btn-sm">
                    <?= htmlspecialchars($cat['name']) ?>
                    <?php if ($cat['product_count'] > 0): ?>
                        <span style="color:var(--color-mute);font-size:12px;">(<?= $cat['product_count'] ?>)</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Products -->
    <section class="section">
        <h2 class="section-title" style="font-size:18px;">Sản phẩm <?= htmlspecialchars($sport['name']) ?></h2>
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-icon">🏅</div>
                <h3>Chưa có sản phẩm</h3>
                <p>Danh mục này đang được cập nhật.</p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                <a href="/products/<?= htmlspecialchars($product['slug']) ?>" class="product-card">
                    <div class="product-card-image">
                        <?php if (!empty($product['primary_image'])): ?>
                            <img src="/<?= htmlspecialchars($product['primary_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:64px;color:var(--color-hairline);">📦</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-info">
                        <?php if (!empty($product['brand_name'])): ?>
                            <div class="product-card-category"><?= htmlspecialchars($product['brand_name']) ?></div>
                        <?php endif; ?>
                        <div class="product-card-name"><?= htmlspecialchars($product['name']) ?></div>
                        <div class="product-card-price"><?= $formatPrice($product['min_effective_price'] ?? 0) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

