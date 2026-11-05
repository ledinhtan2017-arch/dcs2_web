<?php
/**
 * Admin Products list.
 * Variables: $products
 */
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-xl);">
    <h1 style="margin:0;">Quản lý sản phẩm</h1>
    <a href="/admin/products/create" class="btn btn-primary btn-sm">+ Thêm sản phẩm</a>
</div>

<?php if (empty($products)): ?>
    <div class="empty-state"><p>Chưa có sản phẩm nào.</p></div>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên sản phẩm</th>
                <th>Thương hiệu</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= (int)$p['id'] ?></td>
                <td style="font-weight:500;max-width:250px;">
                    <?= htmlspecialchars($p['name'] ?? '') ?>
                </td>
                <td><?= htmlspecialchars($p['brand_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                <td><?= number_format((int)($p['price'] ?? 0), 0, ',', '.') ?> ₫</td>
                <td>
                    <?php $stock = (int)($p['total_stock'] ?? 0); ?>
                    <span style="color:<?= $stock <= 5 ? 'var(--color-sale)' : 'var(--color-ink)' ?>;font-weight:500;">
                        <?= $stock ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge-<?= $p['status'] === 'active' ? 'active' : 'inactive' ?>">
                        <?= ['active' => 'Hoạt động', 'inactive' => 'Ẩn', 'draft' => 'Bản nháp'][$p['status']] ?? htmlspecialchars($p['status']) ?>
                    </span>
                </td>
                <td><a href="/admin/products/<?= (int)$p['id'] ?>/edit" class="btn btn-sm btn-outline">Sửa</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

