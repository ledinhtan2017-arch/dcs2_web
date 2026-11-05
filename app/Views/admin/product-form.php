<?php
/**
 * Admin Product Create/Edit Form.
 * Variables: $product (null for create), $categories, $brands, $sports
 */
$isEdit = !empty($product);
$formAction = $isEdit ? '/api/admin/products/' . (int)$product['id'] : '/api/admin/products';
$formMethod = $isEdit ? 'PATCH' : 'POST';
?>
<div style="max-width:800px;">
    <div class="breadcrumb" style="margin-bottom:var(--space-xl);">
        <a href="/admin/products">Sản phẩm</a>
        <span class="breadcrumb-sep">›</span>
        <span><?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></span>
    </div>

    <h1><?= $isEdit ? 'Sửa: ' . htmlspecialchars($product['name']) : 'Thêm sản phẩm mới' ?></h1>

    <form id="product-form">
        <div class="form-group">
            <label class="form-label" for="name">Tên sản phẩm *</label>
            <input type="text" name="name" id="name" class="form-input"
                   value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="category_id">Danh mục *</label>
                <select name="category_id" id="category_id" class="form-input" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"
                            <?= (int)($product['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(($cat['sport_name'] ?? '') . ' > ' . $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="brand_id">Thương hiệu</label>
                <select name="brand_id" id="brand_id" class="form-input">
                    <option value="">-- Chọn thương hiệu --</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= (int)$b['id'] ?>"
                            <?= (int)($product['brand_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="short_description">Mô tả ngắn</label>
            <textarea name="short_description" id="short_description" class="form-input" rows="2"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Mô tả chi tiết</label>
            <textarea name="description" id="description" class="form-input" rows="6"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <!-- Sports -->
        <div class="form-group">
            <label class="form-label">Môn thể thao</label>
            <div style="display:flex;gap:var(--space-lg);flex-wrap:wrap;">
                <?php
                $productSportIds = [];
                if ($isEdit && !empty($product['sport_ids'])) {
                    $productSportIds = is_array($product['sport_ids']) ? $product['sport_ids'] : explode(',', $product['sport_ids']);
                }
                ?>
                <?php foreach ($sports as $s): ?>
                    <label class="filter-option">
                        <input type="checkbox" name="sport_ids[]" value="<?= (int)$s['id'] ?>"
                            <?= in_array((int)$s['id'], array_map('intval', $productSportIds)) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="status">Trạng thái sản phẩm</label>
            <select name="status" id="status" class="form-input">
                <?php foreach (['active' => 'Hoạt động', 'inactive' => 'Ẩn', 'draft' => 'Bản nháp'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($product['status'] ?? 'draft') === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Variants Section -->
        <h2 style="margin:var(--space-xl) 0 var(--space-lg);border-top:1px solid var(--color-hairline-soft);padding-top:var(--space-xl);">
            Biến thể sản phẩm
        </h2>
        <p style="color:var(--color-mute);margin-bottom:var(--space-md);">Mỗi biến thể có SKU, giá và tồn kho riêng. Bỏ chọn “Đang bán” để ngừng bán mà vẫn giữ lịch sử đơn hàng.</p>
        <div id="variants-container">
            <?php if ($isEdit && !empty($product['variants'])): ?>
                <?php foreach ($product['variants'] as $idx => $v): ?>
                    <div class="variant-row" style="padding:var(--space-lg);background:var(--color-soft-cloud);margin-bottom:var(--space-md);border-radius:var(--space-sm);">
                        <input type="hidden" data-field="id" value="<?= (int)$v['id'] ?>">
                        <input type="hidden" data-field="original_stock" value="<?= (int)$v['stock_quantity'] ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Kích thước</label>
                                <input type="text" data-field="size_label" class="form-input" maxlength="50"
                                       value="<?= htmlspecialchars($v['size_label'] ?? '') ?>" placeholder="VD: 42">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Màu sắc</label>
                                <input type="text" data-field="color_name" class="form-input" maxlength="50" value="<?= htmlspecialchars($v['color_name'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group"><label class="form-label">SKU *</label><input type="text" data-field="sku" class="form-input" maxlength="100" value="<?= htmlspecialchars($v['sku'] ?? '') ?>" required></div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Giá (₫) *</label>
                                <input type="number" data-field="price" class="form-input" min="0" max="999999999999" step="1" value="<?= (int)$v['price'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Giá khuyến mãi (₫)</label>
                                <input type="number" data-field="sale_price" class="form-input" min="1" max="999999999999" step="1" value="<?= $v['sale_price'] === null ? '' : (int)$v['sale_price'] ?>">
                            </div>
                        </div>
                        <div class="form-group"><label class="form-label">Tồn kho *</label><input type="number" data-field="stock_quantity" class="form-input" min="0" max="4294967295" step="1" value="<?= (int)$v['stock_quantity'] ?>" required></div>
                        <label><input type="checkbox" data-field="is_active" <?= $v['is_active'] ? 'checked' : '' ?>> Đang bán</label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-sm btn-secondary" onclick="addVariantRow()">+ Thêm biến thể</button>
        <p id="product-message" role="status" style="margin-top:var(--space-md);"></p>

        <div style="margin-top:var(--space-xl);display:flex;gap:var(--space-md);">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Tạo sản phẩm' ?></button>
            <a href="/admin/products" class="btn btn-outline">Hủy</a>
        </div>
    </form>

    <section style="margin-top:var(--space-xl);padding-top:var(--space-xl);border-top:1px solid var(--color-hairline-soft);">
        <h2>Ảnh sản phẩm</h2>
        <?php if (!$isEdit): ?>
            <p style="margin-top:var(--space-md);color:var(--color-mute);">Sau khi tạo sản phẩm, mở lại trang sửa để thêm ảnh.</p>
        <?php else: ?>
            <p style="margin:var(--space-sm) 0 var(--space-lg);color:var(--color-mute);">Ảnh chính hiển thị trên danh sách sản phẩm. Chọn ảnh JPG, PNG hoặc WebP, tối đa 5 MB.</p>
            <div style="display:flex;gap:var(--space-md);flex-wrap:wrap;">
                <?php foreach (($product['images'] ?? []) as $image): ?>
                    <div style="width:150px;border:1px solid var(--color-hairline-soft);padding:8px;">
                        <img src="/<?= htmlspecialchars(ltrim($image['image_path'], '/'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($image['alt_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:100%;height:130px;object-fit:contain;">
                        <?php if ($image['is_primary']): ?>
                            <p style="margin-top:8px;font-weight:600;">Ảnh chính</p>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-secondary image-primary" data-image-id="<?= (int) $image['id'] ?>" style="margin-top:8px;">Chọn làm ảnh chính</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <form id="image-upload-form" style="margin-top:var(--space-lg);">
                <label class="form-label" for="product-image">Thêm ảnh</label>
                <input id="product-image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required>
                <label style="display:block;margin:var(--space-sm) 0;"><input type="checkbox" name="is_primary" value="1"> Đặt làm ảnh chính</label>
                <button type="submit" class="btn btn-primary btn-sm">Tải ảnh lên</button>
            </form>
            <p id="image-message" role="status" style="margin-top:var(--space-sm);"></p>
        <?php endif; ?>
    </section>
</div>

<script>
<?php if ($isEdit): ?>
var imageMessage = document.getElementById('image-message');
function imageRequest(url, options) {
    options.headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    };
    options.credentials = 'same-origin';
    imageMessage.textContent = 'Đang xử lý...';
    return fetch(url, options).then(function(response) { return response.json(); }).then(function(result) {
        if (!result.success) throw new Error((result.error && result.error.message) || 'Không thể cập nhật ảnh.');
        window.location.reload();
    }).catch(function(error) { imageMessage.textContent = error.message || 'Lỗi kết nối.'; });
}
document.getElementById('image-upload-form').addEventListener('submit', function(event) {
    event.preventDefault();
    imageRequest('/api/admin/products/<?= (int) $product['id'] ?>/images', { method: 'POST', body: new FormData(this) });
});
document.querySelectorAll('.image-primary').forEach(function(button) {
    button.addEventListener('click', function() {
        imageRequest('/api/admin/products/<?= (int) $product['id'] ?>/images/' + button.dataset.imageId + '/primary', { method: 'POST' });
    });
});
<?php endif; ?>
function addVariantRow() {
    var container = document.getElementById('variants-container');
    var html = '<div class="variant-row" style="padding:var(--space-lg);background:var(--color-soft-cloud);margin-bottom:var(--space-md);border-radius:var(--space-sm);">'
        + '<div class="form-grid">'
        + '<div class="form-group"><label class="form-label">Kích thước</label><input type="text" data-field="size_label" maxlength="50" class="form-input"></div>'
        + '<div class="form-group"><label class="form-label">Màu sắc</label><input type="text" data-field="color_name" maxlength="50" class="form-input"></div></div>'
        + '<div class="form-group"><label class="form-label">SKU *</label><input type="text" data-field="sku" maxlength="100" class="form-input" required></div>'
        + '<div class="form-grid">'
        + '<div class="form-group"><label class="form-label">Giá (₫) *</label><input type="number" data-field="price" class="form-input" min="0" max="999999999999" step="1" required></div>'
        + '<div class="form-group"><label class="form-label">Giá khuyến mãi (₫)</label><input type="number" data-field="sale_price" class="form-input" min="1" max="999999999999" step="1"></div></div>'
        + '<div class="form-group"><label class="form-label">Tồn kho *</label><input type="number" data-field="stock_quantity" class="form-input" min="0" max="4294967295" step="1" value="0" required></div>'
        + '<label><input type="checkbox" data-field="is_active" checked> Đang bán</label> '
        + '<button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'.variant-row\').remove()">Bỏ biến thể mới</button></div>';
    container.insertAdjacentHTML('beforeend', html);
}

<?php if (!$isEdit): ?>addVariantRow();<?php endif; ?>

document.getElementById('product-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    var data = {};

    // Build JSON from form data
    data.name = formData.get('name');
    data.category_id = parseInt(formData.get('category_id'));
    data.brand_id = formData.get('brand_id') || null;
    data.short_description = formData.get('short_description') || '';
    data.description = formData.get('description') || '';
    data.status = formData.get('status');
    data.sport_ids = formData.getAll('sport_ids[]').map(Number);

    // Collect variants
    data.variants = [];
    var rows = document.querySelectorAll('.variant-row');
    rows.forEach(function(row) {
        var field = function(name) { return row.querySelector('[data-field="' + name + '"]'); };
        data.variants.push({
            id: field('id') ? field('id').value : null,
            original_stock: field('original_stock') ? field('original_stock').value : null,
            sku: field('sku').value,
            size_label: field('size_label').value,
            color_name: field('color_name').value,
            price: field('price').value,
            sale_price: field('sale_price').value,
            stock_quantity: field('stock_quantity').value,
            is_active: field('is_active').checked
        });
    });

    var message = document.getElementById('product-message');
    if (!data.variants.length) { message.textContent = 'Hãy thêm ít nhất một biến thể.'; return; }
    message.textContent = 'Đang lưu...';
    var submitButton = form.querySelector('[type="submit"]');
    submitButton.disabled = true;

    apiFetch('<?= $formAction ?>', {
        method: '<?= $formMethod ?>',
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            message.textContent = 'Đã lưu sản phẩm.';
            window.location.href = '/admin/products';
        } else {
            message.textContent = (json.error && json.error.message) || 'Không thể lưu sản phẩm.';
            submitButton.disabled = false;
        }
    })
    .catch(function() { message.textContent = 'Lỗi kết nối. Vui lòng thử lại.'; submitButton.disabled = false; });
});
</script>

