<?php

namespace App\Controllers;

use App\Core\View;

class SupportController
{
    public function show(string $slug): void
    {
        $pages = [
            'huong-dan-mua-hang' => [
                'title' => 'Hướng dẫn mua hàng',
                'intro' => 'Chọn sản phẩm phù hợp và hoàn tất đơn hàng chỉ với vài bước.',
                'sections' => [
                    ['Tìm sản phẩm', 'Duyệt theo môn thể thao hoặc dùng ô tìm kiếm để tìm sản phẩm bạn muốn.'],
                    ['Chọn và thêm vào giỏ', 'Mở trang sản phẩm, chọn phiên bản và số lượng, sau đó thêm vào giỏ hàng.'],
                    ['Kiểm tra giỏ hàng', 'Vào giỏ hàng để xem lại sản phẩm, điều chỉnh số lượng hoặc bỏ sản phẩm không cần mua.'],
                    ['Đặt hàng', 'Điền thông tin người nhận và địa chỉ giao hàng, kiểm tra tổng tiền rồi xác nhận đặt hàng. Phương thức thanh toán hiện có là thanh toán khi nhận hàng (COD).'],
                    ['Theo dõi đơn hàng', 'Sau khi đặt hàng, lưu mã đơn hàng. Nếu đã đăng nhập, bạn có thể xem đơn trong mục Đơn hàng của tài khoản.'],
                ],
            ],
            'doi-tra' => [
                'title' => 'Chính sách đổi trả',
                'intro' => 'Thông tin về yêu cầu đổi hoặc trả sản phẩm.',
                'sections' => [
                    ['Trước khi gửi yêu cầu', 'Vui lòng giữ lại mã đơn hàng, sản phẩm, bao bì và các phụ kiện đi kèm. Hình ảnh sản phẩm có thể giúp mô tả vấn đề rõ hơn.'],
                    ['Điều kiện đổi trả', 'Điều kiện, thời hạn và chi phí đổi trả đang được cập nhật. Vui lòng xác nhận với cửa hàng trước khi gửi sản phẩm.'],
                    ['Chuẩn bị thông tin', 'Ghi rõ mã đơn hàng, sản phẩm cần hỗ trợ và lý do đổi hoặc trả để cửa hàng có thể kiểm tra.'],
                ],
            ],
            'van-chuyen' => [
                'title' => 'Vận chuyển',
                'intro' => 'Theo dõi thông tin giao nhận cho đơn hàng của bạn.',
                'sections' => [
                    ['Thông tin giao hàng', 'Kiểm tra họ tên, số điện thoại và địa chỉ trước khi xác nhận đơn để tránh nhầm lẫn khi giao nhận.'],
                    ['Phí và thời gian giao hàng', 'Phí vận chuyển được hiển thị trong phần tổng tiền khi đặt hàng. Thời gian giao hàng tùy thuộc vào địa chỉ và quá trình xử lý đơn.'],
                    ['Theo dõi trạng thái', 'Nếu đã đăng nhập, bạn có thể xem trạng thái đơn hàng trong mục Đơn hàng của tài khoản.'],
                ],
            ],
            'lien-he' => [
                'title' => 'Liên hệ',
                'intro' => 'Chuẩn bị thông tin để được hỗ trợ về sản phẩm hoặc đơn hàng.',
                'sections' => [
                    ['Thông tin cần cung cấp', 'Vui lòng chuẩn bị mã đơn hàng, tên sản phẩm và mô tả vấn đề để việc kiểm tra được thuận tiện.'],
                    ['Kênh liên hệ', 'Thông tin liên hệ của cửa hàng đang được cập nhật.'],
                    ['Kiểm tra đơn hàng', 'Nếu đã đăng nhập, bạn có thể xem lại thông tin và trạng thái đơn trong tài khoản.'],
                ],
            ],
        ];

        if (!isset($pages[$slug])) {
            http_response_code(404);
            View::page('errors.404', ['title' => '404 - Không tìm thấy']);
            return;
        }

        View::page('shop.support', [
            'title' => $pages[$slug]['title'] . ' - Sport Shop',
            'page' => $pages[$slug],
            'currentSlug' => $slug,
            'supportPages' => $pages,
        ]);
    }
}
