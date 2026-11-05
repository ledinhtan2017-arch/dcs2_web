<?php
/**
 * Admin Users list.
 * Variables: $users
 */
?>
<h1>Quản lý người dùng</h1>

<?php if (empty($users)): ?>
    <div class="empty-state"><p>Không có người dùng nào.</p></div>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= (int)$u['id'] ?></td>
                <td style="font-weight:500;"><?= htmlspecialchars($u['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td>
                    <span class="badge <?= ($u['role'] ?? '') === 'admin' ? 'badge-confirmed' : 'badge-draft' ?>">
                        <?= ($u['role'] ?? '') === 'admin' ? 'Admin' : 'Khách hàng' ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge-<?= ($u['status'] ?? 'active') === 'active' ? 'active' : 'locked' ?>">
                        <?= ($u['status'] ?? 'active') === 'active' ? 'Hoạt động' : 'Đã khóa' ?>
                    </span>
                </td>
                <td><?= date('d/m/Y', strtotime($u['created_at'] ?? 'now')) ?></td>
                <td>
                    <?php if (($u['role'] ?? '') !== 'admin'): ?>
                        <?php if (($u['status'] ?? 'active') === 'active'): ?>
                            <button type="button" class="btn btn-sm btn-danger"
                                    onclick="toggleUser(<?= (int)$u['id'] ?>, 'locked')">Khóa</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-primary"
                                    onclick="toggleUser(<?= (int)$u['id'] ?>, 'active')">Mở khóa</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
function toggleUser(userId, newStatus) {
    var action = newStatus === 'locked' ? 'khóa' : 'mở khóa';
    if (!confirm('Bạn có chắc muốn ' + action + ' người dùng này?')) return;

    apiFetch('/api/admin/users/' + userId, {
        method: 'PATCH',
        body: JSON.stringify({ status: newStatus })
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            showToast('Đã ' + action + ' thành công!');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            showToast(json.message || 'Cập nhật thất bại', 'error');
        }
    })
    .catch(function() { showToast('Lỗi kết nối', 'error'); });
}
</script>

