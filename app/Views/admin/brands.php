<?php
/**
 * Admin Brands management.
 * Variables: $brands
 */
?>
<h1>Quản lý thương hiệu</h1>

<!-- Add form -->
<div style="background:var(--color-canvas);padding:var(--space-xl);border-radius:var(--space-sm);margin-bottom:var(--space-xl);">
    <h3 style="margin-bottom:var(--space-lg);">Thêm thương hiệu</h3>
    <form id="add-brand-form" style="display:flex;gap:var(--space-md);align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;flex:1;min-width:200px;">
            <label class="form-label">Tên thương hiệu</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:200px;">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-input" placeholder="VD: yonex">
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="height:48px;">Thêm</button>
    </form>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên thương hiệu</th>
            <th>Slug</th>
            <th>Trạng thái</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($brands ?? []) as $b): ?>
        <tr data-id="<?= (int)$b['id'] ?>">
            <td><?= (int)$b['id'] ?></td>
            <td><input type="text" class="form-input" value="<?= htmlspecialchars($b['name'] ?? '') ?>" data-field="name" style="padding:6px 10px;"></td>
            <td style="font-size:13px;"><?= htmlspecialchars($b['slug'] ?? '') ?></td>
            <td>
                <span class="badge badge-<?= ((int)($b['is_active'] ?? 1)) ? 'active' : 'inactive' ?>">
                    <?= ((int)($b['is_active'] ?? 1)) ? 'Hoạt động' : 'Ẩn' ?>
                </span>
            </td>
            <td><button type="button" class="btn btn-sm btn-outline" onclick="updateBrand(<?= (int)$b['id'] ?>, this)">Lưu</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.getElementById('add-brand-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var f = new FormData(this);
    apiFetch('/api/admin/brands', { method:'POST', body:JSON.stringify({name:f.get('name'),slug:f.get('slug')}) })
    .then(function(r){return r.json();})
    .then(function(j){ if(j.success){showToast('Đã thêm!');location.reload();}else{showToast(j.message||'Lỗi','error');} })
    .catch(function(){showToast('Lỗi','error');});
});

function updateBrand(id, btn) {
    var row = btn.closest('tr');
    var name = row.querySelector('input[data-field="name"]').value;
    apiFetch('/api/admin/brands/' + id, { method:'PATCH', body:JSON.stringify({name:name}) })
    .then(function(r){return r.json();})
    .then(function(j){ if(j.success){showToast('Đã cập nhật!');}else{showToast(j.message||'Lỗi','error');} })
    .catch(function(){showToast('Lỗi','error');});
}
</script>

