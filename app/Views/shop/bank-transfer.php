<?php if (($order['payment_method'] ?? '') === 'bank_transfer'): ?>
<div style="text-align:left;padding:var(--space-lg);margin:var(--space-lg) 0;background:var(--color-soft-cloud);">
    <h2>Thanh toán chuyển khoản</h2>
    <?php if (($order['payment_status'] ?? '') === 'paid'): ?>
        <p>Đã xác nhận thanh toán.</p>
    <?php elseif (($order['order_status'] ?? '') === 'cancelled'): ?>
        <p>Đơn hàng đã hủy.</p>
    <?php elseif (\App\Models\BankTransfer::available()): $bank = \App\Models\BankTransfer::details(); ?>
        <p>Ngân hàng: <strong><?= htmlspecialchars($bank['bank']) ?></strong></p>
        <p>Số tài khoản: <strong><?= htmlspecialchars($bank['account']) ?></strong></p>
        <p>Chủ tài khoản: <strong><?= htmlspecialchars($bank['holder']) ?></strong></p>
        <p>Số tiền: <strong><?= number_format((int)$order['grand_total'], 0, ',', '.') ?> ₫</strong></p>
        <p>Nội dung chuyển khoản: <strong><?= htmlspecialchars($order['order_code']) ?></strong></p>
        <p>Trạng thái: Chờ cửa hàng xác nhận đã nhận tiền.</p>
    <?php else: ?>
        <p>Thông tin chuyển khoản chưa khả dụng. Vui lòng liên hệ cửa hàng.</p>
    <?php endif; ?>
</div>
<?php endif; ?>
