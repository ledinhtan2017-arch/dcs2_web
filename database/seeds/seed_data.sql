-- =====================================================================
-- Sport Shop - Seed Data
-- Dữ liệu mẫu cho hệ thống Sport Shop
-- =====================================================================

-- Use the database
USE sport_shop;

-- Disable foreign key checks for clean insertion
SET FOREIGN_KEY_CHECKS = 0;

-- ─── SPORTS ─────────────────────────────────────────────────────────
INSERT INTO sports (id, name, slug, description, image_path, sort_order, is_active) VALUES
(1, 'Cầu lông',          'cau-long',         'Dụng cụ và trang phục cầu lông chính hãng',                     'assets/images/sports/poster_caulong.jpg',  1, TRUE),
(2, 'Bóng đá',           'bong-da',          'Giày, áo đấu và phụ kiện bóng đá từ các thương hiệu hàng đầu', 'assets/images/sports/poster_bongda.jpg',    2, TRUE),
(3, 'Bóng rổ',           'bong-ro',          'Bóng rổ, giày và phụ kiện bóng rổ chất lượng cao',              'assets/images/sports/poster_bongro.jpg',   3, TRUE),
(4, 'Chạy bộ / Fitness', 'chay-bo-fitness',  'Giày chạy bộ, quần áo và phụ kiện tập luyện',                   'assets/images/sports/poster_chaybo.jpg',   4, TRUE);

-- ─── CATEGORIES ─────────────────────────────────────────────────────

-- Cầu lông categories (sport_id = 1)
INSERT INTO categories (id, sport_id, parent_id, name, slug, image_path, sort_order, is_active) VALUES
(1,  1, NULL, 'Vợt cầu lông',     'vot-cau-long',     'assets/images/categories/vot-cau-long.jpg',     1, TRUE),
(2,  1, NULL, 'Giày cầu lông',    'giay-cau-long',    'assets/images/categories/giay-cau-long.jpg',    2, TRUE),
(3,  1, NULL, 'Áo cầu lông',      'ao-cau-long',      'assets/images/categories/ao-cau-long.jpg',      3, TRUE),
(4,  1, NULL, 'Quần cầu lông',    'quan-cau-long',    'assets/images/categories/quan-cau-long.jpg',    4, TRUE),
(5,  1, NULL, 'Cầu lông',         'cau-long-qua-cau', 'assets/images/categories/cau-long-qua-cau.jpg', 5, TRUE),
(6,  1, NULL, 'Túi cầu lông',     'tui-cau-long',     'assets/images/categories/tui-cau-long.jpg',     6, TRUE),
(7,  1, NULL, 'Phụ kiện cầu lông','phu-kien-cau-long','assets/images/categories/phu-kien-cau-long.jpg',7, TRUE);

-- Bóng đá categories (sport_id = 2)
INSERT INTO categories (id, sport_id, parent_id, name, slug, image_path, sort_order, is_active) VALUES
(8,  2, NULL, 'Áo đấu bóng đá',   'ao-dau-bong-da',    'assets/images/categories/ao-dau-bong-da.jpg',    1, TRUE),
(9,  2, NULL, 'Giày bóng đá',      'giay-bong-da',      'assets/images/categories/giay-bong-da.jpg',      2, TRUE),
(10, 2, NULL, 'Bóng đá',           'bong-da-qua-bong',  'assets/images/categories/bong-da-qua-bong.jpg',  3, TRUE),
(11, 2, NULL, 'Tất bóng đá',       'tat-bong-da',       'assets/images/categories/tat-bong-da.jpg',       4, TRUE),
(12, 2, NULL, 'Găng tay thủ môn',  'gang-tay-thu-mon',  'assets/images/categories/gang-tay-thu-mon.jpg',  5, TRUE),
(13, 2, NULL, 'Phụ kiện bóng đá',  'phu-kien-bong-da',  'assets/images/categories/phu-kien-bong-da.jpg',  6, TRUE);

-- Bóng rổ categories (sport_id = 3)
INSERT INTO categories (id, sport_id, parent_id, name, slug, image_path, sort_order, is_active) VALUES
(14, 3, NULL, 'Bóng rổ',           'bong-ro-qua-bong',  'assets/images/categories/bong-ro-qua-bong.jpg',  1, TRUE),
(15, 3, NULL, 'Giày bóng rổ',      'giay-bong-ro',      'assets/images/categories/giay-bong-ro.jpg',      2, TRUE),
(16, 3, NULL, 'Áo bóng rổ',        'ao-bong-ro',        'assets/images/categories/ao-bong-ro.jpg',        3, TRUE),
(17, 3, NULL, 'Quần bóng rổ',      'quan-bong-ro',      'assets/images/categories/quan-bong-ro.jpg',      4, TRUE),
(18, 3, NULL, 'Phụ kiện bóng rổ',  'phu-kien-bong-ro',  'assets/images/categories/phu-kien-bong-ro.jpg',  5, TRUE);

-- Chạy bộ / Fitness categories (sport_id = 4)
INSERT INTO categories (id, sport_id, parent_id, name, slug, image_path, sort_order, is_active) VALUES
(19, 4, NULL, 'Giày chạy bộ',      'giay-chay-bo',      'assets/images/categories/giay-chay-bo.jpg',      1, TRUE),
(20, 4, NULL, 'Áo chạy bộ',        'ao-chay-bo',        'assets/images/categories/ao-chay-bo.jpg',        2, TRUE),
(21, 4, NULL, 'Quần chạy bộ',      'quan-chay-bo',      'assets/images/categories/quan-chay-bo.jpg',      3, TRUE),
(22, 4, NULL, 'Bình nước',          'binh-nuoc',         'assets/images/categories/binh-nuoc.jpg',         4, TRUE),
(23, 4, NULL, 'Phụ kiện fitness',   'phu-kien-fitness',  'assets/images/categories/phu-kien-fitness.jpg',  5, TRUE);

-- ─── BRANDS ─────────────────────────────────────────────────────────
INSERT INTO brands (id, name, slug, logo_path, is_active) VALUES
(1, 'Yonex',    'yonex',    'assets/images/brands/yonex.jpg',    TRUE),
(2, 'Victor',   'victor',   'assets/images/brands/victor.jpg',   TRUE),
(3, 'Lining',   'lining',   'assets/images/brands/lining.jpg',   TRUE),
(4, 'Adidas',   'adidas',   'assets/images/brands/adidas.jpg',   TRUE),
(5, 'Nike',     'nike',     'assets/images/brands/nike.jpg',     TRUE),
(6, 'Mizuno',   'mizuno',   'assets/images/brands/mizuno.jpg',   TRUE),
(7, 'Molten',   'molten',   'assets/images/brands/molten.jpg',   TRUE),
(8, 'Spalding', 'spalding', 'assets/images/brands/spalding.jpg', TRUE);

-- ─── PRODUCTS ───────────────────────────────────────────────────────

-- === CẦU LÔNG PRODUCTS (sport_id=1) ===

-- Product 1: Vợt Yonex Astrox 88D Pro
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(1, 1, 1, 'Vợt Yonex Astrox 88D Pro', 'vot-yonex-astrox-88d-pro',
 'Vợt cầu lông Yonex Astrox 88D Pro - Thiết kế cho lối chơi tấn công mạnh mẽ',
 'Vợt Yonex Astrox 88D Pro là dòng vợt cao cấp được thiết kế dành cho những người chơi tấn công. Với công nghệ Rotational Generator System, vợt mang lại cú đập mạnh mẽ và chính xác. Thân vợt được làm từ chất liệu HM Graphite, Nanomesh Neo, Tungsten, kết hợp cùng Namd giúp tăng cường độ uốn cong và phục hồi nhanh chóng. Trọng lượng cân bằng nặng đầu (head heavy) cho lực đập tối đa.',
 'active', TRUE);

-- Product 2: Vợt Victor Thruster K 9900
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(2, 1, 2, 'Vợt Victor Thruster K 9900', 'vot-victor-thruster-k-9900',
 'Vợt cầu lông Victor Thruster K 9900 - Siêu phẩm công thủ toàn diện',
 'Victor Thruster K 9900 là một trong những cây vợt hàng đầu của Victor, được nhiều tay vợt chuyên nghiệp tin dùng. Vợt sử dụng công nghệ PYROFIL và Free Core, mang lại khả năng kiểm soát vượt trội cùng lực đánh mạnh mẽ. Khung vợt Aero-Diamond giảm lực cản gió, tăng tốc độ vung vợt. Phù hợp cho người chơi trung cấp đến chuyên nghiệp.',
 'active', TRUE);

-- Product 3: Giày Yonex Power Cushion 65Z3
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(3, 2, 1, 'Giày Yonex Power Cushion 65Z3', 'giay-yonex-power-cushion-65z3',
 'Giày cầu lông Yonex 65Z3 - Êm ái, bám sân tuyệt vời',
 'Giày cầu lông Yonex Power Cushion 65Z3 là phiên bản mới nhất trong dòng giày thi đấu cao cấp. Công nghệ Power Cushion+ giúp hấp thụ chấn động và chuyển đổi thành năng lượng bật nhảy. Đế giày Radial Blade Sole tăng cường khả năng bám sân trên mọi bề mặt. Trọng lượng nhẹ, thiết kế ôm chân cho sự linh hoạt tối đa trên sân.',
 'active', FALSE);

-- Product 4: Áo Yonex Tournament
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(4, 3, 1, 'Áo Yonex Tournament', 'ao-yonex-tournament',
 'Áo thi đấu cầu lông Yonex Tournament - Thoáng mát, năng động',
 'Áo cầu lông Yonex Tournament được thiết kế với chất liệu vải polyester cao cấp, công nghệ Very Cool Dry giúp thấm hút mồ hôi nhanh chóng và giữ cơ thể luôn khô thoáng. Thiết kế thể thao năng động, phù hợp cho thi đấu và tập luyện. Form áo regular fit, thoải mái trong mọi chuyển động.',
 'active', FALSE);

-- Product 5: Cầu lông Yonex Mavis 350
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(5, 5, 1, 'Cầu lông Yonex Mavis 350', 'cau-long-yonex-mavis-350',
 'Quả cầu lông nhựa Yonex Mavis 350 - Bền bỉ, bay ổn định',
 'Yonex Mavis 350 là dòng cầu lông nhựa được ưa chuộng nhất trên thị trường. Thiết kế với 6 cánh nhựa nylon bền bỉ, quả cầu cho đường bay ổn định và tốc độ gần giống cầu lông lông vũ. Phù hợp cho tập luyện hàng ngày và thi đấu phong trào. Độ bền cao, tiết kiệm chi phí so với cầu lông lông vũ.',
 'active', FALSE);

-- Product 6: Túi vợt Victor BR9213
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(6, 6, 2, 'Túi vợt Victor BR9213', 'tui-vot-victor-br9213',
 'Túi vợt cầu lông Victor BR9213 - Chứa được 12 vợt',
 'Túi vợt Victor BR9213 là dòng túi vợt cao cấp với dung lượng lớn, chứa được tới 12 vợt cầu lông. Có nhiều ngăn riêng biệt cho vợt, giày, quần áo và phụ kiện. Chất liệu vải chống thấm nước, khóa kéo YKK bền bỉ. Quai đeo êm ái, có thể đeo vai hoặc xách tay. Phù hợp cho thi đấu và tập luyện thường xuyên.',
 'active', FALSE);

-- === BÓNG ĐÁ PRODUCTS (sport_id=2) ===

-- Product 7: Giày Adidas Predator Elite FG
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(7, 9, 4, 'Giày Adidas Predator Elite FG', 'giay-adidas-predator-elite-fg',
 'Giày đá bóng Adidas Predator Elite - Kiểm soát bóng tuyệt đối',
 'Adidas Predator Elite FG là đỉnh cao của dòng giày kiểm soát bóng. Upper được phủ lớp Hybridtouch mang lại cảm giác chạm bóng mượt mà và kiểm soát chính xác. Đế FG (Firm Ground) với đinh nhựa xoắn ốc tăng khả năng bám sân trên mặt cỏ tự nhiên. Công nghệ Controlframe ổn định bàn chân trong mọi tình huống xoay chuyển.',
 'active', TRUE);

-- Product 8: Áo đấu đội tuyển Việt Nam 2024
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(8, 8, NULL, 'Áo đấu đội tuyển Việt Nam 2024', 'ao-dau-doi-tuyen-viet-nam-2024',
 'Áo đấu chính thức đội tuyển Việt Nam 2024 - Màu đỏ truyền thống',
 'Áo đấu chính thức đội tuyển quốc gia Việt Nam phiên bản 2024. Thiết kế với màu đỏ truyền thống, in logo liên đoàn và ngôi sao vàng. Chất liệu vải Dri-FIT thoáng khí, thấm hút mồ hôi nhanh. Form áo slim fit ôm gọn, tôn dáng. Phù hợp cho cổ vũ, sưu tập và thi đấu phong trào.',
 'active', TRUE);

-- Product 9: Bóng đá Adidas Al Rihla Pro
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(9, 10, 4, 'Bóng đá Adidas Al Rihla Pro', 'bong-da-adidas-al-rihla-pro',
 'Bóng đá Adidas Al Rihla Pro - Phiên bản thi đấu chính thức',
 'Adidas Al Rihla Pro là phiên bản bóng đá thi đấu chính thức. Bóng được sản xuất với công nghệ CTR-CORE giữ hình dáng và độ nảy ổn định. Lớp vỏ Speedshell PU textured 20 mảnh đảm bảo độ chính xác và ổn định khi di chuyển trong không khí. Đường may đúc nhiệt giúp bóng chống thấm nước. FIFA Quality Pro.',
 'active', FALSE);

-- Product 10: Tất bóng đá Adidas Performance
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(10, 11, 4, 'Tất bóng đá Adidas Performance', 'tat-bong-da-adidas-performance',
 'Tất bóng đá Adidas Performance - Êm chân, chống trượt',
 'Tất bóng đá Adidas Performance với công nghệ Aeroready giúp quản lý độ ẩm, giữ đôi chân luôn khô thoáng suốt trận đấu. Đệm dày ở gót và mũi chân chống va đập. Thiết kế dài qua gối, có dải elastic giữ tất không bị tuột. Chất liệu cotton pha polyester co giãn tốt.',
 'active', FALSE);

-- Product 11: Găng tay thủ môn Adidas Predator Pro
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(11, 12, 4, 'Găng tay thủ môn Adidas Predator Pro', 'gang-tay-thu-mon-adidas-predator-pro',
 'Găng tay thủ môn Adidas Predator Pro - Bắt dính tuyệt vời',
 'Găng tay thủ môn Adidas Predator Pro với lớp URG 2.0 (Ultimate Grip) mang lại khả năng bắt dính vượt trội trong mọi điều kiện thời tiết. Thiết kế negative cut ôm sát ngón tay, tăng diện tích tiếp xúc với bóng. Dây đai cổ tay rộng với khóa dán điều chỉnh độ vừa vặn. Lưng găng tay bằng vải mesh thoáng khí.',
 'active', FALSE);

-- Product 12: Giày Mizuno Morelia Neo III
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(12, 9, 6, 'Giày Mizuno Morelia Neo III', 'giay-mizuno-morelia-neo-iii',
 'Giày đá bóng Mizuno Morelia Neo III - Da kangaroo mềm mại',
 'Mizuno Morelia Neo III là dòng giày đá bóng cao cấp với upper bằng da kangaroo (K-leather) siêu mềm mại, mang lại cảm giác chạm bóng tự nhiên nhất. Đế giày nhẹ với đinh nhựa conical truyền thống tăng khả năng bám sân. Công nghệ Mizuno Wave ở gót chân giúp giảm chấn và ổn định. Lựa chọn hàng đầu cho người chơi yêu thích cảm giác chạm bóng thuần túy.',
 'active', TRUE);

-- === BÓNG RỔ PRODUCTS (sport_id=3) ===

-- Product 13: Bóng rổ Spalding TF-1000 Legacy
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(13, 14, 8, 'Bóng rổ Spalding TF-1000 Legacy', 'bong-ro-spalding-tf-1000-legacy',
 'Bóng rổ Spalding TF-1000 Legacy - Chất lượng thi đấu chuyên nghiệp',
 'Spalding TF-1000 Legacy là dòng bóng rổ thi đấu cao cấp, được sử dụng trong nhiều giải đấu chuyên nghiệp. Vỏ bóng bằng da tổng hợp ZK Microfiber Composite mang lại cảm giác cầm nắm tuyệt vời. Công nghệ Dual Density Cushion Core đảm bảo độ nảy đều và ổn định. Phù hợp cho sàn gỗ indoor.',
 'active', TRUE);

-- Product 14: Bóng rổ Molten GG7X
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(14, 14, 7, 'Bóng rổ Molten GG7X', 'bong-ro-molten-gg7x',
 'Bóng rổ Molten GG7X - Bóng thi đấu FIBA chính thức',
 'Molten GG7X (BGG7X) là bóng thi đấu chính thức FIBA, được sử dụng trong các giải đấu quốc tế. Thiết kế 12 mảnh da composite GIUGIARO với đường rãnh sâu giúp cầm nắm chắc chắn. Lõi bóng butyl giữ hơi lâu, độ nảy chuẩn. Trọng lượng và chu vi đạt tiêu chuẩn FIBA cho thi đấu chính thức.',
 'active', FALSE);

-- Product 15: Giày bóng rổ Lining Way of Wade
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(15, 15, 3, 'Giày bóng rổ Lining Way of Wade', 'giay-bong-ro-lining-way-of-wade',
 'Giày bóng rổ Lining Way of Wade - Hiệu năng sân đấu vượt trội',
 'Lining Way of Wade là dòng giày bóng rổ signature cao cấp, kết hợp giữa hiệu năng thi đấu và phong cách thời trang. Đệm Cloud giảm chấn mạnh mẽ, bảo vệ khớp gối khi tiếp đất. Upper bằng vải dệt kỹ thuật ôm chân nhưng thoáng khí. Đế cao su trong suốt với pattern herringbone bám sân indoor tuyệt vời.',
 'active', TRUE);

-- Product 16: Áo bóng rổ training
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(16, 16, NULL, 'Áo bóng rổ training', 'ao-bong-ro-training',
 'Áo bóng rổ tập luyện - Thoáng mát, dễ vận động',
 'Áo bóng rổ training với thiết kế sát nách truyền thống, tối ưu cho sự vận động tự do trên sân. Chất liệu vải mesh polyester 100% thoáng khí, nhanh khô. Đường may phẳng không gây cọ xát da. Form áo regular fit phù hợp cho tập luyện và thi đấu phong trào.',
 'active', FALSE);

-- Product 17: Quần bóng rổ training
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(17, 17, NULL, 'Quần bóng rổ training', 'quan-bong-ro-training',
 'Quần bóng rổ tập luyện - Rộng rãi, thoải mái',
 'Quần bóng rổ training với form rộng truyền thống, dài qua gối. Chất liệu polyester nhẹ, nhanh khô, thoáng khí. Lưng quần elastic với dây rút điều chỉnh. Hai túi bên tiện lợi. Thiết kế đơn giản, phù hợp cho tập luyện và thi đấu phong trào.',
 'active', FALSE);

-- Product 18: Băng đầu gối bóng rổ
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(18, 18, NULL, 'Băng đầu gối bóng rổ', 'bang-dau-goi-bong-ro',
 'Băng bảo vệ đầu gối cho bóng rổ - Chống va đập',
 'Băng bảo vệ đầu gối chuyên dụng cho bóng rổ với lớp đệm EVA dày chống va đập khi ngã hoặc va chạm. Chất liệu vải co giãn 4 chiều ôm gọn đầu gối mà không hạn chế vận động. Thiết kế anti-slip bên trong giữ băng không bị tuột. Thoáng khí, giặt máy được.',
 'active', FALSE);

-- === CHẠY BỘ / FITNESS PRODUCTS (sport_id=4) ===

-- Product 19: Giày Adidas Ultraboost Light
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(19, 19, 4, 'Giày Adidas Ultraboost Light', 'giay-adidas-ultraboost-light',
 'Giày chạy bộ Adidas Ultraboost Light - Nhẹ nhất từ trước đến nay',
 'Adidas Ultraboost Light là phiên bản nhẹ nhất trong lịch sử dòng Ultraboost. Đệm Light BOOST mang lại cảm giác êm ái, đàn hồi cao và phản hồi năng lượng tuyệt vời. Upper Primeknit+ ôm chân thoáng khí. Đế Continental Rubber bám đường trong mọi điều kiện thời tiết. Thiết kế phù hợp cho chạy bộ hàng ngày và thi đấu.',
 'active', TRUE);

-- Product 20: Giày Mizuno Wave Rider 27
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(20, 19, 6, 'Giày Mizuno Wave Rider 27', 'giay-mizuno-wave-rider-27',
 'Giày chạy bộ Mizuno Wave Rider 27 - Êm ái, ổn định',
 'Mizuno Wave Rider 27 là dòng giày chạy bộ trung tính (neutral) nổi tiếng của Mizuno. Công nghệ Mizuno ENERZY mang lại đệm mềm mại và phản hồi năng lượng. Tấm Wave Plate phân tán lực va chạm, mang lại sự ổn định khi chạy. Upper bằng vải Jacquard mesh thoáng khí. Phù hợp cho chạy bộ hàng ngày và chạy marathon.',
 'active', FALSE);

-- Product 21: Áo chạy bộ Adidas Own The Run
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(21, 20, 4, 'Áo chạy bộ Adidas Own The Run', 'ao-chay-bo-adidas-own-the-run',
 'Áo chạy bộ Adidas Own The Run - AEROREADY thoáng khí',
 'Áo chạy bộ Adidas Own The Run với công nghệ AEROREADY quản lý độ ẩm, giữ cơ thể khô thoáng. Chất liệu vải tái chế 100% polyester nhẹ và mềm mại. Dải phản quang ở lưng tăng khả năng nhìn thấy khi chạy ban đêm. Form áo regular fit không quá ôm, thoải mái trong mọi chuyển động.',
 'active', FALSE);

-- Product 22: Quần chạy bộ Adidas Marathon
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(22, 21, 4, 'Quần chạy bộ Adidas Marathon', 'quan-chay-bo-adidas-marathon',
 'Quần short chạy bộ Adidas Marathon - Nhẹ, nhanh khô',
 'Quần short chạy bộ Adidas Marathon 20 được thiết kế cho tốc độ. Chất liệu AEROREADY siêu nhẹ, nhanh khô, thoáng khí. Lưng quần elastic với dây rút bên trong. Xẻ tà hai bên cho bước chạy thoải mái. Túi nhỏ phía sau đựng chìa khóa hoặc gel năng lượng. Form slim fit.',
 'active', FALSE);

-- Product 23: Bình nước thể thao 750ml
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(23, 22, NULL, 'Bình nước thể thao 750ml', 'binh-nuoc-the-thao-750ml',
 'Bình nước thể thao 750ml - Tiện lợi, bền bỉ',
 'Bình nước thể thao dung tích 750ml, thiết kế tiện lợi cho tập luyện và thi đấu. Chất liệu nhựa Tritan BPA-free an toàn cho sức khỏe. Nắp bật một chạm dễ dàng uống khi đang vận động. Thân bình có lớp nhám chống trượt. Có thể rửa bằng máy rửa chén. Đường kính vừa với hầu hết giá đỡ bình xe đạp.',
 'active', FALSE);

-- Product 24: Đai chạy bộ đeo hông
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(24, 23, NULL, 'Đai chạy bộ đeo hông', 'dai-chay-bo-deo-hong',
 'Đai đeo hông chạy bộ - Đựng điện thoại, chìa khóa',
 'Đai chạy bộ đeo hông thiết kế mỏng nhẹ, ôm sát cơ thể không bị xóc nảy khi chạy. Ngăn chính đựng được điện thoại đến 6.7 inch. Ngăn phụ đựng chìa khóa, thẻ, gel năng lượng. Chất liệu lycra co giãn chống thấm mồ hôi. Dây đai elastic điều chỉnh được, phù hợp vòng eo 60-110cm.',
 'active', FALSE);

-- Product 25: Vớ chạy bộ chống trượt
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(25, 23, NULL, 'Vớ chạy bộ chống trượt', 'vo-chay-bo-chong-truot',
 'Vớ chạy bộ chống trượt - Đệm êm, thoáng khí',
 'Vớ chạy bộ chuyên dụng với các hạt silicon chống trượt ở lòng bàn chân. Đệm dày ở gót và mũi chân chống va đập. Vùng giữa bàn chân có dải elastic nén nhẹ hỗ trợ vòm. Chất liệu cotton pha coolmax thoáng khí, nhanh khô. Thiết kế dài ngang mắt cá chân.',
 'active', FALSE);

-- Product 26: Giày Lining Feidian 4
INSERT INTO products (id, category_id, brand_id, name, slug, short_description, description, status, is_featured) VALUES
(26, 19, 3, 'Giày Lining Feidian 4', 'giay-lining-feidian-4',
 'Giày chạy bộ Lining Feidian 4 - Carbon plate siêu nhẹ',
 'Lining Feidian 4 (飛電4) là giày chạy bộ racing flat cao cấp với tấm carbon plate nguyên bản. Đệm giữa BOOM foam siêu nhẹ với độ phản hồi năng lượng lên đến 80%. Upper ultra-thin mesh chỉ nặng khoảng 168g (size 42). Thiết kế dành cho chạy tốc độ cao từ 10K đến marathon. Lựa chọn hàng đầu của nhiều vận động viên Trung Quốc tại các giải chạy lớn.',
 'active', TRUE);

-- ─── PRODUCT-SPORT RELATIONSHIPS ────────────────────────────────────
INSERT INTO product_sports (product_id, sport_id) VALUES
-- Cầu lông products → sport 1
(1, 1), (2, 1), (3, 1), (4, 1), (5, 1), (6, 1),
-- Bóng đá products → sport 2
(7, 2), (8, 2), (9, 2), (10, 2), (11, 2), (12, 2),
-- Bóng rổ products → sport 3
(13, 3), (14, 3), (15, 3), (16, 3), (17, 3), (18, 3),
-- Chạy bộ / Fitness products → sport 4
(19, 4), (20, 4), (21, 4), (22, 4), (23, 4), (24, 4), (25, 4), (26, 4);

-- ─── PRODUCT VARIANTS ───────────────────────────────────────────────

-- Product 1: Vợt Yonex Astrox 88D Pro (2 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(1,  1, 'YNX-AX88D-3U', '3U', NULL, 890000, NULL, 15),
(2,  1, 'YNX-AX88D-4U', '4U', NULL, 890000, NULL, 20);

-- Product 2: Vợt Victor Thruster K 9900 (2 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(3,  2, 'VIC-TK9900-3U', '3U', NULL, 1250000, NULL, 10),
(4,  2, 'VIC-TK9900-4U', '4U', NULL, 1250000, NULL, 12);

-- Product 3: Giày Yonex Power Cushion 65Z3 (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(5,  3, 'YNX-65Z3-40', '40', NULL, 1890000, NULL, 8),
(6,  3, 'YNX-65Z3-41', '41', NULL, 1890000, NULL, 12),
(7,  3, 'YNX-65Z3-42', '42', NULL, 1890000, NULL, 10);

-- Product 4: Áo Yonex Tournament (4 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(8,  4, 'YNX-TOUR-S',  'S',  NULL, 450000, NULL, 25),
(9,  4, 'YNX-TOUR-M',  'M',  NULL, 450000, NULL, 30),
(10, 4, 'YNX-TOUR-L',  'L',  NULL, 450000, NULL, 30),
(11, 4, 'YNX-TOUR-XL', 'XL', NULL, 450000, NULL, 20);

-- Product 5: Cầu lông Yonex Mavis 350 (2 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(12, 5, 'YNX-MV350-6',  'Hộp 6 quả',  NULL, 180000, NULL, 50),
(13, 5, 'YNX-MV350-12', 'Hộp 12 quả', NULL, 340000, NULL, 35);

-- Product 6: Túi vợt Victor BR9213 (2 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(14, 6, 'VIC-BR9213-BLK', NULL, 'Đen',  750000, NULL, 15),
(15, 6, 'VIC-BR9213-BLU', NULL, 'Xanh', 750000, NULL, 10);

-- Product 7: Giày Adidas Predator Elite FG (4 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(16, 7, 'ADI-PRED-40', '40', NULL, 3200000, NULL, 8),
(17, 7, 'ADI-PRED-41', '41', NULL, 3200000, NULL, 12),
(18, 7, 'ADI-PRED-42', '42', NULL, 3200000, NULL, 10),
(19, 7, 'ADI-PRED-43', '43', NULL, 3200000, NULL, 7);

-- Product 8: Áo đấu đội tuyển Việt Nam 2024 (4 variants, with sale_price)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(20, 8, 'VN24-S',  'S',  'Đỏ', 550000, 450000, 40),
(21, 8, 'VN24-M',  'M',  'Đỏ', 550000, 450000, 50),
(22, 8, 'VN24-L',  'L',  'Đỏ', 550000, 450000, 45),
(23, 8, 'VN24-XL', 'XL', 'Đỏ', 550000, 450000, 30);

-- Product 9: Bóng đá Adidas Al Rihla Pro (1 variant)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(24, 9, 'ADI-RIHLA-5', 'Size 5', NULL, 1450000, NULL, 20);

-- Product 10: Tất bóng đá Adidas Performance (2 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(25, 10, 'ADI-SOCK-SM', '39-42', 'Trắng', 120000, NULL, 50),
(26, 10, 'ADI-SOCK-LG', '43-46', 'Trắng', 120000, NULL, 40);

-- Product 11: Găng tay thủ môn Adidas Predator Pro (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(27, 11, 'ADI-GK-8',  'Size 8',  NULL, 1800000, NULL, 10),
(28, 11, 'ADI-GK-9',  'Size 9',  NULL, 1800000, NULL, 12),
(29, 11, 'ADI-GK-10', 'Size 10', NULL, 1800000, NULL, 8);

-- Product 12: Giày Mizuno Morelia Neo III (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(30, 12, 'MIZ-MORL-40', '40', NULL, 2800000, NULL, 6),
(31, 12, 'MIZ-MORL-41', '41', NULL, 2800000, NULL, 10),
(32, 12, 'MIZ-MORL-42', '42', NULL, 2800000, NULL, 8);

-- Product 13: Bóng rổ Spalding TF-1000 Legacy (2 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(33, 13, 'SPD-TF1000-7', 'Size 7', NULL, 1290000, NULL, 15),
(34, 13, 'SPD-TF1000-6', 'Size 6', NULL, 1190000, NULL, 10);

-- Product 14: Bóng rổ Molten GG7X (1 variant)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(35, 14, 'MOL-GG7X-7', 'Size 7', NULL, 850000, NULL, 25);

-- Product 15: Giày bóng rổ Lining Way of Wade (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(36, 15, 'LIN-WOW-41', '41', NULL, 2950000, NULL, 8),
(37, 15, 'LIN-WOW-42', '42', NULL, 2950000, NULL, 12),
(38, 15, 'LIN-WOW-43', '43', NULL, 2950000, NULL, 10);

-- Product 16: Áo bóng rổ training (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(39, 16, 'BB-TANK-S', 'S', 'Trắng', 350000, NULL, 30),
(40, 16, 'BB-TANK-M', 'M', 'Trắng', 350000, NULL, 35),
(41, 16, 'BB-TANK-L', 'L', 'Trắng', 350000, NULL, 30);

-- Product 17: Quần bóng rổ training (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(42, 17, 'BB-SHORT-S', 'S', 'Đen', 280000, NULL, 25),
(43, 17, 'BB-SHORT-M', 'M', 'Đen', 280000, NULL, 30),
(44, 17, 'BB-SHORT-L', 'L', 'Đen', 280000, NULL, 25);

-- Product 18: Băng đầu gối bóng rổ (1 variant)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(45, 18, 'BB-KNEE-FS', 'Free size', 'Đen', 150000, NULL, 40);

-- Product 19: Giày Adidas Ultraboost Light (3 variants, with sale_price)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(46, 19, 'ADI-UBL-40', '40', 'Đen', 3500000, 2990000, 10),
(47, 19, 'ADI-UBL-41', '41', 'Đen', 3500000, 2990000, 15),
(48, 19, 'ADI-UBL-42', '42', 'Đen', 3500000, 2990000, 12);

-- Product 20: Giày Mizuno Wave Rider 27 (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(49, 20, 'MIZ-WR27-40', '40', NULL, 2690000, NULL, 8),
(50, 20, 'MIZ-WR27-41', '41', NULL, 2690000, NULL, 12),
(51, 20, 'MIZ-WR27-42', '42', NULL, 2690000, NULL, 10);

-- Product 21: Áo chạy bộ Adidas Own The Run (4 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(52, 21, 'ADI-OTR-S-BK',  'S', 'Đen',   650000, NULL, 20),
(53, 21, 'ADI-OTR-M-BK',  'M', 'Đen',   650000, NULL, 25),
(54, 21, 'ADI-OTR-L-BK',  'L', 'Đen',   650000, NULL, 20),
(55, 21, 'ADI-OTR-M-WH',  'M', 'Trắng', 650000, NULL, 18);

-- Product 22: Quần chạy bộ Adidas Marathon (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(56, 22, 'ADI-MARA-S', 'S', 'Đen', 520000, NULL, 15),
(57, 22, 'ADI-MARA-M', 'M', 'Đen', 520000, NULL, 20),
(58, 22, 'ADI-MARA-L', 'L', 'Đen', 520000, NULL, 15);

-- Product 23: Bình nước thể thao 750ml (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(59, 23, 'BTL-750-BK', NULL, 'Đen',   180000, NULL, 30),
(60, 23, 'BTL-750-WH', NULL, 'Trắng', 180000, NULL, 25),
(61, 23, 'BTL-750-BL', NULL, 'Xanh',  180000, NULL, 20);

-- Product 24: Đai chạy bộ đeo hông (2 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(62, 24, 'BELT-FS-BK', 'Free size', 'Đen',  290000, NULL, 35),
(63, 24, 'BELT-FS-GR', 'Free size', 'Xám',  290000, NULL, 25);

-- Product 25: Vớ chạy bộ chống trượt (2 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(64, 25, 'SOCK-RUN-SM', '39-42', NULL, 95000, NULL, 45),
(65, 25, 'SOCK-RUN-LG', '43-46', NULL, 95000, NULL, 35);

-- Product 26: Giày Lining Feidian 4 (3 variants)
INSERT INTO product_variants (id, product_id, sku, size_label, color_name, price, sale_price, stock_quantity) VALUES
(66, 26, 'LIN-FD4-41', '41', NULL, 3200000, NULL, 5),
(67, 26, 'LIN-FD4-42', '42', NULL, 3200000, NULL, 8),
(68, 26, 'LIN-FD4-43', '43', NULL, 3200000, NULL, 6);

-- ─── PRODUCT IMAGES ─────────────────────────────────────────────────
-- One primary image per product
INSERT INTO product_images (product_id, variant_id, image_path, alt_text, sort_order, is_primary) VALUES
(1,  NULL, 'assets/images/products/Yonex_astrox_88d.jpg', 'Vợt Yonex Astrox 88D Pro',             1, TRUE),
(2,  NULL, 'assets/images/products/Victor_Thruster_K_9900.jpg', 'Vợt Victor Thruster K 9900',            1, TRUE),
(3,  NULL, 'assets/images/products/Yonex_Power_Cushion_65Z3.jpg', 'Giày Yonex Power Cushion 65Z3',         1, TRUE),
(4,  NULL, 'assets/images/products/Yonex_Tournament.jpg', 'Áo Yonex Tournament',                   1, TRUE),
(5,  NULL, 'assets/images/products/yonex_mavis_350.jpg', 'Cầu lông Yonex Mavis 350',              1, TRUE),
(6,  NULL, 'assets/images/products/Victor_BR9213.jpg', 'Túi vợt Victor BR9213',                 1, TRUE),
(7,  NULL, 'assets/images/products/Adidas_Predator_Elite_FG.jpg', 'Giày Adidas Predator Elite FG',          1, TRUE),
(8,  NULL, 'assets/images/products/ao_vietnam.jpg', 'Áo đấu đội tuyển Việt Nam 2024',        1, TRUE),
(9,  NULL, 'assets/images/products/bong_adidas_wc22.jpg', 'Bóng đá Adidas Al Rihla Pro',            1, TRUE),
(10, NULL, 'assets/images/products/Tat_bong_da.jpg', 'Tất bóng đá Adidas Performance',         1, TRUE),
(11, NULL, 'assets/images/products/adidas_pridator_pro.jpg', 'Găng tay thủ môn Adidas Predator Pro',   1, TRUE),
(12, NULL, 'assets/images/products/Mizuno_Morelia_NeoIII.jpg', 'Giày Mizuno Morelia Neo III',             1, TRUE),
(13, NULL, 'assets/images/products/Spalding_TF1000_Legacy.jpg', 'Bóng rổ Spalding TF-1000 Legacy',        1, TRUE),
(14, NULL, 'assets/images/products/Molten_GG7X.jpg', 'Bóng rổ Molten GG7X',                    1, TRUE),
(15, NULL, 'assets/images/products/way_of_wade.jpg', 'Giày bóng rổ Lining Way of Wade',        1, TRUE),
(16, NULL, 'assets/images/products/ao_bong_ro.jpg', 'Áo bóng rổ training',                    1, TRUE),
(17, NULL, 'assets/images/products/quan_bong_ro.jpg', 'Quần bóng rổ training',                  1, TRUE),
(18, NULL, 'assets/images/products/Bang_dau_goi.jpg', 'Băng đầu gối bóng rổ',                   1, TRUE),
(19, NULL, 'assets/images/products/adidas_ultraboost_light.jpg', 'Giày Adidas Ultraboost Light',            1, TRUE),
(20, NULL, 'assets/images/products/Wave_Rider_27.jpg', 'Giày Mizuno Wave Rider 27',               1, TRUE),
(21, NULL, 'assets/images/products/Ao_chay_bo.jpg', 'Áo chạy bộ Adidas Own The Run',          1, TRUE),
(22, NULL, 'assets/images/products/Quan_Adidas_Marathon.jpg', 'Quần chạy bộ Adidas Marathon',            1, TRUE),
(23, NULL, 'assets/images/products/Binh_nuoc.jpg', 'Bình nước thể thao 750ml',                1, TRUE),
(24, NULL, 'assets/images/products/Dai_chay_bo.jpg', 'Đai chạy bộ đeo hông',                    1, TRUE),
(25, NULL, 'assets/images/products/Tat_chay_bo.jpg', 'Vớ chạy bộ chống trượt',                  1, TRUE),
(26, NULL, 'assets/images/products/Feidian_4.jpg', 'Giày Lining Feidian 4',                   1, TRUE);

-- ─── SAMPLE BANNERS ─────────────────────────────────────────────────
INSERT INTO banners (title, subtitle, image_path, link_url, position, sort_order, is_active) VALUES
('Mùa hè sôi động cùng thể thao', 'Giảm giá đến 30% tất cả sản phẩm', 'assets/images/banners/hero-summer.jpg', '/san-pham', 'hero', 1, TRUE),
('Bộ sưu tập cầu lông 2024', 'Vợt, giày, phụ kiện chính hãng Yonex & Victor', 'assets/images/banners/hero-badminton.jpg', '/mon-the-thao/cau-long', 'hero', 2, TRUE),
('Giày chạy bộ mới nhất', 'Adidas Ultraboost, Mizuno Wave Rider & Lining Feidian', 'assets/images/banners/hero-running.jpg', '/mon-the-thao/chay-bo-fitness', 'hero', 3, TRUE);

-- ─── SAMPLE COUPONS ─────────────────────────────────────────────────
INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, max_discount, starts_at, ends_at, usage_limit, is_active) VALUES
('CHAOBAN10', 'percentage', 10, 500000, 100000, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 1000, TRUE),
('GIAM50K',   'fixed',      50000, 300000, NULL,   '2024-01-01 00:00:00', '2025-12-31 23:59:59', 500,  TRUE),
('SPORT20',   'percentage', 20, 1000000, 200000, '2024-06-01 00:00:00', '2025-06-30 23:59:59', 200,  TRUE);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
