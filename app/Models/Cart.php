<?php
/**
 * Cart Model – Guest/customer persistent cart.
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Auth;

class Cart
{
    /**
     * Get or create cart for current user/guest.
     */
    public static function getOrCreate(): array
    {
        $pdo = Database::getConnection();

        if (Auth::check()) {
            // Logged-in user cart
            $stmt = $pdo->prepare("SELECT * FROM carts WHERE user_id = :uid LIMIT 1");
            $stmt->execute([':uid' => Auth::id()]);
            $cart = $stmt->fetch();

            if (!$cart) {
                $pdo->prepare("INSERT INTO carts (user_id) VALUES (:uid)")
                    ->execute([':uid' => Auth::id()]);
                $cart = ['id' => (int) $pdo->lastInsertId(), 'user_id' => Auth::id()];
            }
        } else {
            // Guest cart via session
            $guestToken = $_SESSION['_guest_cart_token'] ?? null;
            if ($guestToken) {
                $hash = hash('sha256', $guestToken);
                $stmt = $pdo->prepare("SELECT * FROM carts WHERE guest_token_hash = :hash LIMIT 1");
                $stmt->execute([':hash' => $hash]);
                $cart = $stmt->fetch();
            }

            if (empty($cart)) {
                $guestToken = bin2hex(random_bytes(32));
                $_SESSION['_guest_cart_token'] = $guestToken;
                $hash = hash('sha256', $guestToken);
                $pdo->prepare("INSERT INTO carts (guest_token_hash) VALUES (:hash)")
                    ->execute([':hash' => $hash]);
                $cart = ['id' => (int) $pdo->lastInsertId(), 'guest_token_hash' => $hash];
            }
        }

        return $cart;
    }

    /**
     * Get cart items with product/variant details.
     */
    public static function getItems(int $cartId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT ci.id, ci.cart_id, ci.variant_id, ci.quantity,
                   pv.sku, pv.size_label, pv.color_name, pv.price, pv.sale_price,
                   pv.stock_quantity, COALESCE(pv.sale_price, pv.price) AS effective_price,
                   p.id AS product_id, p.name AS product_name, p.slug AS product_slug,
                   pi.image_path AS product_image
            FROM cart_items ci
            JOIN product_variants pv ON pv.id = ci.variant_id
            JOIN products p ON p.id = pv.product_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE ci.cart_id = :cart_id
            ORDER BY ci.created_at ASC
        ");
        $stmt->execute([':cart_id' => $cartId]);
        return $stmt->fetchAll();
    }

    /**
     * Add item to cart (or increase quantity if already exists).
     */
    public static function addItem(int $cartId, int $variantId, int $quantity = 1): bool
    {
        $pdo = Database::getConnection();

        // Check variant exists and has stock
        $stmt = $pdo->prepare("SELECT stock_quantity FROM product_variants WHERE id = :vid AND is_active = 1");
        $stmt->execute([':vid' => $variantId]);
        $variant = $stmt->fetch();
        if (!$variant) return false;

        // Check existing cart item
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = :cid AND variant_id = :vid");
        $stmt->execute([':cid' => $cartId, ':vid' => $variantId]);
        $existing = $stmt->fetch();

        $newQty = ($existing ? (int) $existing['quantity'] : 0) + $quantity;
        if ($newQty > (int) $variant['stock_quantity']) {
            $newQty = (int) $variant['stock_quantity'];
        }
        if ($newQty <= 0) return false;

        if ($existing) {
            $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE id = :id")
                ->execute([':qty' => $newQty, ':id' => $existing['id']]);
        } else {
            $pdo->prepare("INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (:cid, :vid, :qty)")
                ->execute([':cid' => $cartId, ':vid' => $variantId, ':qty' => $newQty]);
        }

        // Update cart timestamp
        $pdo->prepare("UPDATE carts SET updated_at = NOW() WHERE id = :cid")
            ->execute([':cid' => $cartId]);

        return true;
    }

    /**
     * Update cart item quantity.
     */
    public static function updateItem(int $itemId, int $cartId, int $quantity): bool
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT ci.variant_id, pv.stock_quantity
            FROM cart_items ci
            JOIN product_variants pv ON pv.id = ci.variant_id
            WHERE ci.id = :id AND ci.cart_id = :cid
        ");
        $stmt->execute([':id' => $itemId, ':cid' => $cartId]);
        $item = $stmt->fetch();
        if (!$item) return false;

        if ($quantity <= 0) {
            return self::removeItem($itemId, $cartId);
        }

        $quantity = min($quantity, (int) $item['stock_quantity']);

        $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE id = :id AND cart_id = :cid")
            ->execute([':qty' => $quantity, ':id' => $itemId, ':cid' => $cartId]);
        return true;
    }

    /**
     * Remove an item from cart.
     */
    public static function removeItem(int $itemId, int $cartId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :id AND cart_id = :cid");
        $stmt->execute([':id' => $itemId, ':cid' => $cartId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Merge guest cart into user cart on login.
     */
    public static function mergeGuestCart(int $userId): void
    {
        $guestToken = $_SESSION['_guest_cart_token'] ?? null;
        if (!$guestToken) return;

        $pdo = Database::getConnection();
        $hash = hash('sha256', $guestToken);

        $stmt = $pdo->prepare("SELECT id FROM carts WHERE guest_token_hash = :hash LIMIT 1");
        $stmt->execute([':hash' => $hash]);
        $guestCart = $stmt->fetch();
        if (!$guestCart) return;

        // Get or create user cart
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $userId]);
        $userCart = $stmt->fetch();
        if (!$userCart) {
            $pdo->prepare("INSERT INTO carts (user_id) VALUES (:uid)")->execute([':uid' => $userId]);
            $userCartId = (int) $pdo->lastInsertId();
        } else {
            $userCartId = (int) $userCart['id'];
        }

        // Merge items
        $guestItems = self::getItems((int) $guestCart['id']);
        foreach ($guestItems as $gi) {
            self::addItem($userCartId, (int) $gi['variant_id'], (int) $gi['quantity']);
        }

        // Delete guest cart
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cid")->execute([':cid' => $guestCart['id']]);
        $pdo->prepare("DELETE FROM carts WHERE id = :cid")->execute([':cid' => $guestCart['id']]);
        unset($_SESSION['_guest_cart_token']);
    }

    /**
     * Calculate totals from cart items.
     */
    public static function calculateTotals(array $items): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (int) $item['effective_price'] * (int) $item['quantity'];
        }
        $shippingFee = (int) (\App\Config\env('SHIPPING_FEE', 30000));
        return [
            'subtotal'     => $subtotal,
            'discount'     => 0,
            'shipping_fee' => count($items) > 0 ? $shippingFee : 0,
            'grand_total'  => $subtotal + (count($items) > 0 ? $shippingFee : 0),
            'item_count'   => count($items),
        ];
    }

    /**
     * Clear all items from a cart.
     */
    public static function clear(int $cartId): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cid")->execute([':cid' => $cartId]);
    }
}

