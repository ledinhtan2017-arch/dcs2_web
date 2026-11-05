<?php
namespace App\Controllers\Api;
use App\Core\{Response, Auth};
use App\Models\Cart;

class CartApiController
{
    public function show(): void
    {
        $cart = Cart::getOrCreate();
        $items = Cart::getItems((int)$cart['id']);
        $totals = Cart::calculateTotals($items);
        Response::success(['items' => $items, 'totals' => $totals]);
    }

    public function addItem(): void
    {
        $request = new \App\Core\Request();
        $variantId = (int)$request->input('variant_id');
        $quantity = max(1, (int)$request->input('quantity', 1));

        if (!$variantId) {
            Response::error('Vui lòng chọn biến thể sản phẩm.', 'VALIDATION_ERROR', 422);
        }

        $cart = Cart::getOrCreate();
        $ok = Cart::addItem((int)$cart['id'], $variantId, $quantity);

        if (!$ok) {
            Response::error('Không thể thêm sản phẩm. Có thể đã hết hàng.', 'STOCK_ERROR', 409);
        }

        $items = Cart::getItems((int)$cart['id']);
        $totals = Cart::calculateTotals($items);
        Response::success(['items' => $items, 'totals' => $totals], null, 201);
    }

    public function updateItem(string $id): void
    {
        $request = new \App\Core\Request();
        $quantity = (int)$request->input('quantity', 1);
        $cart = Cart::getOrCreate();
        $ok = Cart::updateItem((int)$id, (int)$cart['id'], $quantity);

        if (!$ok) {
            Response::error('Không thể cập nhật sản phẩm.', 'UPDATE_ERROR', 400);
        }

        $items = Cart::getItems((int)$cart['id']);
        $totals = Cart::calculateTotals($items);
        Response::success(['items' => $items, 'totals' => $totals]);
    }

    public function removeItem(string $id): void
    {
        $cart = Cart::getOrCreate();
        Cart::removeItem((int)$id, (int)$cart['id']);

        $items = Cart::getItems((int)$cart['id']);
        $totals = Cart::calculateTotals($items);
        Response::success(['items' => $items, 'totals' => $totals]);
    }
}

