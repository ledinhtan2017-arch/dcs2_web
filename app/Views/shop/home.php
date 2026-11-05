<?php
/** @var array $sports */
/** @var array $featured */
/** @var array $newArrivals */
$formatPrice = function($p) { return number_format((int)$p, 0, ',', '.') . ' ₫'; };
?>
<!-- Hero Section -->
<section class="hero-section">
    <div style="width:100%;height:100%;background:linear-gradient(135deg,rgba(0,0,0,.7),rgba(0,0,0,.3)),url('https://i.pinimg.com/1200x/27/64/68/276468ac7bc7e6b5f2459a499104a478.jpg') center/cover no-repeat;display:flex;align-items:center;justify-content:center;">
        <div class="hero-content" style="position:relative;text-align:left;bottom:auto;max-width:800px;padding:48px;">
            <h1 class="hero-title">Trang bị cho<br>chiến thắng</h1>
            <p class="hero-subtitle">Đồ thể thao chính hãng — Cầu lông, Bóng đá, Bóng rổ, Chạy bộ & Fitness</p>
            <a href="/search?q=" class="btn btn-outline" style="background:#fff;color:#111;">Khám phá ngay</a>
        </div>
    </div>
</section>

<div class="container">
    <!-- Shop by Sport -->
    <section class="section">
        <h2 class="section-title">Mua theo môn thể thao</h2>
        <div class="sport-grid">
            <?php
            $sportImages = [
                'cau-long' => 'poster_caulong.jpg',
                'bong-da' => 'poster_bongda.jpg',
                'bong-ro' => 'poster_bongro.jpg',
                'chay-bo-fitness' => 'poster_chaybo.jpg',
            ];
            ?>
            <?php foreach ($sports as $sport): ?>
            <a href="/sports/<?= htmlspecialchars($sport['slug']) ?>" class="sport-card">
                <?php if (isset($sportImages[$sport['slug']])): ?>
                    <img src="/assets/images/sports/<?= htmlspecialchars($sportImages[$sport['slug']]) ?>" alt="" loading="lazy">
                <?php else: ?>
                    <div class="sport-card-placeholder" aria-hidden="true">🏅</div>
                <?php endif; ?>
                <div class="sport-card-overlay">
                    <span class="sport-card-name"><?= htmlspecialchars($sport['name']) ?></span>
                    <span class="btn btn-outline">Xem ngay</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!empty($brands)): ?>
    <section class="section" aria-labelledby="brands-title">
        <h2 class="section-title" id="brands-title">Thương hiệu nổi bật</h2>
        <div class="brand-grid">
            <?php foreach ($brands as $brand): ?>
                <div class="brand-card">
                    <?php if (!empty($brand['logo_path'])): ?>
                        <img src="/<?= htmlspecialchars(ltrim($brand['logo_path'], '/'), ENT_QUOTES, 'UTF-8') ?>"
                             alt="" loading="lazy" onerror="this.hidden=true">
                    <?php endif; ?>
                    <span><?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Products -->
    <?php if (!empty($featured)): ?>
    <section class="section">
        <h2 class="section-title">Sản phẩm nổi bật</h2>
        <div class="product-grid">
            <?php foreach ($featured as $product): ?>
            <a href="/products/<?= htmlspecialchars($product['slug']) ?>" class="product-card">
                <div class="product-card-image">
                    <?php if ($product['primary_image']): ?>
                        <img src="/<?= htmlspecialchars($product['primary_image']) ?>" alt="<?= htmlspecialchars($product['primary_image_alt'] ?? $product['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:64px;color:var(--color-hairline);">📦</div>
                    <?php endif; ?>
                    <span class="product-card-badge">Nổi bật</span>
                </div>
                <div class="product-card-info">
                    <?php if (!empty($product['brand_name'])): ?>
                        <div class="product-card-category"><?= htmlspecialchars($product['brand_name']) ?></div>
                    <?php endif; ?>
                    <div class="product-card-name"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="product-card-price">
                        <?php if (!empty($product['has_sale']) && $product['has_sale']): ?>
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
    </section>
    <?php endif; ?>

    <!-- New Arrivals -->
    <?php if (!empty($newArrivals)): ?>
    <section class="section">
        <h2 class="section-title">Mới nhất</h2>
        <div class="product-grid">
            <?php foreach (array_slice($newArrivals, 0, 6) as $product): ?>
            <a href="/products/<?= htmlspecialchars($product['slug']) ?>" class="product-card">
                <div class="product-card-image">
                    <?php if ($product['primary_image']): ?>
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
                        <?php if (!empty($product['has_sale']) && $product['has_sale']): ?>
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
    </section>
    <?php endif; ?>
</div>

