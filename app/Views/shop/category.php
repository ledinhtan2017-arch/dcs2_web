<?php
/** @var array $category */
/** @var array $products */
/** @var array $pagination */
/** @var array $filterOptions */
/** @var string $sort */
$formatPrice = function($p) { return number_format((int)$p, 0, ',', '.') . ' ₫'; };
$currentUrl = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
?>
<div class="container" style="padding-top:var(--space-xl);">
    <div class="plp-toolbar">
        <div class="breadcrumb">
            <a href="/">Trang chủ</a><span class="breadcrumb-sep">/</span>
            <a href="/sports/<?= htmlspecialchars($category['sport_slug']) ?>"><?= htmlspecialchars($category['sport_name']) ?></a>
            <span class="breadcrumb-sep">/</span>
            <span><?= htmlspecialchars($category['name']) ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:14px;color:var(--color-mute);"><?= $pagination['total'] ?> sản phẩm</span>
            <select class="sort-select" onchange="location.href='<?= htmlspecialchars($currentUrl) ?>?sort='+this.value" aria-label="Sắp xếp">
                <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Mới nhất</option>
                <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Giá tăng dần</option>
                <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Giá giảm dần</option>
                <option value="name_asc" <?= $sort==='name_asc'?'selected':'' ?>>Tên A-Z</option>
            </select>
        </div>
    </div>

    <div class="plp-layout">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <form method="GET" action="<?= htmlspecialchars($currentUrl) ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

                <?php if (!empty($filterOptions['brands'])): ?>
                <div class="filter-group">
                    <div class="filter-group-title">Thương hiệu</div>
                    <?php foreach ($filterOptions['brands'] as $b): ?>
                    <label class="filter-option">
                        <input type="radio" name="brand" value="<?= $b['id'] ?>" <?= ($filters['brand'] ?? '')==$b['id']?'checked':'' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($filterOptions['sizes'])): ?>
                <div class="filter-group">
                    <div class="filter-group-title">Size</div>
                    <?php foreach ($filterOptions['sizes'] as $sz): ?>
                    <label class="filter-option">
                        <input type="radio" name="size" value="<?= htmlspecialchars($sz) ?>" <?= ($filters['size'] ?? '')===$sz?'checked':'' ?>>
                        <?= htmlspecialchars($sz) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-sm" style="width:100%;margin-top:12px;">Lọc</button>
                <a href="<?= htmlspecialchars($currentUrl) ?>" class="btn btn-secondary btn-sm" style="width:100%;margin-top:8px;text-align:center;">Xóa bộ lọc</a>
            </form>
        </aside>

        <!-- Product Grid -->
        <div>
            <h1 style="font-size:24px;margin-bottom:var(--space-xl);"><?= htmlspecialchars($category['name']) ?></h1>
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <h3>Không tìm thấy sản phẩm</h3>
                    <p>Thử thay đổi bộ lọc hoặc <a href="<?= htmlspecialchars($currentUrl) ?>">xem tất cả</a>.</p>
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
                            <div class="product-card-price">
                                <?php if (!empty($product['has_sale'])): ?>
                                    <span class="price-sale"><?= $formatPrice($product['min_effective_price']) ?></span>
                                    <span class="price-original"><?= $formatPrice($product['max_price']) ?></span>
                                <?php else: ?>
                                    <?= $formatPrice($product['min_effective_price'] ?? 0) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i === $pagination['page']): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($currentUrl) ?>?page=<?= $i ?>&sort=<?= htmlspecialchars($sort) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

