# Quyết định thiết kế — Sport Shop

> Ghi lại các quyết định kỹ thuật và thiết kế không có trong spec.

## Kiến trúc

| # | Quyết định | Lý do | Ngày |
|---|-----------|-------|------|
| D01 | Dùng simple file-based autoloader thay vì Composer PSR-4 | Giảm dependency, đơn giản hóa setup XAMPP | 2026-10-31 |
| D02 | Router tự viết với regex matching | Không dùng framework, đủ cho monolith PHP | 2026-10-31 |
| D03 | Session-based auth, không JWT | Phù hợp server-rendered app, đơn giản và an toàn | 2026-10-31 |
| D04 | Guest cart dùng session token (HttpOnly cookie) | Không localStorage, tránh XSS lộ cart data | 2026-10-31 |
| D05 | Giá VND dùng DECIMAL(12,0) | Không float để tránh lỗi làm tròn; VND không có xu | 2026-10-31 |
| D06 | Font thay thế: Inter (body) + Bebas Neue (display) | Thay Nike Futura ND/Helvetica Now (proprietary) | 2026-10-31 |
| D07 | Cart merge khi login: cộng số lượng, cap theo stock | Trải nghiệm tốt hơn cho guest chuyển customer | 2026-10-31 |
| D08 | Order code format: SS-YYYYMMDD-XXXXX (random 5 chars) | Không expose sequential ID; dễ đọc | 2026-10-31 |
| D09 | Hoàn kho khi cancel: chỉ hoàn 1 lần, check flag | Tránh double-refund stock | 2026-10-31 |
| D10 | Placeholder images cho MVP, thay bằng AI-generated images khi có | Không block development vì thiếu ảnh | 2026-10-31 |
| D11 | Checkout form: tên, phone, tỉnh/thành, quận/huyện, phường/xã, địa chỉ chi tiết | Phù hợp VN address format | 2026-10-31 |
| D12 | Shipping fee: flat rate cấu hình trong .env (mặc định 30,000₫) | Đơn giản cho MVP; P2 mới tích hợp API vận chuyển | 2026-10-31 |
| D13 | Admin UI cùng bảng màu nhưng layout đơn giản hơn, ưu tiên data table | Theo spec: không ép campaign design vào admin | 2026-10-31 |

## UI States (bổ sung cho DESIGN.md)

| State | Xử lý |
|-------|--------|
| Loading | Skeleton placeholder hoặc spinner tối giản trên nền soft-cloud |
| Empty | Icon + text mô tả + CTA gợi ý (ví dụ: "Chưa có sản phẩm nào") |
| Error | Toast notification đỏ nhẹ ở top-right; form inline error dưới field |
| Disabled | Opacity 0.5, cursor not-allowed, không submit |
| Focus | 2px ink outline + 2px white gap (WCAG visible focus) |
| Success | Toast xanh success ở top-right, auto-dismiss 3s |
| Validation | Inline errors đỏ dưới field, border đỏ, aria-invalid |
