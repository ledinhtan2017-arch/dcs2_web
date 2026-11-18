<?php
/**
 * Order Model
 */

namespace App\Models;

use App\Core\Database;

class Order
{
    /**
     * Generate unique order code: SS-YYYYMMDD-XXXXX
     */
    public static function generateCode(): string
    {
        $date = date('Ymd');
        $rand = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
        return "SS-{$date}-{$rand}";
    }

    /**
     * Create an order with items (within transaction).
     */
    public static function create(array $orderData, array $items): ?array
    {
        $pdo = Database::getConnection();
        $paymentMethod = $orderData['payment_method'] ?? 'cod';
        if (!in_array($paymentMethod, ['cod', 'bank_transfer'], true)
            || ($paymentMethod === 'bank_transfer' && !BankTransfer::available())) return null;

        // A zero or duplicate quantity must never be able to increase stock
        // or bypass the stock check during checkout.
        $variantIds = [];
        if (!$items) return null;
        foreach ($items as $item) {
            $variantId = (int) ($item['variant_id'] ?? 0);
            if ($variantId <= 0 || (int) ($item['quantity'] ?? 0) <= 0 || isset($variantIds[$variantId])) return null;
            $variantIds[$variantId] = true;
        }

        try {
            $pdo->beginTransaction();

            $orderCode = self::generateCode();

            // Check idempotency
            if (!empty($orderData['idempotency_key'])) {
                $stmt = $pdo->prepare("SELECT id, order_code FROM orders WHERE idempotency_key = :key LIMIT 1");
                $stmt->execute([':key' => $orderData['idempotency_key']]);
                $existing = $stmt->fetch();
                if ($existing) {
                    $pdo->rollBack();
                    return $existing; // Return existing order
                }
            }

            // Calculate totals server-side
            $subtotal = 0;
            foreach ($items as &$item) {
                // Re-query variant price and stock with row lock
                $stmt = $pdo->prepare("
                    SELECT pv.id, pv.sku, pv.price, pv.sale_price, pv.stock_quantity, pv.size_label, pv.color_name,
                           p.name AS product_name
                    FROM product_variants pv
                    JOIN products p ON p.id = pv.product_id
                    WHERE pv.id = :vid AND pv.is_active = 1
                    FOR UPDATE
                ");
                $stmt->execute([':vid' => $item['variant_id']]);
                $variant = $stmt->fetch();

                if (!$variant) {
                    $pdo->rollBack();
                    return null; // Variant not found
                }

                if ((int) $variant['stock_quantity'] < (int) $item['quantity']) {
                    $pdo->rollBack();
                    return null; // Insufficient stock
                }

                $unitPrice = (int) ($variant['sale_price'] ?? $variant['price']);
                $lineTotal = $unitPrice * (int) $item['quantity'];
                $subtotal += $lineTotal;

                $item['unit_price'] = $unitPrice;
                $item['line_total'] = $lineTotal;
                $item['sku'] = $variant['sku'];
                $item['product_name'] = $variant['product_name'];
                $item['options'] = trim(($variant['size_label'] ?? '') . ' ' . ($variant['color_name'] ?? ''));
                $item['stock'] = (int) $variant['stock_quantity'];
            }
            unset($item);

            $shippingFee = (int) (\App\Config\env('SHIPPING_FEE', 30000));
            $grandTotal = $subtotal + $shippingFee;

            // Insert order
            $stmt = $pdo->prepare("
                INSERT INTO orders (order_code, user_id, customer_name, email, phone,
                    shipping_address_json, subtotal, discount_total, shipping_fee, grand_total,
                    payment_method, payment_status, order_status, idempotency_key, note)
                VALUES (:code, :uid, :name, :email, :phone,
                    :addr, :subtotal, :discount, :ship, :total,
                    :payment_method, 'unpaid', 'pending', :ikey, :note)
            ");
            $stmt->execute([
                ':code'     => $orderCode,
                ':uid'      => $orderData['user_id'] ?? null,
                ':name'     => $orderData['customer_name'],
                ':email'    => $orderData['email'],
                ':phone'    => $orderData['phone'],
                ':addr'     => json_encode($orderData['address'], JSON_UNESCAPED_UNICODE),
                ':subtotal' => $subtotal,
                ':discount' => 0,
                ':ship'     => $shippingFee,
                ':total'    => $grandTotal,
                ':payment_method' => $paymentMethod,
                ':ikey'     => $orderData['idempotency_key'] ?? null,
                ':note'     => $orderData['note'] ?? null,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            // Insert order items and deduct stock
            foreach ($items as $item) {
                $pdo->prepare("
                    INSERT INTO order_items (order_id, variant_id, sku_snapshot, name_snapshot, options_snapshot, unit_price, quantity, line_total)
                    VALUES (:oid, :vid, :sku, :name, :opts, :price, :qty, :total)
                ")->execute([
                    ':oid'   => $orderId,
                    ':vid'   => $item['variant_id'],
                    ':sku'   => $item['sku'],
                    ':name'  => $item['product_name'],
                    ':opts'  => $item['options'],
                    ':price' => $item['unit_price'],
                    ':qty'   => $item['quantity'],
                    ':total' => $item['line_total'],
                ]);

                // Deduct stock
                $pdo->prepare("
                    UPDATE product_variants SET stock_quantity = stock_quantity - :qty WHERE id = :vid
                ")->execute([':qty' => $item['quantity'], ':vid' => $item['variant_id']]);
            }

            // Insert status history
            $pdo->prepare("
                INSERT INTO order_status_history (order_id, from_status, to_status, actor_user_id, note)
                VALUES (:oid, NULL, 'pending', :uid, 'Đơn hàng mới được tạo')
            ")->execute([':oid' => $orderId, ':uid' => $orderData['user_id'] ?? null]);

            // Insert payment record
            $pdo->prepare("
                INSERT INTO payments (order_id, provider, amount, status)
                VALUES (:oid, :provider, :amount, 'pending')
            ")->execute([':oid' => $orderId, ':provider' => $paymentMethod, ':amount' => $grandTotal]);

            $pdo->commit();

            return ['id' => $orderId, 'order_code' => $orderCode, 'grand_total' => $grandTotal];

        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function findByCode(string $code): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = :code LIMIT 1");
        $stmt->execute([':code' => $code]);
        $order = $stmt->fetch();
        if (!$order) return null;

        // Get items
        $stmt = $pdo->prepare("SELECT oi.*,
            (SELECT pi.image_path FROM product_variants pv
             JOIN product_images pi ON pi.product_id = pv.product_id AND pi.is_primary = 1
             WHERE pv.id = oi.variant_id ORDER BY pi.id LIMIT 1) AS product_image
            FROM order_items oi WHERE oi.order_id = :oid ORDER BY oi.id");
        $stmt->execute([':oid' => $order['id']]);
        $order['items'] = $stmt->fetchAll();

        // Get status history
        $stmt = $pdo->prepare("
            SELECT osh.*, u.name AS actor_name
            FROM order_status_history osh
            LEFT JOIN users u ON u.id = osh.actor_user_id
            WHERE osh.order_id = :oid ORDER BY osh.created_at ASC
        ");
        $stmt->execute([':oid' => $order['id']]);
        $order['status_history'] = $stmt->fetchAll();

        return $order;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function getUserOrders(int $userId, int $page = 1, int $perPage = 10): array
    {
        $pdo = Database::getConnection();
        $offset = ($page - 1) * $perPage;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid");
        $countStmt->execute([':uid' => $userId]);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT * FROM orders WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'orders' => $stmt->fetchAll(),
            'total'  => $total,
            'page'   => $page,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Update order status with validation.
     */
    public static function updateStatus(int $orderId, string $newStatus, ?int $actorId = null, ?string $note = null): bool
    {
        return self::changeStatus($orderId, $newStatus, $actorId, $note, false);
    }

    /** Customers may cancel only their own order while it is still pending. */
    public static function cancelByCustomer(string $code, int $userId): bool
    {
        if ($userId <= 0) return false;
        return self::changeStatus($code, 'cancelled', $userId, 'Khách hàng hủy đơn', true);
    }

    /** The caller must first verify the guest's order code in their session. */
    public static function cancelByGuest(string $code): bool
    {
        return self::changeStatus($code, 'cancelled', null, 'Khách hàng hủy đơn', true);
    }

    private static function changeStatus(int|string $orderKey, string $newStatus, ?int $actorId, ?string $note, bool $customerCancellation): bool
    {
        $pdo = Database::getConnection();

        // Valid transitions
        $validTransitions = [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['shipping', 'cancelled'],
            'shipping'  => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        try {
            $pdo->beginTransaction();

            // Serialize state changes so two cancellation requests cannot both
            // restore inventory for the same order.
            $stmt = $pdo->prepare(is_int($orderKey)
                ? "SELECT id, user_id, order_status, payment_method, payment_status FROM orders WHERE id = :key FOR UPDATE"
                : "SELECT id, user_id, order_status, payment_method, payment_status FROM orders WHERE order_code = :key FOR UPDATE");
            $stmt->execute([':key' => $orderKey]);
            $order = $stmt->fetch();
            $currentStatus = $order['order_status'] ?? null;
            $ownerMatches = !$customerCancellation || ($actorId === null
                ? ($order && $order['user_id'] === null)
                : ($order && $order['user_id'] !== null && (int) $order['user_id'] === $actorId));
            if (!$currentStatus || !$ownerMatches || ($customerCancellation && $currentStatus !== 'pending')
                || ($newStatus === 'cancelled' && $order['payment_method'] === 'bank_transfer' && $order['payment_status'] === 'paid')
                || ($newStatus === 'shipping' && $order['payment_method'] === 'bank_transfer' && $order['payment_status'] !== 'paid')
                || !in_array($newStatus, $validTransitions[$currentStatus] ?? [], true)) {
                $pdo->rollBack();
                return false;
            }
            $orderId = (int) $order['id'];

            $pdo->prepare("UPDATE orders SET order_status = :status, updated_at = NOW() WHERE id = :id")
                ->execute([':status' => $newStatus, ':id' => $orderId]);

            // If cancelled, restore stock (once)
            if ($newStatus === 'cancelled') {
                $items = $pdo->prepare("SELECT variant_id, quantity FROM order_items WHERE order_id = :oid");
                $items->execute([':oid' => $orderId]);
                foreach ($items->fetchAll() as $item) {
                    if ($item['variant_id']) {
                        $pdo->prepare("UPDATE product_variants SET stock_quantity = stock_quantity + :qty WHERE id = :vid")
                            ->execute([':qty' => $item['quantity'], ':vid' => $item['variant_id']]);
                    }
                }
            }

            // If delivered, mark payment as completed for COD
            if ($newStatus === 'delivered' && $order['payment_method'] === 'cod') {
                $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = :id")
                    ->execute([':id' => $orderId]);
                $pdo->prepare("UPDATE payments SET status = 'completed' WHERE order_id = :oid")
                    ->execute([':oid' => $orderId]);
            }

            // Record history
            $pdo->prepare("
                INSERT INTO order_status_history (order_id, from_status, to_status, actor_user_id, note)
                VALUES (:oid, :from, :to, :uid, :note)
            ")->execute([
                ':oid'  => $orderId,
                ':from' => $currentStatus,
                ':to'   => $newStatus,
                ':uid'  => $actorId,
                ':note' => $note,
            ]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    /** Confirm a bank transfer only after checking the bank transaction. */
    public static function confirmBankPayment(int $orderId): bool
    {
        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT payment_method, payment_status, order_status, grand_total FROM orders WHERE id = ? FOR UPDATE');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            if (!$order || $order['payment_method'] !== 'bank_transfer' || $order['payment_status'] !== 'unpaid' || $order['order_status'] === 'cancelled') {
                $pdo->rollBack();
                return false;
            }
            $payment = $pdo->prepare("SELECT id, amount FROM payments WHERE order_id = ? AND provider = 'bank_transfer' AND status = 'pending' FOR UPDATE");
            $payment->execute([$orderId]);
            $record = $payment->fetch();
            if (!$record || (int)$record['amount'] !== (int)$order['grand_total']) {
                $pdo->rollBack();
                return false;
            }
            $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?")->execute([$orderId]);
            $pdo->prepare("UPDATE payments SET status = 'completed' WHERE id = ?")->execute([$record['id']]);
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}

