<?php
/**
 * Register page.
 */
?>
<div class="auth-container">
    <h1>Đăng ký tài khoản</h1>
    <form action="/register" method="POST">
        <?= $csrf_field ?>

        <div class="form-group">
            <label class="form-label" for="name">Họ tên *</label>
            <input type="text" name="name" id="name" class="form-input" required minlength="2" maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email *</label>
            <input type="email" name="email" id="email" class="form-input"
                   placeholder="email@example.com" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="phone">Số điện thoại</label>
            <input type="tel" name="phone" id="phone" class="form-input"
                   placeholder="0912345678">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Mật khẩu *</label>
            <input type="password" name="password" id="password" class="form-input"
                   placeholder="Tối thiểu 6 ký tự" required minlength="6">
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Xác nhận mật khẩu *</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input"
                   placeholder="Nhập lại mật khẩu" required minlength="6">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:var(--space-md);">
            Đăng ký
        </button>
    </form>

    <p style="text-align:center;margin-top:var(--space-xl);color:var(--color-mute);">
        Đã có tài khoản? <a href="/login" style="color:var(--color-ink);font-weight:500;">Đăng nhập</a>
    </p>
</div>

