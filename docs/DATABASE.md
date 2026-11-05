# Database Documentation — Sport Shop

> Thiết kế cơ sở dữ liệu MySQL 8+ cho hệ thống e-commerce Sport Shop.

## Quy ước chung

| Quy ước | Giá trị |
|---------|---------|
| Engine | InnoDB |
| Charset | `utf8mb4` |
| Collation | `utf8mb4_unicode_ci` |
| Tiền tệ VND | `DECIMAL(12,0)` — không bao giờ dùng FLOAT/DOUBLE |
| Timestamp | `DATETIME` lưu UTC, hiển thị `Asia/Ho_Chi_Minh` |
| Soft delete | Dùng cột `status` hoặc `is_active` thay vì xóa cứng |
| Naming | snake_case, số ít cho tên bảng (ngoại trừ bảng quan hệ N:N) |
| Primary key | `id INT UNSIGNED AUTO_INCREMENT` |
| Foreign key | Đặt tên `fk_{table}_{column}`, ON DELETE RESTRICT mặc định |

---

## ERD — Mô tả quan hệ

```
┌──────────┐       ┌───────────────┐       ┌──────────┐
│  users   │1────N│   addresses   │       │  sports  │
└────┬─────┘       └───────────────┘       └────┬─────┘
     │                                          │
     │1                                         │1
     │                                          │
     ▼N                                         ▼N
┌──────────┐                              ┌────────────┐
│  orders  │                              │ categories │
└────┬─────┘                              └────┬───────┘
     │1                                        │1
     │                                         │
     ├───N──►┌─────────────────────┐           ▼N
     │       │   order_items       │     ┌───────────┐      ┌────────┐
     │       └─────────────────────┘     │ products  │N───1│ brands │
     │                                   └─────┬─────┘      └────────┘
     ├───N──►┌─────────────────────┐           │
     │       │ order_status_history│           │1
     │       └─────────────────────┘           │
     │                                         ├───N──►┌──────────────────┐
     └───N──►┌─────────────────────┐           │       │ product_variants │
             │     payments        │           │       └──────────────────┘
             └─────────────────────┘           │
                                               ├───N──►┌──────────────────┐
                                               │       │ product_images   │
┌──────────┐                                   │       └──────────────────┘
│  carts   │1────N┌─────────────┐             │
└──────────┘      │ cart_items  │──────N───1──┘
                  └─────────────┘
                                          ┌──────────────────┐
                                          │  product_sports  │
                                          │ (product ↔ sport)│
                                          └──────────────────┘

Bảng P1 (sau MVP):
  wishlists, reviews, coupons, banners
```

---

## Bảng chi tiết

### 1. `users`

Quản lý tài khoản người dùng (customer và admin).

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `name` | VARCHAR(100) | NOT NULL | Họ tên |
| `email` | VARCHAR(255) | NOT NULL, UNIQUE | Email đăng nhập |
| `password_hash` | VARCHAR(255) | NOT NULL | bcrypt hash |
| `phone` | VARCHAR(20) | NULL | Số điện thoại |
| `role` | ENUM('customer','admin') | NOT NULL, DEFAULT 'customer' | Phân quyền |
| `status` | ENUM('active','locked') | NOT NULL, DEFAULT 'active' | Trạng thái tài khoản |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Ngày tạo |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | Ngày cập nhật |

**Indexes:** `UNIQUE(email)`

---

### 2. `addresses`

Địa chỉ giao hàng của user (theo format Việt Nam).

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `user_id` | INT UNSIGNED | FK → users.id, NOT NULL | Chủ sở hữu |
| `receiver_name` | VARCHAR(100) | NOT NULL | Tên người nhận |
| `phone` | VARCHAR(20) | NOT NULL | SĐT người nhận |
| `province` | VARCHAR(100) | NOT NULL | Tỉnh/Thành phố |
| `district` | VARCHAR(100) | NOT NULL | Quận/Huyện |
| `ward` | VARCHAR(100) | NOT NULL | Phường/Xã |
| `street` | VARCHAR(255) | NOT NULL | Địa chỉ chi tiết |
| `is_default` | TINYINT(1) | NOT NULL, DEFAULT 0 | Địa chỉ mặc định |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(user_id)` \
**FK:** `user_id → users(id) ON DELETE CASCADE`

---

### 3. `sports`

Các môn thể thao (Cầu lông, Bóng đá, Bóng rổ, Chạy bộ/Fitness).

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `name` | VARCHAR(100) | NOT NULL | Tên môn |
| `slug` | VARCHAR(120) | NOT NULL, UNIQUE | URL slug |
| `description` | TEXT | NULL | Mô tả |
| `image_path` | VARCHAR(500) | NULL | Ảnh đại diện |
| `sort_order` | INT | NOT NULL, DEFAULT 0 | Thứ tự hiển thị |
| `is_active` | TINYINT(1) | NOT NULL, DEFAULT 1 | Kích hoạt |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(slug)`

---

### 4. `categories`

Danh mục sản phẩm phân cấp theo môn thể thao.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `sport_id` | INT UNSIGNED | FK → sports.id, NOT NULL | Môn thể thao chính |
| `parent_id` | INT UNSIGNED | FK → categories.id, NULL | Danh mục cha (NULL = root) |
| `name` | VARCHAR(100) | NOT NULL | Tên danh mục |
| `slug` | VARCHAR(120) | NOT NULL, UNIQUE | URL slug |
| `description` | TEXT | NULL | Mô tả |
| `image_path` | VARCHAR(500) | NULL | Ảnh minh họa |
| `sort_order` | INT | NOT NULL, DEFAULT 0 | Thứ tự |
| `is_active` | TINYINT(1) | NOT NULL, DEFAULT 1 | Kích hoạt |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(slug)`, `INDEX(sport_id)`, `INDEX(parent_id)` \
**FK:** `sport_id → sports(id) ON DELETE RESTRICT`, `parent_id → categories(id) ON DELETE SET NULL`

---

### 5. `brands`

Thương hiệu sản phẩm.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `name` | VARCHAR(100) | NOT NULL | Tên thương hiệu |
| `slug` | VARCHAR(120) | NOT NULL, UNIQUE | URL slug |
| `logo_path` | VARCHAR(500) | NULL | Logo thương hiệu |
| `description` | TEXT | NULL | Mô tả |
| `is_active` | TINYINT(1) | NOT NULL, DEFAULT 1 | Kích hoạt |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(slug)`

---

### 6. `products`

Sản phẩm chính (không chứa biến thể size/color).

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `category_id` | INT UNSIGNED | FK → categories.id, NOT NULL | Danh mục chính |
| `brand_id` | INT UNSIGNED | FK → brands.id, NULL | Thương hiệu |
| `name` | VARCHAR(255) | NOT NULL | Tên sản phẩm |
| `slug` | VARCHAR(280) | NOT NULL, UNIQUE | URL slug |
| `description` | TEXT | NULL | Mô tả chi tiết |
| `short_description` | VARCHAR(500) | NULL | Mô tả ngắn |
| `status` | ENUM('active','inactive','draft') | NOT NULL, DEFAULT 'draft' | Trạng thái |
| `is_featured` | TINYINT(1) | NOT NULL, DEFAULT 0 | Sản phẩm nổi bật |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(slug)`, `INDEX(category_id)`, `INDEX(brand_id)`, `INDEX(status)`, `FULLTEXT(name, description)` \
**FK:** `category_id → categories(id) ON DELETE RESTRICT`, `brand_id → brands(id) ON DELETE SET NULL`

---

### 7. `product_sports`

Bảng quan hệ N:N — sản phẩm có thể thuộc nhiều môn thể thao.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `product_id` | INT UNSIGNED | FK → products.id, NOT NULL | Sản phẩm |
| `sport_id` | INT UNSIGNED | FK → sports.id, NOT NULL | Môn thể thao |

**Indexes:** `PRIMARY KEY(product_id, sport_id)` \
**FK:** `product_id → products(id) ON DELETE CASCADE`, `sport_id → sports(id) ON DELETE CASCADE`

---

### 8. `product_variants`

Biến thể sản phẩm (size, màu) — mỗi variant có SKU, giá, tồn kho riêng.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `product_id` | INT UNSIGNED | FK → products.id, NOT NULL | Sản phẩm cha |
| `sku` | VARCHAR(50) | NOT NULL, UNIQUE | Mã SKU duy nhất |
| `size_label` | VARCHAR(20) | NULL | Nhãn size (S, M, L, 40, 41...) |
| `color_name` | VARCHAR(50) | NULL | Tên màu |
| `color_code` | VARCHAR(7) | NULL | Mã màu hex (#FF0000) |
| `attributes_json` | JSON | NULL | Thuộc tính mở rộng (weight, grip...) |
| `price` | DECIMAL(12,0) | NOT NULL | Giá gốc (VND) |
| `sale_price` | DECIMAL(12,0) | NULL | Giá khuyến mãi (NULL = không KM) |
| `stock_quantity` | INT UNSIGNED | NOT NULL, DEFAULT 0 | Tồn kho, CHECK >= 0 |
| `is_active` | TINYINT(1) | NOT NULL, DEFAULT 1 | Kích hoạt |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(sku)`, `INDEX(product_id)`, `INDEX(product_id, is_active)` \
**FK:** `product_id → products(id) ON DELETE CASCADE` \
**Check:** `sale_price IS NULL OR (sale_price >= 0 AND sale_price < price)`

---

### 9. `product_images`

Ảnh sản phẩm — nhiều ảnh/sản phẩm, có thể gắn theo variant.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `product_id` | INT UNSIGNED | FK → products.id, NOT NULL | Sản phẩm |
| `variant_id` | INT UNSIGNED | FK → product_variants.id, NULL | Variant (NULL = chung) |
| `image_path` | VARCHAR(500) | NOT NULL | Đường dẫn file |
| `alt_text` | VARCHAR(255) | NULL | Mô tả alt cho ảnh |
| `sort_order` | INT | NOT NULL, DEFAULT 0 | Thứ tự hiển thị |
| `is_primary` | TINYINT(1) | NOT NULL, DEFAULT 0 | Ảnh chính |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(product_id)`, `INDEX(variant_id)`, `INDEX(product_id, sort_order)` \
**FK:** `product_id → products(id) ON DELETE CASCADE`, `variant_id → product_variants(id) ON DELETE SET NULL`

---

### 10. `carts`

Giỏ hàng — hỗ trợ cả guest (token) và logged-in user.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `user_id` | INT UNSIGNED | FK → users.id, NULL | User (NULL = guest) |
| `guest_token_hash` | VARCHAR(64) | NULL | SHA-256 hash của guest token |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(user_id)`, `INDEX(guest_token_hash)` \
**FK:** `user_id → users(id) ON DELETE CASCADE`

> **Ghi chú:** `guest_token_hash` lưu hash, không lưu plaintext. Token thực gửi qua HttpOnly cookie.

---

### 11. `cart_items`

Mặt hàng trong giỏ hàng.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `cart_id` | INT UNSIGNED | FK → carts.id, NOT NULL | Giỏ hàng |
| `variant_id` | INT UNSIGNED | FK → product_variants.id, NOT NULL | Biến thể sản phẩm |
| `quantity` | INT UNSIGNED | NOT NULL, CHECK > 0 | Số lượng |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(cart_id, variant_id)`, `INDEX(variant_id)` \
**FK:** `cart_id → carts(id) ON DELETE CASCADE`, `variant_id → product_variants(id) ON DELETE CASCADE`

---

### 12. `orders`

Đơn hàng — snapshot thông tin tại thời điểm đặt hàng.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `order_code` | VARCHAR(20) | NOT NULL, UNIQUE | Mã đơn (SS-YYYYMMDD-XXXXX) |
| `user_id` | INT UNSIGNED | FK → users.id, NULL | Customer (NULL = guest) |
| `customer_name` | VARCHAR(100) | NOT NULL | Tên người nhận (snapshot) |
| `email` | VARCHAR(255) | NULL | Email |
| `phone` | VARCHAR(20) | NOT NULL | SĐT |
| `shipping_address_json` | JSON | NOT NULL | Địa chỉ giao (snapshot dạng JSON) |
| `subtotal` | DECIMAL(12,0) | NOT NULL | Tổng tiền hàng |
| `discount_total` | DECIMAL(12,0) | NOT NULL, DEFAULT 0 | Tổng giảm giá |
| `shipping_fee` | DECIMAL(12,0) | NOT NULL | Phí vận chuyển |
| `grand_total` | DECIMAL(12,0) | NOT NULL | Tổng thanh toán |
| `payment_method` | ENUM('cod') | NOT NULL, DEFAULT 'cod' | Phương thức thanh toán |
| `payment_status` | ENUM('unpaid','paid','refunded') | NOT NULL, DEFAULT 'unpaid' | Trạng thái thanh toán |
| `order_status` | ENUM('pending','confirmed','shipping','delivered','cancelled') | NOT NULL, DEFAULT 'pending' | Trạng thái đơn |
| `idempotency_key` | VARCHAR(64) | NULL, UNIQUE | Chống double-submit |
| `guest_token_hash` | VARCHAR(64) | NULL | Hash token để guest xem đơn |
| `stock_restored` | TINYINT(1) | NOT NULL, DEFAULT 0 | Đã hoàn kho khi cancel chưa |
| `note` | TEXT | NULL | Ghi chú của khách |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(order_code)`, `UNIQUE(idempotency_key)`, `INDEX(user_id)`, `INDEX(order_status)`, `INDEX(created_at)`, `INDEX(guest_token_hash)` \
**FK:** `user_id → users(id) ON DELETE SET NULL`

---

### 13. `order_items`

Chi tiết đơn hàng — snapshot tại thời điểm mua, không thay đổi khi admin sửa sản phẩm.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `order_id` | INT UNSIGNED | FK → orders.id, NOT NULL | Đơn hàng |
| `variant_id` | INT UNSIGNED | NULL | FK → product_variants.id (giữ ref) |
| `sku_snapshot` | VARCHAR(50) | NOT NULL | SKU tại thời điểm mua |
| `name_snapshot` | VARCHAR(255) | NOT NULL | Tên sản phẩm snapshot |
| `options_snapshot` | JSON | NULL | Size/Màu/thuộc tính snapshot |
| `image_snapshot` | VARCHAR(500) | NULL | Ảnh snapshot |
| `unit_price` | DECIMAL(12,0) | NOT NULL | Đơn giá (VND) |
| `quantity` | INT UNSIGNED | NOT NULL | Số lượng |
| `line_total` | DECIMAL(12,0) | NOT NULL | Thành tiền = unit_price × quantity |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(order_id)`, `INDEX(variant_id)` \
**FK:** `order_id → orders(id) ON DELETE CASCADE`, `variant_id → product_variants(id) ON DELETE SET NULL`

---

### 14. `order_status_history`

Lịch sử thay đổi trạng thái đơn hàng (audit trail).

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `order_id` | INT UNSIGNED | FK → orders.id, NOT NULL | Đơn hàng |
| `from_status` | VARCHAR(20) | NULL | Trạng thái trước (NULL = khởi tạo) |
| `to_status` | VARCHAR(20) | NOT NULL | Trạng thái mới |
| `actor_user_id` | INT UNSIGNED | FK → users.id, NULL | Người thực hiện (NULL = system) |
| `note` | TEXT | NULL | Ghi chú |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(order_id)`, `INDEX(actor_user_id)` \
**FK:** `order_id → orders(id) ON DELETE CASCADE`, `actor_user_id → users(id) ON DELETE SET NULL`

---

### 15. `payments`

Bản ghi thanh toán — COD trước; cổng thanh toán P2.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `order_id` | INT UNSIGNED | FK → orders.id, NOT NULL | Đơn hàng |
| `provider` | VARCHAR(50) | NOT NULL, DEFAULT 'cod' | Nhà cung cấp (cod, vnpay...) |
| `provider_reference` | VARCHAR(255) | NULL | Mã giao dịch từ provider |
| `amount` | DECIMAL(12,0) | NOT NULL | Số tiền (VND) |
| `status` | ENUM('pending','completed','failed','refunded') | NOT NULL, DEFAULT 'pending' | Trạng thái |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(order_id)`, `INDEX(provider_reference)` \
**FK:** `order_id → orders(id) ON DELETE CASCADE`

---

### 16. `wishlists` (P1)

Danh sách yêu thích.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `user_id` | INT UNSIGNED | FK → users.id, NOT NULL | User |
| `product_id` | INT UNSIGNED | FK → products.id, NOT NULL | Sản phẩm |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `PRIMARY KEY(user_id, product_id)` \
**FK:** `user_id → users(id) ON DELETE CASCADE`, `product_id → products(id) ON DELETE CASCADE`

---

### 17. `reviews` (P1)

Đánh giá sản phẩm (chỉ cho đơn đã mua).

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `user_id` | INT UNSIGNED | FK → users.id, NOT NULL | Người đánh giá |
| `product_id` | INT UNSIGNED | FK → products.id, NOT NULL | Sản phẩm |
| `order_item_id` | INT UNSIGNED | FK → order_items.id, NOT NULL | Item đã mua (1 review/item) |
| `rating` | TINYINT UNSIGNED | NOT NULL, CHECK 1-5 | Số sao (1-5) |
| `title` | VARCHAR(200) | NULL | Tiêu đề |
| `body` | TEXT | NULL | Nội dung |
| `status` | ENUM('pending','approved','rejected') | NOT NULL, DEFAULT 'pending' | Duyệt |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(order_item_id)`, `INDEX(product_id)`, `INDEX(user_id)` \
**FK:** `user_id → users(id) ON DELETE CASCADE`, `product_id → products(id) ON DELETE CASCADE`, `order_item_id → order_items(id) ON DELETE CASCADE`

---

### 18. `coupons` (P1)

Mã giảm giá.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `code` | VARCHAR(50) | NOT NULL, UNIQUE | Mã coupon |
| `description` | VARCHAR(255) | NULL | Mô tả |
| `discount_type` | ENUM('percentage','fixed') | NOT NULL | Loại giảm giá |
| `discount_value` | DECIMAL(12,0) | NOT NULL | Giá trị (% hoặc VND) |
| `min_order_value` | DECIMAL(12,0) | NULL | Đơn tối thiểu |
| `max_discount` | DECIMAL(12,0) | NULL | Giảm tối đa (cho %) |
| `starts_at` | DATETIME | NULL | Bắt đầu hiệu lực |
| `ends_at` | DATETIME | NULL | Hết hiệu lực |
| `usage_limit` | INT UNSIGNED | NULL | Giới hạn lần dùng |
| `used_count` | INT UNSIGNED | NOT NULL, DEFAULT 0 | Đã dùng |
| `is_active` | TINYINT(1) | NOT NULL, DEFAULT 1 | Kích hoạt |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `UNIQUE(code)`

---

### 19. `banners` (P1)

Quản trị banner hiển thị trên trang chủ/trang môn.

| Cột | Kiểu | Ràng buộc | Mô tả |
|-----|------|-----------|-------|
| `id` | INT UNSIGNED | PK, AUTO_INCREMENT | ID |
| `title` | VARCHAR(200) | NOT NULL | Tiêu đề |
| `subtitle` | VARCHAR(500) | NULL | Phụ đề |
| `image_path` | VARCHAR(500) | NOT NULL | Ảnh banner |
| `image_mobile_path` | VARCHAR(500) | NULL | Ảnh mobile (4:5) |
| `link_url` | VARCHAR(500) | NULL | Link khi click |
| `position` | ENUM('home_hero','home_sub','sport_hero') | NOT NULL, DEFAULT 'home_hero' | Vị trí |
| `sort_order` | INT | NOT NULL, DEFAULT 0 | Thứ tự |
| `is_active` | TINYINT(1) | NOT NULL, DEFAULT 1 | Kích hoạt |
| `starts_at` | DATETIME | NULL | Hiển thị từ |
| `ends_at` | DATETIME | NULL | Hiển thị đến |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Indexes:** `INDEX(position, is_active, sort_order)`

---

## Sơ đồ quan hệ (Foreign Keys)

| Bảng nguồn | Cột | → Bảng đích | Cột | ON DELETE |
|------------|-----|-------------|-----|-----------|
| addresses | user_id | users | id | CASCADE |
| categories | sport_id | sports | id | RESTRICT |
| categories | parent_id | categories | id | SET NULL |
| products | category_id | categories | id | RESTRICT |
| products | brand_id | brands | id | SET NULL |
| product_sports | product_id | products | id | CASCADE |
| product_sports | sport_id | sports | id | CASCADE |
| product_variants | product_id | products | id | CASCADE |
| product_images | product_id | products | id | CASCADE |
| product_images | variant_id | product_variants | id | SET NULL |
| carts | user_id | users | id | CASCADE |
| cart_items | cart_id | carts | id | CASCADE |
| cart_items | variant_id | product_variants | id | CASCADE |
| orders | user_id | users | id | SET NULL |
| order_items | order_id | orders | id | CASCADE |
| order_items | variant_id | product_variants | id | SET NULL |
| order_status_history | order_id | orders | id | CASCADE |
| order_status_history | actor_user_id | users | id | SET NULL |
| payments | order_id | orders | id | CASCADE |
| wishlists | user_id | users | id | CASCADE |
| wishlists | product_id | products | id | CASCADE |
| reviews | user_id | users | id | CASCADE |
| reviews | product_id | products | id | CASCADE |
| reviews | order_item_id | order_items | id | CASCADE |

---

## Quy tắc quan trọng

### Tiền tệ VND
- Tất cả cột tiền tệ dùng `DECIMAL(12,0)` — không bao giờ FLOAT/DOUBLE
- VND không có đơn vị xu nên không cần decimal places
- DECIMAL(12,0) hỗ trợ tới 999,999,999,999₫ (đủ cho mọi đơn hàng)
- Hiển thị dạng: `1.250.000 ₫` (dấu chấm ngăn hàng nghìn, ký hiệu ₫ cuối)

### Timestamps
- Tất cả `created_at` / `updated_at` lưu UTC
- Hiển thị phía client chuyển sang `Asia/Ho_Chi_Minh` (UTC+7)
- `DEFAULT CURRENT_TIMESTAMP` của MySQL trả UTC nếu server timezone = UTC

### Soft Deletes
- Sản phẩm: dùng `status = 'inactive'` thay vì DELETE
- User: dùng `status = 'locked'` thay vì DELETE
- Variant/Category/Sport: dùng `is_active = 0`
- Không hard-delete dữ liệu đã được order/cart tham chiếu
- Order snapshot (order_items) giữ nguyên vĩnh viễn

### Stock Management
- `product_variants.stock_quantity` >= 0 (CHECK constraint)
- Trừ kho trong transaction khi tạo order
- `SELECT ... FOR UPDATE` để lock variant rows khi checkout
- Hoàn kho khi cancel: chỉ 1 lần, kiểm tra `orders.stock_restored`

---

## Query ví dụ

### Lấy sản phẩm với giá min/max từ variants

```sql
SELECT 
    p.id, p.name, p.slug, b.name AS brand_name,
    MIN(COALESCE(pv.sale_price, pv.price)) AS min_price,
    MAX(pv.price) AS max_price,
    SUM(pv.stock_quantity) AS total_stock,
    pi.image_path AS primary_image
FROM products p
LEFT JOIN brands b ON p.brand_id = b.id
INNER JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.status = 'active'
GROUP BY p.id
ORDER BY p.created_at DESC
LIMIT 12 OFFSET 0;
```

### Lấy chi tiết sản phẩm + variants

```sql
-- Thông tin sản phẩm
SELECT p.*, b.name AS brand_name, c.name AS category_name, s.name AS sport_name
FROM products p
LEFT JOIN brands b ON p.brand_id = b.id
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN sports s ON c.sport_id = s.id
WHERE p.slug = :slug AND p.status = 'active';

-- Variants
SELECT pv.*
FROM product_variants pv
WHERE pv.product_id = :product_id AND pv.is_active = 1
ORDER BY pv.size_label, pv.color_name;

-- Images
SELECT pi.*
FROM product_images pi
WHERE pi.product_id = :product_id
ORDER BY pi.sort_order ASC;
```

### Tìm kiếm sản phẩm

```sql
SELECT p.id, p.name, p.slug,
    MIN(COALESCE(pv.sale_price, pv.price)) AS price,
    pi.image_path
FROM products p
INNER JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.status = 'active'
  AND MATCH(p.name, p.description) AGAINST(:keyword IN BOOLEAN MODE)
GROUP BY p.id
ORDER BY MATCH(p.name, p.description) AGAINST(:keyword IN BOOLEAN MODE) DESC
LIMIT :limit OFFSET :offset;
```

### Tạo đơn hàng (transaction)

```sql
START TRANSACTION;

-- 1. Lock variants, kiểm tra stock
SELECT id, stock_quantity, price, sale_price
FROM product_variants
WHERE id IN (:variant_ids)
FOR UPDATE;

-- 2. Validate stock >= requested quantity (trong PHP)

-- 3. Insert order
INSERT INTO orders (order_code, user_id, customer_name, phone, ..., idempotency_key)
VALUES (:code, :user_id, :name, :phone, ..., :idempotency_key);

-- 4. Insert order items (snapshot data)
INSERT INTO order_items (order_id, variant_id, sku_snapshot, name_snapshot, ...)
VALUES (:order_id, :variant_id, :sku, :name, ...);

-- 5. Trừ kho
UPDATE product_variants
SET stock_quantity = stock_quantity - :quantity
WHERE id = :variant_id AND stock_quantity >= :quantity;

-- 6. Insert payment record
INSERT INTO payments (order_id, provider, amount, status)
VALUES (:order_id, 'cod', :amount, 'pending');

-- 7. Insert status history
INSERT INTO order_status_history (order_id, from_status, to_status, note)
VALUES (:order_id, NULL, 'pending', 'Đơn hàng mới');

COMMIT;
```

### Hoàn kho khi hủy đơn

```sql
START TRANSACTION;

-- Kiểm tra điều kiện hủy
SELECT id, order_status, stock_restored
FROM orders WHERE id = :order_id FOR UPDATE;

-- Chỉ hủy nếu pending/confirmed VÀ chưa hoàn kho
-- (kiểm tra trong PHP)

-- Hoàn kho cho từng item
UPDATE product_variants pv
INNER JOIN order_items oi ON oi.variant_id = pv.id
SET pv.stock_quantity = pv.stock_quantity + oi.quantity
WHERE oi.order_id = :order_id;

-- Cập nhật trạng thái
UPDATE orders
SET order_status = 'cancelled', stock_restored = 1
WHERE id = :order_id;

-- Ghi history
INSERT INTO order_status_history (order_id, from_status, to_status, actor_user_id, note)
VALUES (:order_id, :old_status, 'cancelled', :admin_id, :note);

COMMIT;
```

### Dashboard admin — thống kê cơ bản

```sql
-- Tổng đơn hàng theo trạng thái
SELECT order_status, COUNT(*) AS count
FROM orders
GROUP BY order_status;

-- Doanh thu đơn đã giao (delivered) tháng này
SELECT SUM(grand_total) AS revenue
FROM orders
WHERE order_status = 'delivered'
  AND payment_status = 'paid'
  AND created_at >= :month_start
  AND created_at < :month_end;

-- Sản phẩm sắp hết hàng (< 10)
SELECT p.name, pv.sku, pv.size_label, pv.color_name, pv.stock_quantity
FROM product_variants pv
INNER JOIN products p ON p.id = pv.product_id
WHERE pv.stock_quantity < 10 AND pv.is_active = 1
ORDER BY pv.stock_quantity ASC
LIMIT 20;
```
