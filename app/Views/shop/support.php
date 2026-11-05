<style>
    .support-page { max-width: 1100px; margin: 0 auto; padding: 30px 24px 48px; }
    .support-heading { margin: 34px 0; max-width: 700px; }
    .support-heading h1 { margin: 8px 0 12px; font-size: clamp(32px, 5vw, 48px); font-weight: 700; }
    .support-heading p { color: var(--color-mute); }
    .support-layout { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 48px; align-items: start; }
    .support-nav { display: grid; gap: 4px; }
    .support-nav a { display: block; padding: 12px 16px; border-radius: 10px; color: var(--color-ash); }
    .support-nav a[aria-current="page"] { background: var(--color-ink); color: white; font-weight: 500; }
    .support-content { border-top: 1px solid var(--color-hairline-soft); }
    .support-section { padding: 24px 0; border-bottom: 1px solid var(--color-hairline-soft); }
    .support-section h2 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
    .support-section p { color: var(--color-ash); line-height: 1.7; }
    .support-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
    @media (max-width: 700px) {
        .support-page { padding: 24px 18px 36px; }
        .support-layout { grid-template-columns: 1fr; gap: 28px; }
        .support-nav { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .support-nav a { font-size: 14px; }
    }
</style>
<div class="support-page">
    <nav class="breadcrumb" aria-label="Đường dẫn">
        <a href="/">Trang chủ</a><span class="breadcrumb-sep">›</span><span>Hỗ trợ</span><span class="breadcrumb-sep">›</span><span><?= htmlspecialchars($page['title']) ?></span>
    </nav>
    <header class="support-heading">
        <p>SPORT SHOP / HỖ TRỢ</p>
        <h1><?= htmlspecialchars($page['title']) ?></h1>
        <p><?= htmlspecialchars($page['intro']) ?></p>
    </header>
    <div class="support-layout">
        <nav class="support-nav" aria-label="Các trang hỗ trợ">
            <?php foreach ($supportPages as $slug => $supportPage): ?>
                <a href="/support/<?= htmlspecialchars($slug) ?>" <?= $slug === $currentSlug ? 'aria-current="page"' : '' ?>><?= htmlspecialchars($supportPage['title']) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="support-content">
            <?php foreach ($page['sections'] as [$heading, $body]): ?>
                <section class="support-section">
                    <h2><?= htmlspecialchars($heading) ?></h2>
                    <p><?= htmlspecialchars($body) ?></p>
                </section>
            <?php endforeach; ?>
            <div class="support-actions">
                <a class="btn btn-primary" href="/account/orders">Xem đơn hàng</a>
                <a class="btn btn-outline" href="/">Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
</div>
