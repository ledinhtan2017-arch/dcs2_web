<?php
/**
 * CartController – Cart page rendering.
 */

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;

class CartController
{
    public function show(): void
    {
        $cart  = Cart::getOrCreate();
        $items = Cart::getItems((int) $cart['id']);
        $totals = Cart::calculateTotals($items);

        View::page('shop.cart', [
            'title'  => 'Giỏ hàng - Sport Shop',
            'items'  => $items,
            'totals' => $totals,
            'cartId' => $cart['id'],
        ]);
    }
}

