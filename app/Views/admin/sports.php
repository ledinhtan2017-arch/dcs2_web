<?php
/**
 * Admin Sports management.
 * Variables: $sports
 */
?>
<h1>Quản lý môn thể thao</h1>

<!-- Add form -->
<div style="background:var(--color-canvas);padding:var(--space-xl);border-radius:var(--space-sm);margin-bottom:var(--space-xl);">
    <h3 style="margin-bottom:var(--space-lg);">Thêm môn thể thao</h3>
    <form id="add-sport-form" style="display:flex;gap:var(--space-md);align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;flex:1;min-width:200px;">
            <label class="form-label">Tên</label>
            <input type="text" name="name" class="form-input" required>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:200px;">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-input" placeholder="VD: cau-long">
        </div>
        <div class="form-group" style="margin:0;width:80px;">
            <label class="form-label">Thứ tự</label>
            <input type="number" name="sort_order" class="form-input" value="0" min="0">
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="height:48px;">Thêm</button>
    </form>
</div>

<table class="data-table" id="sports-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Slug</th>
            <th>Thứ tự</th>
            <th>Trạng thái</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($sports ?? []) as $s): ?>
        <tr data-id="<?= (int)$s['id'] ?>">
            <td><?= (int)$s['id'] ?></td>
            <td><input type="text" class="form-input" value="<?= htmlspecialchars($s['name'] ?? '') ?>" data-field="name" style="padding:6px 10px;"></td>
            <td><input type="text" class="form-input" value="<?= htmlspecialchars($s['slug'] ?? '') ?>" data-field="slug" style="padding:6px 10px;"></td>
            <td><input type="number" class="form-input" value="<?= (int)($s['sort_order'] ?? 0) ?>" data-field="sort_order" style="padding:6px 10px;width:70px;" min="0"></td>
            <td>
                <span class="badge badge-<?= ((int)($s['is_active'] ?? 1)) ? 'active' : 'inactive' ?>">
                    <?= ((int)($s['is_active'] ?? 1)) ? 'Hoạt động' : 'Ẩn' ?>
                </span>
            </td>
            <td><button type="button" class="btn btn-sm btn-outline" onclick="updateSport(<?= (int)$s['id'] ?>, this)">Lưu</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.getElementById('add-sport-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var f = new FormData(this);
    apiFetch('/api/admin/sports', { method:'POST', body:JSON.stringify({name:f.get('name'),slug:f.get('slug'),sort_order:parseInt(f.get('sort_order'))||0}) })
    .then(function(r){return r.json();})
    .then(function(j){ if(j.success){showToast('Đã thêm!');location.reload();}else{showToast(j.message||'Lỗi','error');} })
    .catch(function(){showToast('Lỗi','error');});
});

function updateSport(id, btn) {
    var row = btn.closest('tr');
    var data = {};
    row.querySelectorAll('input[data-field]').forEach(function(el) {
        data[el.dataset.field] = el.type === 'number' ? parseInt(el.value) || 0 : el.value;
    });
    apiFetch('/api/admin/sports/' + id, { method:'PATCH', body:JSON.stringify(data) })
    .then(function(r){return r.json();})
    .then(function(j){ if(j.success){showToast('Đã cập nhật!');}else{showToast(j.message||'Lỗi','error');} })
    .catch(function(){showToast('Lỗi','error');});
}
</script>

