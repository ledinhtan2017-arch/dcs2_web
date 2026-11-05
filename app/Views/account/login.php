<?php
/**
 * Login page.
 */
?>
<div class="auth-container">
    <h1>Đăng nhập</h1>
    <form action="/login" method="POST">
        <?= $csrf_field ?>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" name="email" id="email" class="form-input"
                   placeholder="email@example.com" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Mật khẩu</label>
            <input type="password" name="password" id="password" class="form-input"
                   placeholder="Nhập mật khẩu" required minlength="6">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:var(--space-md);">
            Đăng nhập
        </button>
    </form>

    <p style="text-align:center;margin-top:var(--space-xl);color:var(--color-mute);">
        Chưa có tài khoản? <a href="/register" style="color:var(--color-ink);font-weight:500;">Đăng ký ngay</a>
    </p>
</div>

