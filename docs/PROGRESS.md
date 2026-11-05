# Tiến độ dự án — Sport Shop

> Cập nhật 05/11/2026: Bảng milestone bên dưới là ghi chép ban đầu, chưa phản ánh đầy đủ code hiện có. Ứng dụng đã có các trang danh mục, tài khoản, giỏ hàng, checkout COD và quản trị. Các chức năng cần tiếp tục kiểm thử thực tế trước khi đánh dấu hoàn thành. Đã bổ sung các trang hỗ trợ; thông tin liên hệ và điều kiện đổi trả cần chủ cửa hàng cung cấp. Thanh toán online và khôi phục mật khẩu chưa được triển khai.

> Cập nhật mỗi milestone.

## Tổng quan

| Milestone | Tên | Trạng thái | Ngày bắt đầu | Ngày hoàn thành |
|-----------|-----|-----------|---------------|-----------------|
| M0 | Audit & Planning | ✅ Hoàn thành | 2026-10-31 | 2026-10-31 |
| M1 | Bootstrap / Dev Environment | 🔄 Đang làm | 2026-10-31 | - |
| M2 | Schema & Sample Data | 🔄 Đang làm | 2026-10-31 | - |
| M3 | Design System / Layout | ⬜ Chưa bắt đầu | - | - |
| M4 | Catalog End-to-End | ⬜ Chưa bắt đầu | - | - |
| M5 | Auth, Session & Profile | ⬜ Chưa bắt đầu | - | - |
| M6 | Cart | ⬜ Chưa bắt đầu | - | - |
| M7 | Checkout COD & Order Lifecycle | ⬜ Chưa bắt đầu | - | - |
| M8 | Admin CRUD | ⬜ Chưa bắt đầu | - | - |
| M9 | Enhancements (P1) | ⬜ Chưa bắt đầu | - | - |
| M10 | QA & Shipping | ⬜ Chưa bắt đầu | - | - |

## Chi tiết Milestones

### M0 — Audit & Planning ✅
- [x] Đọc DESIGN.md và SPORT_SHOP_AI_SPEC.md
- [x] Tạo docs/DECISIONS.md
- [x] Tạo docs/PROGRESS.md
- [x] Tạo docs/API.md
- [x] Tạo docs/DATABASE.md
- [x] Tạo docs/TEST_PLAN.md
- [x] Xác định tech stack, routes, entities

### M1 — Bootstrap / Dev Environment 🔄
- [ ] Cấu trúc thư mục
- [ ] .env.example, .gitignore
- [ ] Apache .htaccess rewrite
- [ ] PDO connection + error handling
- [ ] Router
- [ ] Request/Response helpers
- [ ] Auth, CSRF, Validator core
- [ ] View rendering
- [ ] README.md

### M2 — Schema & Sample Data 🔄
- [ ] Migration SQL files (19 tables)
- [ ] migrate.php script
- [ ] Seed data (4 sports, 25+ categories, 8 brands, 26+ products, 60+ variants)
- [ ] seed.php script
- [ ] Admin user seeding

## Lỗi / Blockers
- Chưa có

## Bước tiếp theo
- Hoàn thành M1 và M2
- Bắt đầu M3 (Design System)
