<?php
/**
 * Profile page.
 * Variables: $user
 */
?>
<section class="container section" style="padding-top:var(--space-xl);">
    <div style="max-width:600px;margin:0 auto;">
        <h1>Tài khoản của tôi</h1>

        <div style="display:flex;gap:var(--space-lg);margin-bottom:var(--space-xl);padding-bottom:var(--space-lg);border-bottom:1px solid var(--color-hairline-soft);">
            <a href="/account" style="font-weight:700;border-bottom:2px solid var(--color-ink);padding-bottom:4px;">Thông tin</a>
            <a href="/account/orders" style="color:var(--color-mute);">Đơn hàng</a>
        </div>

        <form action="/account/update" method="POST">
            <?= $csrf_field ?>

            <div class="form-group">
                <label class="form-label" for="name">Họ tên *</label>
                <input type="text" name="name" id="name" class="form-input"
                       value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                <div class="form-error" style="color:var(--color-mute);">Email không thể thay đổi.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Số điện thoại</label>
                <input type="tel" name="phone" id="phone" class="form-input"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>

            <hr style="margin:var(--space-xl) 0;border-color:var(--color-hairline-soft);">
            <h2 style="margin-bottom:var(--space-lg);">Đổi mật khẩu</h2>
            <p style="font-size:14px;color:var(--color-mute);margin-bottom:var(--space-lg);">Để trống nếu không muốn thay đổi.</p>

            <div class="form-group">
                <label class="form-label" for="password">Mật khẩu mới</label>
                <input type="password" name="password" id="password" class="form-input"
                       placeholder="Tối thiểu 6 ký tự" minlength="6">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">
                Cập nhật thông tin
            </button>
        </form>

        <form action="/logout" method="POST" style="margin-top:var(--space-xl);text-align:center;">
            <?= $csrf_field ?>
            <button type="submit" class="btn btn-outline" style="width:100%;">Đăng xuất</button>
        </form>
    </div>
</section>

