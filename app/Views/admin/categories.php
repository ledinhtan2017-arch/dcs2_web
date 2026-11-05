<?php
/**
 * Admin Categories management.
 * Variables: $categories
 */
?>
<h1>Quản lý danh mục</h1>

<!-- Add form -->
<div style="background:var(--color-canvas);padding:var(--space-xl);border-radius:var(--space-sm);margin-bottom:var(--space-xl);">
    <h3 style="margin-bottom:var(--space-lg);">Thêm danh mục</h3>
    <form id="add-category-form" style="display:flex;gap:var(--space-md);align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;flex:1;min-width:200px;">
            <label class="form-label">Tên danh mục</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:200px;">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-input" placeholder="VD: vot-cau-long">
        </div>
        <div class="form-group" style="margin:0;width:150px;">
            <label class="form-label">Môn TT (sport_id)</label>
            <input type="number" name="sport_id" class="form-input" min="1" required>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="height:48px;">Thêm</button>
    </form>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th>Slug</th>
            <th>Môn thể thao</th>
            <th>Trạng thái</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($categories ?? []) as $c): ?>
        <tr data-id="<?= (int)$c['id'] ?>">
            <td><?= (int)$c['id'] ?></td>
            <td><input type="text" class="form-input" value="<?= htmlspecialchars($c['name'] ?? '') ?>" data-field="name" style="padding:6px 10px;"></td>
            <td style="font-size:13px;"><?= htmlspecialchars($c['slug'] ?? '') ?></td>
            <td><?= htmlspecialchars($c['sport_name'] ?? $c['sport_id'] ?? '') ?></td>
            <td>
                <span class="badge badge-<?= ((int)($c['is_active'] ?? 1)) ? 'active' : 'inactive' ?>">
                    <?= ((int)($c['is_active'] ?? 1)) ? 'Hoạt động' : 'Ẩn' ?>
                </span>
            </td>
            <td><button type="button" class="btn btn-sm btn-outline" onclick="updateCategory(<?= (int)$c['id'] ?>, this)">Lưu</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.getElementById('add-category-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var f = new FormData(this);
    apiFetch('/api/admin/categories', { method:'POST', body:JSON.stringify({name:f.get('name'),slug:f.get('slug'),sport_id:parseInt(f.get('sport_id'))}) })
    .then(function(r){return r.json();})
    .then(function(j){ if(j.success){showToast('Đã thêm!');location.reload();}else{showToast(j.message||'Lỗi','error');} })
    .catch(function(){showToast('Lỗi','error');});
});

function updateCategory(id, btn) {
    var row = btn.closest('tr');
    var name = row.querySelector('input[data-field="name"]').value;
    apiFetch('/api/admin/categories/' + id, { method:'PATCH', body:JSON.stringify({name:name}) })
    .then(function(r){return r.json();})
    .then(function(j){ if(j.success){showToast('Đã cập nhật!');}else{showToast(j.message||'Lỗi','error');} })
    .catch(function(){showToast('Lỗi','error');});
}
</script>

