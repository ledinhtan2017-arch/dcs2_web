# SPORT SHOP — AI PROJECT SPECIFICATION & VIBE CODING PLAYBOOK

> Phiên bản: 1.1 | Ngôn ngữ giao diện: Tiếng Việt | Tiền tệ: VND | Múi giờ: Asia/Ho_Chi_Minh  
> Trạng thái: Bản đặc tả và hướng dẫn triển khai; **không phải mã nguồn đã hoàn thành**.

## 0. CÁCH AI PHẢI SỬ DỤNG FILE NÀY

Bạn là senior full-stack engineer, kiến trúc sư hệ thống, UI developer và QA engineer cho dự án **Sport Shop**. Đây là nguồn sự thật chính về chức năng, kỹ thuật, bảo mật và tiêu chí nghiệm thu. Đọc đồng thời `DESIGN.md` ở thư mục gốc: file đó là nguồn sự thật cho giao diện. Nếu có xung đột, ưu tiên `DESIGN.md` với thiết kế; ưu tiên file này với nghiệp vụ, data/API và bảo mật. Không sửa `DESIGN.md` khi chưa được yêu cầu.

**Quy tắc bắt buộc:**
1. Không tạo website chỉ bằng HTML tĩnh hoặc dữ liệu giả rồi tuyên bố dự án xong. Sản phẩm/giá/tồn kho/đơn hàng phải đến từ MySQL qua PHP.
2. Không dùng React, Vue, Angular, Next.js, Node.js cho runtime hoặc Laravel nếu chưa có xác nhận; frontend **HTML5/CSS3/vanilla JavaScript (ES modules)**; backend **PHP 8.2+ thuần, có tổ chức**, cơ sở dữ liệu **MySQL 8+**. Có thể dùng Composer cho thư viện PHP thực sự cần thiết.
3. Làm theo giai đoạn. Trước khi sửa: liệt kê file sẽ tạo/sửa và đầu ra mong đợi. Sau mỗi giai đoạn: chạy kiểm thử liên quan, báo file đã đổi, cách kiểm tra, lỗi còn tồn tại; không tự nhận đã test khi chưa chạy.
4. Không xóa/ghi đè file, thay đổi schema phá dữ liệu hoặc đưa dữ liệu thật lên GitHub mà không kiểm tra. Ưu tiên thay đổi nhỏ và tương thích.
5. Dùng prepared statements (PDO); validate input phía server; encode output; CSRF protection cho thao tác thay đổi qua session; `password_hash`/`password_verify`; session cookie an toàn; phân quyền server-side; transaction cho tạo đơn; không tin giá tiền, quyền admin hoặc số lượng do client gửi.
6. **CHO PHÉP và khuyến khích AI tự tạo ảnh** cho website: ảnh hero/banner, ảnh danh mục môn thể thao, ảnh sản phẩm minh họa, hình lifestyle và nhận diện/logo riêng Sport Shop bằng công cụ tạo ảnh (nếu có). AI được chủ động tạo tài nguyên hình ảnh phù hợp `DESIGN.md`, không cần xin phép lại cho từng ảnh. Ảnh do AI tạo phải được lưu thành file thực trong dự án và sử dụng trên giao diện, không chỉ mô tả bằng prompt. Không sao chép logo Nike, ảnh có bản quyền hoặc giả mạo ảnh sản phẩm/thương hiệu thực; khi ảnh mang tính minh họa, phải ghi rõ ở dữ liệu demo hoặc tài liệu.
7. Thiết kế theo WCAG hợp lý: semantic HTML, nhãn form, điều hướng bàn phím, focus rõ, alt text, tương phản phù hợp, thông báo lỗi dễ hiểu.
8. Nếu thiếu thông tin không quan trọng, chọn mặc định hợp lý, ghi vào `docs/DECISIONS.md` và tiếp tục. Nếu thiếu thông tin liên quan tiền thật, thanh toán production, quyền truy cập hoặc thông tin bí mật, không tự giả định; dùng sandbox/mock luồng được ghi rõ.
9. Luôn cập nhật `docs/PROGRESS.md` (đã làm / chưa làm / đang lỗi / bước tiếp theo). Không đánh dấu hoàn thành khi không đạt điều kiện nghiệm thu.
10. Trước khi phát triển, đọc các file hiện có và tái sử dụng code hợp lệ thay vì tạo duplicate.

---

## 1. TẦM NHÌN & PHẠM VI

Website thương mại điện tử đồ thể thao đa môn. Khách tìm sản phẩm theo **môn thể thao → danh mục → sản phẩm → biến thể**, chọn số lượng, đặt hàng và theo dõi đơn. Admin quản lý nội dung và vận hành. Dùng cho mục tiêu học tập/portfolio trước, với thiết kế và quy trình đủ tốt để có thể mở rộng production sau khi được review bảo mật và vận hành.

### 1.1. Cây danh mục khởi tạo (admin được thêm/sửa)

| Môn thể thao | Nhóm sản phẩm ví dụ | Thuộc tính/biến thể ví dụ |
| --- | --- | --- |
| Cầu lông | Vợt, giày, áo, quần, cầu, túi, phụ kiện | Khối lượng 3U/4U, grip, size giày, màu |
| Bóng đá | Áo đấu, giày, bóng, tất, găng tay, phụ kiện | Size áo S-XXL, size giày 39-45, số bóng 4/5, màu |
| Bóng rổ | Bóng, giày, áo, quần, phụ kiện | Size bóng 6/7, size giày, màu |
| Chạy bộ / Fitness | Giày, áo, quần, bình nước, phụ kiện | Size, màu, giới tính phù hợp |

Sản phẩm có thể thuộc nhiều môn/danh mục liên quan nếu cần; không hard-code mọi danh mục trong JavaScript.

### 1.2. Phân quyền

- **Guest**: xem sản phẩm, tìm kiếm, lọc, giỏ hàng tạm, đặt hàng không cần tài khoản (guest checkout, nếu thực hiện ở giai đoạn checkout).
- **Customer**: hồ sơ, địa chỉ, wishlist, giỏ hàng, checkout, xem các đơn **của chính mình**, đánh giá sản phẩm đã mua.
- **Admin**: quản lý danh mục, thương hiệu, sản phẩm, biến thể, ảnh, kho, đơn hàng, mã giảm giá, nội dung banner và người dùng; dashboard.

Tất cả kiểm tra quyền **phải ở PHP**, không chỉ ẩn nút bằng JS.

### 1.3. Thứ tự ưu tiên

**MVP bắt buộc (P0):** trang chủ, điều hướng thể thao, danh sách/tìm/lọc/sắp xếp, chi tiết + biến thể + tồn kho, tài khoản, giỏ hàng, checkout COD, đơn hàng và trạng thái, admin CRUD, upload ảnh, quản lý kho, seed data, bảo mật cơ bản và test các luồng cốt lõi.

**P1 sau MVP:** wishlist, đánh giá đã mua, mã giảm giá, admin dashboard thống kê, banner CMS, quản lý địa chỉ, lịch sử đơn chi tiết, email thông báo nếu có cấu hình SMTP.

**P2 sau P1:** thanh toán cổng chính thức, tự động phí vận chuyển theo hãng, trả hàng/hoàn tiền phức tạp, báo cáo nâng cao, SEO nâng cao, import/export, multi-language. Không giả vờ đã tích hợp thanh toán thật.

---

## 2. DESIGN CONTRACT — ĐỌC `DESIGN.md`

`DESIGN.md` đính kèm là thiết kế gốc tham khảo phong cách Nike, **không phải giấy phép sử dụng logo/ảnh/font độc quyền**. Dùng token và nguyên lý của file để tạo **Sport Shop có nhận diện riêng**:

- Màu: `#111111` (primary/ink), `#FFFFFF` (canvas), `#F5F5F5` (soft surface), `#707072` (muted), `#D30005` (giá khuyến mãi), `#007D48` (thành công). Các màu khác theo file design khi thực sự cần.
- Typography: body **Inter** hoặc Arial fallback; tiêu đề campaign **Bebas Neue** hoặc **Anton** (font được cấp phép), không phụ thuộc `Nike Futura ND` / `Helvetica Now` proprietary.
- Hình sản phẩm 1:1 trên nền xám sáng; grid 3 cột desktop / 2 tablet / 1 mobile theo gợi ý design; hero ảnh lớn ~16:9 desktop và crop phù hợp mobile.
- CTA primary đen, chữ trắng, bo tròn pill; chip lọc pill; thẻ sản phẩm vuông góc, phẳng, không shadow; khoảng cách nền 8px, section 48px desktop.
- Navbar desktop gồm logo Sport Shop, New/Featured, Cầu lông, Bóng đá, Bóng rổ, Chạy bộ, tìm kiếm, tài khoản, wishlist, giỏ hàng. Mobile dùng menu drawer, search icon và cart icon.
- Product listing có breadcrumb, filter sidebar (môn, loại, thương hiệu, giá, size, màu, còn hàng), sort, phân trang. PDP có gallery, lựa chọn biến thể, số lượng, nút thêm giỏ, mô tả và chính sách giao hàng.
- Tất cả thành phần phải thống nhất với `DESIGN.md`; triển khai thêm các state chưa có trong file gốc: loading, empty, error, disabled, focus, validation, toast, modal, checkout, login và admin.
- Giao diện admin cùng bảng màu nhưng ưu tiên khả năng sử dụng của bảng biểu; không cố nhét ảnh campaign vào admin.

### 2.1. QUY ĐỊNH TẠO ẢNH BẰNG AI (ĐƯỢC PHÉP)

- AI **được quyền tự tạo ảnh** mới mà không cần hỏi lại cho từng ảnh, miễn đúng bối cảnh website Sport Shop và phong cách `DESIGN.md`.
- Ưu tiên tạo: (1) hero trang chủ bóng đá/cầu lông, (2) banner từng môn, (3) ảnh sản phẩm demo như vợt, giày, bóng, áo thể thao, (4) ảnh lifestyle/khuyến mãi, (5) logo chữ hoặc biểu tượng độc lập Sport Shop.
- Ảnh sản phẩm minh họa cần khớp **loại sản phẩm, màu và biến thể** trong MySQL; ảnh trong gallery phải nhất quán với sản phẩm tương ứng. Không tự tạo hình có logo của hãng thật rồi trình bày như ảnh chính hãng.
- Quy cách: ảnh sản phẩm 1:1; hero desktop khoảng 16:9, bản mobile 4:5 khi cần; xuất WebP/AVIF hoặc JPEG/PNG tối ưu, đặt tên dễ hiểu và lưu tại `public/assets/images/` (ảnh do AI tạo) hoặc đường dẫn được kiểm soát. Cập nhật bản ghi DB `product_images`, `sports.image_path`, `banners.image_path` tương ứng.
- Nếu coding agent **không có công cụ tạo ảnh**: tuyệt đối không tuyên bố đã tạo ảnh. Ghi prompt ảnh cụ thể vào `docs/IMAGE_PROMPTS.md`, dùng placeholder tạm có nhãn, thông báo phần còn thiếu để người dùng có thể tạo ảnh bằng công cụ khác. Không chèn URL ảnh tưởng tượng hoặc ảnh hỏng.
- Dùng alt text mô tả đúng nội dung; tránh ảnh vi phạm quyền sở hữu trí tuệ, giả mạo sản phẩm thật hay sử dụng hình ảnh cá nhân trái phép.

**Lưu ý:** File design gốc chưa xác nhận nhiều trạng thái mobile, form checkout, login và cart, vì vậy phải thiết kế bổ sung có chủ đích, ghi lại trong `docs/DECISIONS.md`.

---

## 3. KIẾN TRÚC VÀ TỔ CHỨC THƯ MỤC

Kiến trúc đề xuất: **PHP monolith có lớp API JSON**. PHP serve các trang HTML qua template `.php` (HTML vẫn là frontend) và endpoint `/api/*`; JS sử dụng `fetch` để cập nhật tương tác. Apache + PHP + MySQL (XAMPP để phát triển cục bộ). Không cần SPA framework.

```text
sport-shop/
├── DESIGN.md                  # design nguyên gốc
├── SPORT_SHOP_AI_SPEC.md      # file chỉ dẫn này
├── README.md
├── .env.example               # chỉ ví dụ, không có secret thật
├── .gitignore
├── composer.json              # tùy nhu cầu
├── app/
│   ├── Config/               # đọc environment/config
│   ├── Core/                 # Router, Request, Response, DB, Auth, CSRF
│   ├── Controllers/          # Public, Auth, Cart, Checkout, Admin, API
│   ├── Models/               # truy vấn PDO và entity mapping
│   ├── Services/             # price, stock, order, image upload...
│   ├── Middleware/           # auth, admin, CSRF, rate limit khi cần
│   └── Views/
│       ├── layouts/          # header, footer, navigation
│       ├── partials/         # component có thể dùng lại
│       ├── shop/             # home, listing, PDP, search, cart, checkout
│       ├── account/          # login, register, profile, orders
│       └── admin/            # dashboard và CRUD
├── public/                   # CHỈ THƯ MỤC NÀY PUBLIC WEB
│   ├── index.php             # front controller
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/              # tokens.css, base.css, components.css, pages.css
│   │   ├── js/               # api.js, cart.js, filters.js, checkout.js...
│   │   ├── images/           # ảnh được cấp phép hoặc do AI tự tạo: hero, sport, product, banner
│   │   └── fonts/            # chỉ font có quyền sử dụng
│   └── uploads/products/     # ảnh được kiểm định; chặn thực thi PHP
├── database/
│   ├── migrations/           # SQL migration có thứ tự
│   └── seeds/                # SQL/PHP seed demo hợp lệ
├── tests/                    # test đơn vị/integration và kịch bản manual
└── docs/
    ├── DECISIONS.md
    ├── PROGRESS.md
    ├── API.md
    ├── DATABASE.md
    └── TEST_PLAN.md
```

Nếu sử dụng XAMPP: ưu tiên cấu hình Apache DocumentRoot trỏ tới `sport-shop/public`. Nếu không đổi DocumentRoot, cấu hình rewrite/virtual host sao cho `app/`, `.env`, `database/`, `docs/` không thể tải từ trình duyệt. Không đưa secret vào folder public.

### 3.1. Luồng dữ liệu mẫu

`Product page → JS fetch('/api/products/{id}') → PHP Router → ProductController → ProductService → PDO → MySQL → JSON → DOM`. Trường hợp render ban đầu bằng PHP cũng dùng chung Service/Model; không nhân đôi business logic.

Quy ước API: `Content-Type: application/json`, response thành công `{ "success": true, "data": ..., "meta": ... }`, thất bại `{ "success": false, "error": { "code": "...", "message": "..." } }`. Dùng status 200/201/204/400/401/403/404/409/422/429/500 hợp lý, không trả 200 cho mọi lỗi.

---

## 4. DATABASE MYSQL — THIẾT KẾ YÊU CẦU

- Engine InnoDB, charset `utf8mb4`, khóa ngoại, unique index và index tìm kiếm. Tiền tệ VND: `DECIMAL(12,0)` hoặc `BIGINT` đơn vị đồng, **không dùng FLOAT**. Thời gian lưu UTC khi phù hợp và hiển thị `Asia/Ho_Chi_Minh`.
- Schema thực tế phải tạo bằng migration SQL có thứ tự; README ghi lệnh tạo DB/migrate/seed. Không dùng truncate/drop dữ liệu ngoài môi trường test.

| Bảng | Cột quan trọng | Ràng buộc / mục đích |
| --- | --- | --- |
| `users` | id, name, email, password_hash, role, status, created_at | email unique, role customer/admin |
| `addresses` | id, user_id, receiver_name, phone, province, district, ward, street, is_default | thuộc về user |
| `sports` | id, name, slug, image_path, is_active | cầu lông/bóng đá...; slug unique |
| `categories` | id, sport_id, parent_id nullable, name, slug, is_active | phân cấp danh mục theo môn; index theo sport |
| `brands` | id, name, slug, logo_path | thương hiệu sản phẩm |
| `products` | id, category_id, brand_id nullable, name, slug, description, status, created_at | không dùng một cột size duy nhất |
| `product_sports` (nếu cần) | product_id, sport_id | sản phẩm xuất hiện ở nhiều môn; tránh duplicate |
| `product_variants` | id, product_id, sku, size_label nullable, color_name nullable, attributes_json nullable, price, sale_price nullable, stock_quantity, is_active | mỗi size/màu có SKU/tồn kho/giá; SKU unique; stock >= 0 |
| `product_images` | id, product_id, variant_id nullable, path, alt_text, sort_order, is_primary | nhiều ảnh/variant |
| `carts` | id, user_id nullable, guest_token_hash nullable, created_at, updated_at | giỏ persistent; token khách không lưu plaintext |
| `cart_items` | id, cart_id, variant_id, quantity | unique cart+variant; quantity > 0 |
| `orders` | id, order_code, user_id nullable, customer_name, email, phone, shipping_address_json, subtotal, discount_total, shipping_fee, grand_total, payment_method, payment_status, order_status, created_at | snapshot dữ liệu mua hàng, mã unique |
| `order_items` | id, order_id, variant_id nullable, sku_snapshot, name_snapshot, options_snapshot, unit_price, quantity, line_total | dữ liệu snapshot không đổi khi admin sửa sản phẩm |
| `order_status_history` | id, order_id, from_status, to_status, actor_user_id nullable, note, created_at | audit trạng thái |
| `payments` | id, order_id, provider, provider_reference nullable, amount, status, created_at | COD trước; gateway thật P2 |
| `wishlists` (P1) | user_id, product_id, created_at | unique user+product |
| `reviews` (P1) | id, user_id, product_id, order_item_id, rating, body, status, created_at | chỉ đánh giá đơn đã mua, 1 review/order_item |
| `coupons` (P1) | id, code, discount_type, value, starts_at, ends_at, usage_limit, is_active | mã unique, validate server |
| `banners` (P1) | id, title, image_path, link_path, sort_order, is_active | quản trị banner |

Phải chốt một cách phân loại nhất quán: mặc định `categories.sport_id` là môn chính của category; dùng `product_sports` khi một sản phẩm có nhiều môn. `products.category_id` chỉ primary category. Khi thêm cross-listing nâng cao, thêm bảng `product_categories` bằng migration riêng.

### 4.1. Seed demo

Ít nhất 4 môn, mỗi môn >= 3 nhóm danh mục, tổng >= 24 sản phẩm demo, các sản phẩm ưu tiên ảnh minh họa do AI tự tạo (có thể dùng placeholder tạm nếu chưa có công cụ tạo ảnh), 2-3 biến thể phù hợp và tồn kho khác nhau. Ví dụ: vợt cầu lông (3U/4U), giày cầu lông (40/41/42), áo đấu bóng đá (S/M/L), giày bóng đá (40/41), bóng đá size 5, giày chạy bộ (40/41/42). Sử dụng tên/giá demo rõ ràng; không gắn nhãn giá thị trường thực tế.

---

## 5. DANH SÁCH TRANG & CHỨC NĂNG

### 5.1. Khách hàng (P0)

| Trang / route gợi ý | Nội dung / hành vi bắt buộc |
| --- | --- |
| `/` | Hero, nổi bật, shop by sport, mới, bán chạy (dựa dữ liệu có sẵn) |
| `/sports/{slug}` | Trang môn: banner, nhóm sản phẩm, sản phẩm theo môn |
| `/categories/{slug}` | Danh sách sản phẩm có filter/sort/pagination |
| `/products/{slug}` | Gallery, tên, giá hiện hành, brand, thuộc tính, variant, stock, số lượng, add to cart |
| `/search?q=` | Tìm tên, SKU, brand; empty result; giữ từ khóa và filter |
| `/cart` | xem/sửa/xóa items, hiện đúng variant và giá backend, cập nhật tổng |
| `/checkout` | thông tin người nhận, địa chỉ, COD, chi phí cuối, đặt hàng một lần |
| `/order-success/{code}` | xác nhận đơn, thông tin không nhạy cảm; xác minh quyền/guest token |
| `/login`, `/register`, `/logout` | đăng nhập, đăng ký, đăng xuất, lỗi hợp lệ |
| `/account`, `/account/orders`, `/account/orders/{code}` | hồ sơ, lịch sử & chi tiết đơn của chính user |
| `/404`, `/403`, `/500` | trang lỗi theo design, không lộ stack trace |

### 5.2. Admin (P0)

| Trang | Nghiệp vụ |
| --- | --- |
| `/admin` | tổng đơn, doanh thu đơn đã xác nhận/thanh toán (định nghĩa rõ), sản phẩm sắp hết |
| `/admin/products` | danh sách, thêm/sửa/ẩn, nhập biến thể SKU/size/màu/giá/tồn kho, ảnh |
| `/admin/sports`, `/admin/categories`, `/admin/brands` | CRUD + ràng buộc tránh xóa dữ liệu đang được tham chiếu |
| `/admin/orders` | lọc, xem chi tiết, chuyển trạng thái hợp lệ, ghi history |
| `/admin/users` | xem tài khoản/khóa mềm; không cho customer tự nâng role |

### 5.3. UI states bắt buộc

Mọi trang dữ liệu có loading, empty, error, success; hiển thị VND dạng `1.250.000 ₫`, thông báo thiếu size, hết hàng, lỗi mạng; disabled khi gửi form; chống double submit; responsive 320px → desktop; focus keyboard. Không render raw HTML từ input người dùng (`innerHTML` không tin cậy).

---

## 6. API CONTRACT (GỢI Ý, CHỐT TẠI `docs/API.md`)

| Method | Endpoint | Ai dùng | Ghi chú |
| --- | --- | --- | --- |
| GET | `/api/sports`, `/api/categories` | public | cấu trúc menu |
| GET | `/api/products?search=&sport=&category=&brand=&min_price=&max_price=&size=&sort=&page=` | public | whitelist sort; paginate, giới hạn page_size |
| GET | `/api/products/{id}` | public | variants, images, stock |
| GET/POST/PATCH/DELETE | `/api/cart` & `/api/cart/items/{id}` | guest/customer | backend tính lại totals và validate stock |
| POST | `/api/auth/register`, `/api/auth/login`, `/api/auth/logout` | public/session | auth + CSRF + throttling |
| GET | `/api/auth/me` | customer/admin | trả thông tin tối thiểu |
| POST | `/api/checkout` | guest/customer | idempotency key, transaction, COD |
| GET | `/api/orders/{code}` | owner / guest có token | không lộ đơn người khác |
| GET/POST/PATCH | `/api/admin/products`, `/api/admin/products/{id}` | admin | upload ảnh route riêng; validate dữ liệu |
| GET/POST/PATCH | `/api/admin/orders`, `/api/admin/orders/{id}/status` | admin | state machine |

Có thể chọn gửi form HTML truyền thống thay vì API với một số trang; tuy nhiên phải thống nhất với router, auth, CSRF và tránh tạo 2 nguồn logic nghiệp vụ.

---

## 7. QUY TẮC NGHIỆP VỤ CỰC KỲ QUAN TRỌNG

1. Giá hiển thị tính từ `product_variants`; giá sale hợp lệ chỉ khi `0 <= sale_price < price`. Backend quyết định giá cuối, JS chỉ hiển thị.
2. Chỉ cho chọn số lượng `1..stock_quantity`. Trạng thái hết hàng không được checkout.
3. Giỏ hàng guest sử dụng session hoặc cookie token ngẫu nhiên, HttpOnly/Secure khi HTTPS; merge giỏ khi login (xác định chính sách cộng hoặc chọn max, kiểm tra tồn kho). Không dùng localStorage làm nguồn sự thật cho đơn hàng.
4. Checkout: validate người nhận, phone, địa chỉ, items; truy vấn lại giá/tồn kho; transaction + lock rows biến thể khi cần; lưu `orders` và `order_items` snapshot; trừ/reserve stock đúng **một lần**; rollback toàn bộ nếu lỗi; ngăn lặp request bằng idempotency key unique. Không tạo hai đơn khi double click.
5. COD: `payment_status = unpaid`; chuyển paid khi xác nhận thu tiền. Không đánh dấu paid lúc đặt hàng.
6. Vòng đời đơn tối thiểu: `pending → confirmed → shipping → delivered`; `pending/confirmed → cancelled` khi hợp lệ. Định nghĩa rõ chính sách hoàn kho khi hủy: chỉ hoàn một lần và lưu history; không cho quay ngược trạng thái tùy ý.
7. Mọi biến thể được quản lý riêng; nếu xóa/ẩn sản phẩm vẫn phải giữ snapshot đơn cũ. Không hard-delete dữ liệu đã được order tham chiếu.
8. Tìm kiếm, filter, sort dùng query được kiểm soát (parameter binding + whitelist column), phân trang server-side; chống N+1 và query vô hạn.
9. Không expose ID tuần tự của đơn như chứng thực; owner check hoặc token bí mật cho guest tracking; rate limit endpoint nhạy cảm.
10. Sản phẩm chỉ hiển thị public khi active; SKU, category, variant, status và stock phải được kiểm tra trên server.

---

## 8. SECURITY & QUALITY BAR

- PDO `ERRMODE_EXCEPTION`, `ATTR_EMULATE_PREPARES=false`; không nối trực tiếp input vào SQL.
- Đăng nhập PHP session với `session_regenerate_id(true)` sau khi auth, logout invalidation, `HttpOnly`, `SameSite=Lax/Strict`, `Secure` khi HTTPS.
- CSRF token cho mọi POST/PATCH/DELETE dựa session; API có session cũng phải kiểm tra CSRF/origin phù hợp.
- Escape HTML ở PHP bằng `htmlspecialchars`; JS dùng `textContent` cho dữ liệu text; chặn stored/reflected XSS.
- Upload ảnh: allowlist MIME được xác minh bằng fileinfo, verify ảnh, giới hạn size/kích thước, tên ngẫu nhiên, không cho upload hoặc chạy PHP/SVG script, ngăn traversal; tách public paths và dữ liệu.
- Validate field type, enum, min/max, email, phone, độ dài ở backend; lỗi không tiết lộ SQL, filesystem hoặc secret.
- Cấu hình `.env` không commit, `.env.example` không chứa password; tài khoản admin demo chỉ seed tại dev, hướng dẫn đổi mật khẩu.
- Không xử lý thẻ ngân hàng trực tiếp; payment integration P2 qua SDK/server verification của cổng được lựa chọn và HTTPS. Không tuyên bố PCI-compliant chỉ vì đã có form.
- CORS hạn chế đúng origin cần thiết; headers chống clickjacking, MIME sniffing; HTTPS production; logs không chứa password/token.
- Kiểm thử unauthorized IDOR, CSRF, SQLi, XSS, session expiry, stock race, double submit, upload sai định dạng.
- Hiệu năng: ảnh nén WebP/AVIF khi được hỗ trợ, lazy-load, kích thước ảnh đúng tỉ lệ; paginate backend, index filter; hạn chế blocking JS.
- SEO cơ bản: title/meta độc lập, URL slug, h1 duy nhất, semantic structure, canonical khi cần, alt text.

---

## 9. QUY TRÌNH TRIỂN KHAI THEO MILESTONE

**M0 — Audit & planning**
- Đọc `DESIGN.md`, spec này và source hiện tại. Viết `docs/DECISIONS.md`, `docs/PROGRESS.md`, lập routes/page map, ERD, API map và danh sách component.
- **Done khi:** tech stack được cố định; dependency, route và entity quan hệ rõ; không có phần cốt lõi chưa chốt.

**M1 — Bootstrap / dev environment**
- Dựng cấu trúc thư mục; Apache rewrite, `.env.example`, PDO connection, error handling, router, README, .gitignore; kiểm tra PHP/MySQL.
- **Done khi:** `GET /` chạy từ PHP/Apache, app kết nối MySQL theo cấu hình, lỗi được xử lý không lộ secret.

**M2 — Schema & sample data**
- Tạo migration, seed data các môn + sản phẩm/biến thể, ERD và data dictionary.
- **Done khi:** migrate từ DB rỗng thành công; seed idempotent hoặc reset dev an toàn; join/query dữ liệu chạy được.

**M3 — Design system / layout skeleton**
- `tokens.css`, reset, typography, buttons, cards, nav, footer, responsive; **chủ động tạo ảnh AI** cho hero/banner/danh mục và ảnh minh họa sản phẩm, lưu asset vào dự án; tạo trang chủ và trang danh mục dùng data từ DB.
- **Done khi:** khớp tinh thần `DESIGN.md`, responsive 320/768/1440 và không còn lorem ipsum trong luồng chính.

**M4 — Catalog end-to-end**
- Trang theo môn, category, search, filter, sort, pagination, PDP gallery, variants, available stock.
- **Done khi:** admin/seed thêm 1 sản phẩm thì listing và detail xuất hiện đúng; filters/sort hoạt động với dữ liệu backend.

**M5 — Auth, session & profile**
- Register, login/logout, role middleware, profile, lịch sử orders placeholder đúng quyền (sẽ có dữ liệu sau M7).
- **Done khi:** customer không truy cập admin; truy cập tài khoản khác bị từ chối; password lưu hash.

**M6 — Cart**
- Giỏ guest/customer, chọn variant, thay đổi qty, remove item, tính tổng server-side, xử lý hết hàng; JS fetch.
- **Done khi:** refresh không mất cart, không thể thêm quá tồn kho, giá không thể sửa từ client.

**M7 — Checkout COD & order lifecycle**
- Checkout, tạo order transaction/idempotency, trừ kho, confirm success, order history/detail, admin status update và hoàn kho khi cancel.
- **Done khi:** giao dịch end-to-end chạy; double submit không trùng đơn; rollback khi lỗi; không thể xem đơn người khác.

**M8 — Admin CRUD**
- CRUD sports/category/brand/product/variant/images, quản lý tồn kho, orders, dashboard basic.
- **Done khi:** admin thêm/sửa/ẩn một sản phẩm thấy thay đổi trên public; upload hợp lệ; kiểm tra phân quyền.

**M9 — Enhancements (P1)**
- Wishlist, purchased reviews, coupons, banners, addresses, admin reporting; thêm tùy tiến độ.
- **Done khi:** mỗi tính năng có migration/API/UI/test rõ ràng, không làm hỏng P0.

**M10 — QA & shipping**
- Kiểm thử chức năng/security/responsive/accessibility; cleanup, performance, README setup + deploy checklist, backup strategy.
- **Done khi:** test plan pass; không có issue nghiêm trọng chưa giải quyết, demo script và tài khoản dev rõ ràng.

**Không** triển khai M8 admin quản lý sản phẩm trước khi có schema M2. Có thể phát triển frontend song song bằng mock ngắn hạn nhưng phải đánh dấu MOCK và thay thế trước khi nghiệm thu M4/M7.

---

## 10. TEST CASES / DEFINITION OF DONE (MVP)

| ID | Kiểm thử chấp nhận | Kết quả mong đợi |
| --- | --- | --- |
| T01 | mở `/` desktop/mobile | layout không vỡ, điều hướng đúng |
| T02 | vào Cầu lông → Vợt | chỉ sản phẩm phù hợp, ảnh/giá từ DB |
| T03 | tìm `giày`, filter size, sort giá | kết quả thực, đúng thứ tự, trang |
| T04 | mở sản phẩm nhiều biến thể | giá/tồn kho/ảnh thay theo lựa chọn |
| T05 | thêm sản phẩm chưa chọn size | yêu cầu chọn biến thể, không thêm sai |
| T06 | qty lớn hơn stock | backend trả validation error |
| T07 | sửa giá qua DevTools khi checkout | đơn vẫn dùng giá server |
| T08 | guest đặt COD | tạo 1 order, trừ kho 1 lần, payment unpaid |
| T09 | gửi lại checkout 2 lần cùng key | chỉ 1 đơn có hiệu lực |
| T10 | admin hủy đơn đúng điều kiện | hoàn kho 1 lần, lưu status history |
| T11 | customer gọi API admin | 403 |
| T12 | user A cố xem đơn user B | 403/404, không lộ PII |
| T13 | upload file PHP giả ảnh | từ chối, file không thực thi |
| T14 | input chứa script / dấu nháy SQL | không XSS, không SQL injection |
| T15 | đăng xuất / CSRF sai | protected write bị chặn |
| T16 | admin thêm sản phẩm + variant + ảnh | hiển thị đúng ở catalog và chi tiết |
| T17 | responsive 320, 375, 768, 1024, 1440 | không overflow ngang, navbar dùng được |
| T18 | Tab/Enter/labels/alt/contrast | thao tác chính bằng bàn phím được |
| T19 | fresh install từ README | migrate, seed, run không cần sửa code bí mật |
| T20 | lỗi kết nối DB | thông báo thân thiện, server logs chi tiết, không lộ secret |

Cần cả kiểm thử thủ công và tự động hợp lý: PHP unit/integration (ví dụ PHPUnit nếu có), HTTP smoke test, JS DOM/unit nếu đã có test runner. **Báo chính xác những test đã chạy**.

---

## 11. CÁCH PHỐI HỢP VỚI AI MỖI LẦN CODE

### 11.1. Prompt khởi động — copy vào AI coding agent

```text
Bạn là Senior Full-Stack Developer cho dự án Sport Shop.
Hãy đọc toàn bộ DESIGN.md và SPORT_SHOP_AI_SPEC.md, sau đó kiểm tra cấu trúc source hiện tại.
Giữ nguyên stack HTML + CSS + Vanilla JavaScript / PHP / MySQL (XAMPP).
DESIGN.md quyết định thiết kế, SPORT_SHOP_AI_SPEC.md quyết định nghiệp vụ, kiến trúc và tiêu chí nghiệm thu.

BƯỚC NÀY CHỈ làm M0:
1. Phân tích yêu cầu; liệt kê trang, chức năng, role và luồng mua hàng.
2. Đề xuất cây thư mục thật, ERD, endpoint và lộ trình milestones.
3. Tạo docs/DECISIONS.md và docs/PROGRESS.md.
4. Chỉ ra điều gì đang có/thiếu trong repository.
5. Không code toàn website và không đổi stack.

Báo rõ output đã tạo và chờ chỉ dẫn giai đoạn tiếp theo.
```

### 11.2. Prompt triển khai từng milestone

```text
Tiếp tục dự án Sport Shop. Đọc DESIGN.md, SPORT_SHOP_AI_SPEC.md, docs/DECISIONS.md và docs/PROGRESS.md.
Hãy triển khai DUY NHẤT milestone M[N] được mô tả trong spec.
Trước khi làm, liệt kê file cần tạo/sửa và các acceptance criteria.
Viết code thực sự chạy với HTML/CSS/JS + PHP + MySQL, không thay bằng mock trừ khi spec cho phép.
Đảm bảo validation và kiểm thử phù hợp. Không phá tính năng cũ.
Sau khi làm: chạy test khả thi, mô tả test PASS/FAIL, cách chạy tại XAMPP,
liệt kê file đã đổi, limitations và cập nhật docs/PROGRESS.md.
```

### 11.3. Prompt QA / sửa lỗi

```text
Đọc DESIGN.md, SPORT_SHOP_AI_SPEC.md và docs/PROGRESS.md.
Kiểm tra chức năng [TÊN] end-to-end: frontend interaction → API PHP → PDO → MySQL.
Tái hiện lỗi bằng bước cụ thể, tìm root cause, sửa tối thiểu, thêm regression test.
Không sửa thiết kế ngoài phạm vi, không bỏ validation hay tắt security để qua test.
Nêu file thay đổi, bằng chứng test và tác động đến các luồng khác.
```

### 11.4. Prompt review cuối dự án

```text
Audit toàn bộ Sport Shop so với mục 10 của SPORT_SHOP_AI_SPEC.md.
Chạy các test có thể chạy; review schema, routes, auth, CSRF, SQL injection,
XSS, upload, stock race, order idempotency, responsive, keyboard, README.
Tạo báo cáo PASS/FAIL/BLOCKED từng test, issue severity và thứ tự sửa.
Không đánh dấu production-ready khi chưa kiểm chứng security, backup và HTTPS.
```

---

## 12. QUY TẮC GIT & TÀI LIỆU

- Dùng Git branch hoặc commit theo milestone, ví dụ `feat/catalog`, `feat/checkout`, `fix/order-stock`; commit nhỏ, mô tả rõ.
- Trước `git push`: `git status`, `git diff`, test; **không `push --force`** để giải quyết xung đột nếu chưa hiểu lịch sử. Không commit `.env`, credentials, uploads chứa PII, log hoặc dump thực tế.
- `README.md` phải gồm prerequisites, cấu hình Apache/XAMPP, tạo `.env`, tạo DB, migration, seed, chạy app, test, tài khoản demo (cách tạo an toàn), troubleshooting.
- `docs/PROGRESS.md`: milestone, trạng thái, ngày cập nhật, test, blockers, bước tiếp theo. `docs/DECISIONS.md`: mọi lựa chọn không có trong spec.
- Không dùng ảnh Nike, logo Nike, slogan Nike hoặc proprietary fonts trong website Sport Shop nếu không được phép; chỉ **lấy cảm hứng thiết kế**.

---

## 13. THÔNG SỐ MẶC ĐỊNH ĐỂ TRIỂN KHAI NGAY

- Tên thương hiệu tạm: **SPORT SHOP** (có thể đổi qua cấu hình).
- Địa bàn bán hàng demo: Việt Nam; hiển thị VND, nội dung tiếng Việt.
- Thanh toán MVP: **COD**; phí vận chuyển đơn giản theo cấu hình (không tự mô phỏng API vận chuyển thật).
- Image assets: **AI được phép chủ động tạo và sử dụng ảnh mới** (hero, banner, danh mục, ảnh sản phẩm minh họa, lifestyle, logo Sport Shop), bên cạnh ảnh do người dùng cung cấp hoặc ảnh đã được cấp phép. Chỉ dùng placeholder tạm khi không thể tạo ảnh; thay thế trước nghiệm thu UI nếu có công cụ tạo ảnh. Không gắn hình minh họa AI với tuyên bố sai về sản phẩm thương mại thực.
- Admin account: tạo từ CLI seed chỉ dành cho dev; password lấy từ env/input, không hard-code password mặc định trong repo.
- Mặc định lọc/sort/pagination xử lý server-side; page size 12, maximum 48.
- Nếu thông số nào thay đổi, ghi vào `docs/DECISIONS.md` và cập nhật các endpoint/test có liên quan.

**Mục tiêu hoàn thành:** khách vào trang, chọn môn Cầu lông/Bóng đá, tìm sản phẩm thực từ DB, chọn đúng size/màu, thêm giỏ, đặt COD, admin thấy đơn và cập nhật trạng thái, stock thay đổi đúng, bảo mật/kiểm thử đủ dùng cho một dự án portfolio nghiêm túc.
