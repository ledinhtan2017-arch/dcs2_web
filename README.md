# Sport Shop – E-Commerce Website

Website bán đồ thể thao với PHP thuần (không framework) và MySQL.

## Tiến độ dự án

*Cập nhật ngày 05/11/2026.*

| Hạng mục | Tiến độ |
|----------|----------|
| Cửa hàng | Đã có danh mục theo môn thể thao, tìm kiếm, trang sản phẩm và giỏ hàng. |
| Tài khoản | Đã có đăng ký, đăng nhập, cập nhật thông tin và xem đơn hàng. |
| Đặt hàng | Đã có thanh toán khi nhận hàng (COD), theo dõi trạng thái và lịch sử đơn. |
| Hủy đơn | Khách có tài khoản được hủy đơn của mình khi đang chờ xác nhận. Khách vãng lai có thể hủy đơn gần nhất trong phiên đặt hàng. Hệ thống hoàn tồn kho khi hủy thành công. |
| Quản trị | Đã có quản lý sản phẩm, biến thể, ảnh, môn thể thao, danh mục, thương hiệu, người dùng và trạng thái đơn hàng. |
| Hỗ trợ | Đã có trang hướng dẫn mua hàng, vận chuyển, đổi trả và liên hệ. Nội dung liên hệ và điều kiện đổi trả cần chủ cửa hàng bổ sung. |
| Kiểm thử | Đã có kiểm thử dùng bảng MySQL tạm cho vòng đời đơn hàng, hủy đơn, quyền truy cập, CSRF và quản lý sản phẩm. Cần tiếp tục kiểm tra giao diện và toàn bộ quy trình thực tế trên trình duyệt. |

Các tính năng chưa triển khai gồm thanh toán trực tuyến và khôi phục mật khẩu. Bảng milestone trong `docs/PROGRESS.md` là ghi chép ban đầu và chưa phản ánh đầy đủ các thay đổi hiện tại.

## 📋 Yêu cầu hệ thống

- **PHP** 8.2+ (với các extension: `pdo_mysql`, `mbstring`, `json`)
- **MySQL** 8.0+
- **Apache** (XAMPP khuyến nghị) với `mod_rewrite` bật
- **Composer** không cần (không dùng framework)

## 🚀 Cài đặt

### 1. Clone/Copy dự án

```bash
git clone <repo-url> d:\anti_sport
cd d:\anti_sport
```

### 2. Cấu hình môi trường

```bash
cp .env.example .env
```

Mở `.env` và cập nhật:

```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sport_shop
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://localhost:8080
```

### 3. Cấu hình Apache (XAMPP)

Trong `httpd-vhosts.conf` hoặc `httpd.conf`:

```apache
<VirtualHost *:8080>
    DocumentRoot "d:/anti_sport/public"
    ServerName localhost

    <Directory "d:/anti_sport/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Đảm bảo Apache có `Listen 8080`, bật `mod_rewrite` và include file virtual host. Nếu dùng cổng khác, thay `8080` trong cấu hình và địa chỉ truy cập. Khởi động lại Apache sau khi thay đổi.

### 4. Tạo Database

```bash
# Chạy migration
C:\xampp\php\php.exe database/migrate.php

# Chạy seed data
C:\xampp\php\php.exe database/seeds/seed.php
```

### 5. Khởi động

- Bật Apache + MySQL trong XAMPP
- Truy cập: `http://localhost:8080`
- Quản lý đơn hàng: `http://localhost:8080/admin/orders` sau khi đăng nhập admin.

Nếu database đã có dữ liệu, không cần chạy lại seed. Khi tạo admin lần đầu, seed yêu cầu mật khẩu; `admin123` là mặc định khi nhập mật khẩu không hợp lệ hoặc để trống.

### Quản lý ảnh và sản phẩm

Đăng nhập admin, vào **Sản phẩm**, chọn **Sửa** rồi dùng mục **Ảnh sản phẩm** để tải ảnh JPG, PNG hoặc WebP (tối đa 5 MB) và chọn ảnh chính. Với sản phẩm mới, tạo sản phẩm trước rồi mở lại trang sửa để thêm ảnh. Giá và tồn kho được quản lý theo từng biến thể; bỏ chọn **Đang bán** để ngừng bán một biến thể.

### Kiểm thử trên MySQL local

Các kiểm thử dưới đây dùng bảng tạm của kết nối để không thay đổi dữ liệu cửa hàng:

```powershell
C:\xampp\php\php.exe tests/order_lifecycle.php
C:\xampp\php\php.exe tests/customer_order_cancel.php
C:\xampp\php\php.exe tests/customer_cancel_route.php guest_missing_token
C:\xampp\php\php.exe tests/customer_cancel_route.php guest_wrong_session
C:\xampp\php\php.exe tests/customer_cancel_route.php guest_valid
C:\xampp\php\php.exe tests/customer_cancel_route.php owner_missing_token
C:\xampp\php\php.exe tests/customer_cancel_route.php owner_wrong_user
C:\xampp\php\php.exe tests/customer_cancel_route.php owner_valid
C:\xampp\php\php.exe tests/product_management.php create
C:\xampp\php\php.exe tests/product_management.php update
C:\xampp\php\php.exe tests/product_management.php rollback
C:\xampp\php\php.exe tests/product_management.php stale
```

## 👤 Tài khoản demo

| Vai trò  | Email                    | Mật khẩu    |
|----------|--------------------------|-------------|
| Admin    | admin@sportshop.local    | Mật khẩu đã đặt khi seed (mặc định `admin123`) |
| Khách    | customer@sportshop.local | customer123 |

## 📁 Cấu trúc dự án

```
anti_sport/
├── app/
│   ├── Config/          # .env parser, config
│   ├── Controllers/     # Page + API controllers
│   │   └── Api/
│   │       └── Admin/
│   ├── Core/            # Router, Database, Auth, CSRF, Validator, View, Request, Response
│   ├── Middleware/       # Auth, Admin, CSRF middleware
│   ├── Models/           # Sport, Category, Product, User, Cart, Order
│   └── Views/
│       ├── layouts/     # main.php, admin.php
│       ├── shop/        # home, sport, category, product, search, cart, checkout, order-success
│       ├── account/     # login, register, profile, orders, order-detail
│       ├── admin/       # dashboard, products, product-form, orders, order-detail, sports, categories, brands, users
│       └── errors/      # 404, 403
├── database/
│   ├── migrations/      # 20 SQL migration files
│   └── seeds/           # seed_data.sql + seed.php
├── docs/                # DECISIONS.md, DATABASE.md, PROGRESS.md
├── public/
│   ├── index.php        # Front controller
│   ├── .htaccess        # Apache rewrite
│   └── assets/
│       ├── css/styles.css
│       ├── js/app.js
│       └── images/
├── routes.php           # All route definitions
├── .env.example
└── .gitignore
```

## 🏅 Môn thể thao

- **Cầu lông** – Vợt, cầu, giày, phụ kiện
- **Bóng đá** – Giày, bóng, áo, bảo hộ
- **Bóng rổ** – Giày, bóng, trang phục
- **Chạy bộ / Fitness** – Giày, đồ tập, phụ kiện

## 🔐 Bảo mật

- CSRF token cho các form và các API sửa sản phẩm, ảnh sản phẩm, trạng thái đơn hàng; các API khác cần tiếp tục rà soát.
- Password hashing (`password_hash` / `password_verify`)
- PDO prepared statements (chống SQL injection)
- `htmlspecialchars` cho output (chống XSS)
- Session regeneration khi login
- Role-based authorization (customer / admin)
- Guest cart dùng session token SHA256 hash

## 💰 Tiền tệ

- Đơn vị: VND (Việt Nam Đồng)
- Kiểu dữ liệu: `DECIMAL(12,0)` – không dùng FLOAT
- Format: `1.250.000 ₫`

## 📝 Ghi chú

Đây là dự án portfolio, không phải website thương mại thật.

