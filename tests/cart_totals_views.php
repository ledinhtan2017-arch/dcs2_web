<?php
/** Verify cart and checkout summaries use the totals calculated by the model. */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
$_ENV['SHIPPING_FEE'] = '30000';
require BASE_PATH . '/app/Config/config.php';
require BASE_PATH . '/app/Models/Cart.php';
require BASE_PATH . '/app/Models/BankTransfer.php';

set_error_handler(static function (int $severity, string $message): void {
    throw new ErrorException($message, 0, $severity);
});

function renderCartView(string $view, array $items): string
{
    $totals = \App\Models\Cart::calculateTotals($items);
    $user = null;
    $csrf_field = '';
    ob_start();
    require BASE_PATH . '/app/Views/shop/' . $view . '.php';
    return ob_get_clean();
}

function assertSummary(string $html, string $label, string $amount, string $view): void
{
    $pattern = '~<div class="summary-row(?: total)?"[^>]*>\s*<span>'
        . preg_quote($label, '~') . '</span>\s*<span(?:\s+[^>]*)?>\s*'
        . preg_quote($amount, '~') . '\s*₫\s*</span>~u';
    if (!preg_match($pattern, $html)) {
        throw new RuntimeException("$view: expected $label = $amount ₫");
    }
}

$items = [
    ['id' => 1, 'product_name' => 'Product 1', 'price' => 100000, 'sale_price' => null, 'effective_price' => 100000, 'quantity' => 2],
    ['id' => 2, 'product_name' => 'Product 2', 'price' => 60000, 'sale_price' => 50000, 'effective_price' => 50000, 'quantity' => 1],
];

foreach (['cart', 'checkout'] as $view) {
    $html = renderCartView($view, $items);
    assertSummary($html, 'Tạm tính', '250.000', $view);
    assertSummary($html, 'Phí vận chuyển', '30.000', $view);
    assertSummary($html, 'Tổng cộng', '280.000', $view);

    // A subsequent render after removing one item must use the updated totals.
    $html = renderCartView($view, [$items[0]]);
    assertSummary($html, 'Tạm tính', '200.000', $view);
    assertSummary($html, 'Phí vận chuyển', '30.000', $view);
    assertSummary($html, 'Tổng cộng', '230.000', $view);
}

$emptyTotals = \App\Models\Cart::calculateTotals([]);
if ($emptyTotals['subtotal'] !== 0 || $emptyTotals['shipping_fee'] !== 0 || $emptyTotals['grand_total'] !== 0) {
    throw new RuntimeException('An empty cart must have zero totals.');
}
echo "Cart and checkout summary checks passed.\n";
