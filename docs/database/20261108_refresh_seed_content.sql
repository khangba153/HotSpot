-- Cập nhật seed 07/11/2026 sang nội dung 08/11/2026.

-- Sao lưu trước khi chạy. Không xóa địa điểm hoặc đổi owner/status/password/email.

-- Chỉ khớp địa điểm theo email tài khoản seed và tên cũ/tên mới; không dùng ID cố định.

SET NAMES utf8mb4;

START TRANSACTION;

UPDATE users SET full_name='Nguyễn Hoàng Nam', bio='Quản lý nội dung và hỗ trợ cộng đồng Hot Spot.' WHERE email='demo-20261107-admin@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Nguyễn Hoàng Nam');

UPDATE users SET full_name='Nguyễn Minh Anh', bio='Thích khám phá các góc phố và lưu lại những điểm dừng chân thú vị.' WHERE email='demo-20261107-member01@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Nguyễn Minh Anh');

UPDATE users SET full_name='Trần Gia Huy', bio='Quan tâm đến ẩm thực địa phương và những chuyến đi cùng bạn bè.' WHERE email='demo-20261107-member02@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Trần Gia Huy');

UPDATE users SET full_name='Lê Phương Linh', bio='Yêu kiến trúc, cảnh quan và những câu chuyện văn hóa ở mỗi nơi ghé qua.' WHERE email='demo-20261107-member03@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Lê Phương Linh');

UPDATE users SET full_name='Phạm Quốc Bảo', bio='Chia sẻ các địa điểm đáng ghé trong những chuyến đi cuối tuần.' WHERE email='demo-20261107-member04@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Phạm Quốc Bảo');

UPDATE users SET full_name='Hoàng Ngọc Hà', bio='Thích khám phá các góc phố và lưu lại những điểm dừng chân thú vị.' WHERE email='demo-20261107-member05@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Hoàng Ngọc Hà');

UPDATE users SET full_name='Vũ Đức Minh', bio='Quan tâm đến ẩm thực địa phương và những chuyến đi cùng bạn bè.' WHERE email='demo-20261107-member06@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Vũ Đức Minh');

UPDATE users SET full_name='Đặng Thanh Trúc', bio='Yêu kiến trúc, cảnh quan và những câu chuyện văn hóa ở mỗi nơi ghé qua.' WHERE email='demo-20261107-member07@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Đặng Thanh Trúc');

UPDATE users SET full_name='Bùi Tuấn Kiệt', bio='Chia sẻ các địa điểm đáng ghé trong những chuyến đi cuối tuần.' WHERE email='demo-20261107-member08@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Bùi Tuấn Kiệt');

UPDATE users SET full_name='Đỗ Khánh Vy', bio='Thích khám phá các góc phố và lưu lại những điểm dừng chân thú vị.' WHERE email='demo-20261107-member09@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Đỗ Khánh Vy');

UPDATE users SET full_name='Nguyễn Hoài Nam', bio='Quan tâm đến ẩm thực địa phương và những chuyến đi cùng bạn bè.' WHERE email='demo-20261107-member10@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Nguyễn Hoài Nam');

UPDATE users SET full_name='Trần Bảo Ngọc', bio='Yêu kiến trúc, cảnh quan và những câu chuyện văn hóa ở mỗi nơi ghé qua.' WHERE email='demo-20261107-member11@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Trần Bảo Ngọc');

UPDATE users SET full_name='Lê Nhật Quang', bio='Chia sẻ các địa điểm đáng ghé trong những chuyến đi cuối tuần.' WHERE email='demo-20261107-member12@hotspot.test' AND (full_name LIKE 'Demo %' OR full_name='Lê Nhật Quang');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member01@hotspot.test') AND places.title IN ('Phố cổ Hội An','Phố cổ Hội An') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='48',title='Phố cổ Hội An',description='Khu phố cổ ven sông Hoài, nổi bật với nhà mái ngói, những mặt tiền tông vàng và các tuyến phố đi bộ. Có thể kết hợp ngắm kiến trúc, dạo ven sông và khám phá đời sống địa phương.',address='Khu phố cổ Hội An, Đà Nẵng',latitude='15.877135',longitude='108.328631' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/pho-co-hoi-an.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/pho-co-hoi-an.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/b456ddca004e60718c.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/b456ddca004e60718c.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cau-rong-da-nang.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cau-rong-da-nang.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member02@hotspot.test') AND places.title IN ('Vịnh Hạ Long','Vịnh Hạ Long') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'22' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='22',title='Vịnh Hạ Long',description='Vịnh biển có nhiều đảo đá vôi, mặt nước xanh và cảnh quan đặc trưng của Quảng Ninh. Vị trí trên bản đồ là điểm tham chiếu trong khu vực vịnh; cần chọn bến phù hợp với lịch trình tham quan.',address='Vịnh Hạ Long, Quảng Ninh',latitude='20.9',longitude='107.2' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/vinh-ha-long.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/vinh-ha-long.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/cb602e1757e3c3cdde.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/cb602e1757e3c3cdde.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/778de4e888dddf09fb.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/778de4e888dddf09fb.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Nên xem trước đường đi và chọn thời điểm phù hợp.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member03@hotspot.test') AND places.title IN ('Cầu Rồng Đà Nẵng','Cầu Rồng Đà Nẵng') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='48',title='Cầu Rồng Đà Nẵng',description='Cầu bắc qua sông Hàn với thiết kế hình rồng, một điểm nhận diện của Đà Nẵng.',address='Cầu Rồng, Đà Nẵng',latitude='16.0611111',longitude='108.2266667' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cau-rong-da-nang.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cau-rong-da-nang.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/b4778f40c0a0bfdf8f.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/b4778f40c0a0bfdf8f.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/fc321acc6f65b6d409.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/fc321acc6f65b6d409.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Mình thích cách nơi này gắn với nhịp sống địa phương.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member04@hotspot.test') AND places.title IN ('Cầu Trường Tiền','Cầu Trường Tiền') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'46' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='46',title='Cầu Trường Tiền',description='Cây cầu bắc qua sông Hương ở trung tâm Huế, có những nhịp thép cong đặc trưng. Khu vực hai bờ sông thích hợp đi dạo, chụp ảnh và kết hợp tham quan chợ Đông Ba hoặc các tuyến phố trung tâm.',address='Cầu Trường Tiền, Huế',latitude='16.468889',longitude='107.588611' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cau-truong-tien.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cau-truong-tien.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/6d84989f7caead0fca.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/6d84989f7caead0fca.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dai-noi-hue.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dai-noi-hue.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member05@hotspot.test') AND places.title IN ('Ga Đà Lạt','Ga Đà Lạt') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'68' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='68',title='Ga Đà Lạt',description='Nhà ga đường sắt có kiến trúc đặc trưng tại Đà Lạt.',address='Đường Quang Trung, Đà Lạt, Lâm Đồng',latitude='11.942',longitude='108.455' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ga-da-lat.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ga-da-lat.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/8aa3cc2fed75861a24.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/8aa3cc2fed75861a24.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/a97ab83e10a6795a99.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/a97ab83e10a6795a99.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên xem trước đường đi và chọn thời điểm phù hợp.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member06@hotspot.test') AND places.title IN ('Nhà thờ Đức Bà Sài Gòn','Nhà thờ Đức Bà Sài Gòn') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='79',title='Nhà thờ Đức Bà Sài Gòn',description='Nhà thờ với kiến trúc gạch đỏ tại khu vực trung tâm Thành phố Hồ Chí Minh.',address='Công trường Công xã Paris, Thành phố Hồ Chí Minh',latitude='10.779722',longitude='106.699167' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/nha-tho-duc-ba-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/nha-tho-duc-ba-sai-gon.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/eac922b390b93c0b3d.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/eac922b390b93c0b3d.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cho-ben-thanh.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cho-ben-thanh.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Mình thích cách nơi này gắn với nhịp sống địa phương.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member07@hotspot.test') AND places.title IN ('Bến Ninh Kiều','Bến Ninh Kiều') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'92' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='92',title='Bến Ninh Kiều',description='Không gian ven sông tại nơi gặp nhau của sông Cần Thơ và sông Hậu. Khu bến có lối đi dạo, cảnh sinh hoạt sông nước và vị trí thuận tiện để khám phá trung tâm Cần Thơ.',address='Đường Hai Bà Trưng, Cần Thơ',latitude='10.033998',longitude='105.788796' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ben-ninh-kieu.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ben-ninh-kieu.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/358de90dc272524754.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/358de90dc272524754.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/583f71f0e055ea0763.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/583f71f0e055ea0763.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member08@hotspot.test') AND places.title IN ('Chợ Bến Thành','Chợ Bến Thành') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='79',title='Chợ Bến Thành',description='Chợ truyền thống ở trung tâm thành phố, có nhiều hàng hóa và khu ẩm thực.',address='Chợ Bến Thành, Thành phố Hồ Chí Minh',latitude='10.772521',longitude='106.698019' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cho-ben-thanh.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cho-ben-thanh.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/f657d225cbb9d41012.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/f657d225cbb9d41012.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/nha-tho-duc-ba-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/nha-tho-duc-ba-sai-gon.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member09@hotspot.test') AND places.title IN ('Chợ Đà Lạt','Chợ Đà Lạt') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'68' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='68',title='Chợ Đà Lạt',description='Chợ trung tâm Đà Lạt, một địa điểm tìm hiểu sinh hoạt và sản vật địa phương.',address='Đường Nguyễn Thị Minh Khai, Đà Lạt, Lâm Đồng',latitude='11.9429528',longitude='108.4368222' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cho-da-lat.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cho-da-lat.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ga-da-lat.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ga-da-lat.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/8aa3cc2fed75861a24.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/8aa3cc2fed75861a24.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Mình thích cách nơi này gắn với nhịp sống địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Mình thích cách nơi này gắn với nhịp sống địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member10@hotspot.test') AND places.title IN ('Phố đi bộ Nguyễn Huệ','Phố đi bộ Nguyễn Huệ') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='79',title='Phố đi bộ Nguyễn Huệ',description='Không gian đi bộ ở trung tâm thành phố, nối khu vực tòa thị chính với bờ sông.',address='Đường Nguyễn Huệ, Thành phố Hồ Chí Minh',latitude='10.774112',longitude='106.703619' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/pho-di-bo-nguyen-hue.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/pho-di-bo-nguyen-hue.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/af4ef234b6309b9fa4.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/af4ef234b6309b9fa4.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/nha-tho-duc-ba-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/nha-tho-duc-ba-sai-gon.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Nên xem trước đường đi và chọn thời điểm phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Nên dành thời gian đi bộ để ngắm cảnh và tìm các góc chụp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Có thể kết hợp với các điểm gần đó cho lịch trình gọn hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Nên xem trước đường đi và chọn thời điểm phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Nên xem trước đường đi và chọn thời điểm phù hợp.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member11@hotspot.test') AND places.title IN ('Văn Miếu - Quốc Tử Giám','Văn Miếu - Quốc Tử Giám') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'01' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='01',title='Văn Miếu - Quốc Tử Giám',description='Quần thể kiến trúc gắn với truyền thống giáo dục và khoa bảng Việt Nam.',address='58 Quốc Tử Giám, Hà Nội',latitude='21.028611',longitude='105.835556' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/van-mieu-quoc-tu-giam.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/van-mieu-quoc-tu-giam.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/efd79b86058ab14558.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/efd79b86058ab14558.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/9d5cd64b1dddad0f12.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/9d5cd64b1dddad0f12.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Mình thích những câu chuyện gắn với địa điểm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Mình thích những câu chuyện gắn với địa điểm.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member12@hotspot.test') AND places.title IN ('Bảo tàng Dân tộc học Việt Nam','Bảo tàng Dân tộc học Việt Nam') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'01' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='01',title='Bảo tàng Dân tộc học Việt Nam',description='Bảo tàng giới thiệu đời sống và văn hóa các dân tộc Việt Nam.',address='Đường Nguyễn Văn Huyên, Hà Nội',latitude='21.0406',longitude='105.799' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/bao-tang-dan-toc-hoc-viet-nam.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/bao-tang-dan-toc-hoc-viet-nam.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/van-mieu-quoc-tu-giam.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/van-mieu-quoc-tu-giam.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/di-tich-nha-tu-hoa-lo.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/di-tich-nha-tu-hoa-lo.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member01@hotspot.test') AND places.title IN ('Di tích Nhà tù Hỏa Lò','Di tích Nhà tù Hỏa Lò') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'01' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='01',title='Di tích Nhà tù Hỏa Lò',description='Di tích lịch sử có các không gian trưng bày về nhà tù Hỏa Lò.',address='1 Hỏa Lò, Hà Nội',latitude='21.0252778',longitude='105.8463889' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/di-tich-nha-tu-hoa-lo.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/di-tich-nha-tu-hoa-lo.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/4c06c83988d2778a90.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/4c06c83988d2778a90.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/van-mieu-quoc-tu-giam.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/van-mieu-quoc-tu-giam.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Mình thích những câu chuyện gắn với địa điểm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member02@hotspot.test') AND places.title IN ('Bưu điện Trung tâm Sài Gòn','Bưu điện Trung tâm Sài Gòn') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='79',title='Bưu điện Trung tâm Sài Gòn',description='Công trình bưu điện lịch sử với kiến trúc và không gian sảnh đặc trưng.',address='2 Công xã Paris, Thành phố Hồ Chí Minh',latitude='10.779884',longitude='106.699943' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dinh-doc-lap.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dinh-doc-lap.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/bao-tang-chung-tich-chien-tranh.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/bao-tang-chung-tich-chien-tranh.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Mình thích những câu chuyện gắn với địa điểm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member03@hotspot.test') AND places.title IN ('Dinh Độc Lập','Dinh Độc Lập') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='79',title='Dinh Độc Lập',description='Di tích lịch sử với kiến trúc và các không gian gắn với lịch sử Việt Nam.',address='135 Nam Kỳ Khởi Nghĩa, Thành phố Hồ Chí Minh',latitude='10.77705',longitude='106.69537' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dinh-doc-lap.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dinh-doc-lap.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/bao-tang-chung-tich-chien-tranh.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/bao-tang-chung-tich-chien-tranh.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Mình thích những câu chuyện gắn với địa điểm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member04@hotspot.test') AND places.title IN ('Bảo tàng Chứng tích Chiến tranh','Bảo tàng Chứng tích Chiến tranh') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='79',title='Bảo tàng Chứng tích Chiến tranh',description='Bảo tàng trưng bày tư liệu và hiện vật về chiến tranh, hướng đến thông điệp hòa bình.',address='28 Võ Văn Tần, Thành phố Hồ Chí Minh',latitude='10.7795',longitude='106.692' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/bao-tang-chung-tich-chien-tranh.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/bao-tang-chung-tich-chien-tranh.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/fc424661d08cc2325d.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/fc424661d08cc2325d.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Mình thích những câu chuyện gắn với địa điểm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Mình thích những câu chuyện gắn với địa điểm.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member05@hotspot.test') AND places.title IN ('Bảo tàng Điêu khắc Chăm Đà Nẵng','Bảo tàng Điêu khắc Chăm Đà Nẵng') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='48',title='Bảo tàng Điêu khắc Chăm Đà Nẵng',description='Bảo tàng giới thiệu hiện vật và nghệ thuật điêu khắc Chăm.',address='2 đường 2 Tháng 9, Đà Nẵng',latitude='16.059894',longitude='108.222641' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/bao-tang-dieu-khac-cham-da-nang.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/bao-tang-dieu-khac-cham-da-nang.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/2a92e42f87c8016d1a.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/2a92e42f87c8016d1a.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/80e9532a3dda87ce7a.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/80e9532a3dda87ce7a.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Mình thích những câu chuyện gắn với địa điểm.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member06@hotspot.test') AND places.title IN ('Đại Nội Huế','Đại Nội Huế') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'46' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='46',title='Đại Nội Huế',description='Quần thể cung điện và công trình thuộc khu vực hoàng thành của cố đô Huế.',address='Hoàng thành Huế, Huế',latitude='16.4694444',longitude='107.5777778' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dai-noi-hue.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dai-noi-hue.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/109a5d58fb481fda3b.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/109a5d58fb481fda3b.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/8154e93e8d4bf1b36e.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/8154e93e8d4bf1b36e.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Mình thích những câu chuyện gắn với địa điểm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member07@hotspot.test') AND places.title IN ('Chùa Thiên Mụ','Chùa Thiên Mụ') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'46' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='46',title='Chùa Thiên Mụ',description='Ngôi chùa trên đồi ven sông Hương, nổi bật với tháp Phước Duyên.',address='Đường Nguyễn Phúc Nguyên, Huế',latitude='16.453599',longitude='107.544812' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/chua-thien-mu.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/chua-thien-mu.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dai-noi-hue.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dai-noi-hue.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/109a5d58fb481fda3b.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/109a5d58fb481fda3b.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Không gian có nhiều chi tiết kiến trúc đáng quan sát.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Không gian có nhiều chi tiết kiến trúc đáng quan sát.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member08@hotspot.test') AND places.title IN ('Tháp Bà Po Nagar','Tháp Bà Po Nagar') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'56' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='56',title='Tháp Bà Po Nagar',description='Quần thể tháp Chăm trên đồi gần cửa sông Cái tại Nha Trang.',address='Đồi Cù Lao, Nha Trang, Khánh Hòa',latitude='12.2652778',longitude='109.1955556' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/thap-ba-po-nagar.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/thap-ba-po-nagar.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/8b0f71f2ff79bb38e8.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/8b0f71f2ff79bb38e8.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/a6200f16394e92ae8d.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/a6200f16394e92ae8d.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Mình thích những câu chuyện gắn với địa điểm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Mình thích những câu chuyện gắn với địa điểm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Nên dành thời gian đọc các thông tin giới thiệu để hiểu rõ hơn.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Phù hợp hơn khi muốn tìm hiểu văn hóa thay vì chỉ chụp ảnh.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member09@hotspot.test') AND places.title IN ('Hồ Hoàn Kiếm','Hồ Hoàn Kiếm') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'01' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='01',title='Hồ Hoàn Kiếm',description='Hồ nước tại trung tâm Hà Nội, gắn với tháp Rùa và cảnh quan đô thị.',address='Hồ Hoàn Kiếm, Hà Nội',latitude='21.0288889',longitude='105.8525' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ho-hoan-kiem.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ho-hoan-kiem.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ho-tay.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ho-tay.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/15519c8a1f29f5b8a9.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/15519c8a1f29f5b8a9.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Cảnh quan là điểm mình chú ý nhất.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member10@hotspot.test') AND places.title IN ('Hồ Tây','Hồ Tây') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'01' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='01',title='Hồ Tây',description='Hồ nước lớn trong nội thành Hà Nội với các tuyến đường ven hồ.',address='Hồ Tây, Hà Nội',latitude='21.055',longitude='105.82' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ho-tay.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ho-tay.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/15519c8a1f29f5b8a9.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/15519c8a1f29f5b8a9.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ho-hoan-kiem.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ho-hoan-kiem.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Cảnh quan là điểm mình chú ý nhất.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member11@hotspot.test') AND places.title IN ('Ngũ Hành Sơn','Ngũ Hành Sơn') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='48',title='Ngũ Hành Sơn',description='Quần thể núi đá vôi, hang động và công trình tín ngưỡng ở phía nam Đà Nẵng. Ghim được đặt tại khu vực Thủy Sơn, một điểm tham quan chính của quần thể.',address='Khu danh thắng Ngũ Hành Sơn, Đà Nẵng',latitude='16.0043044',longitude='108.263527' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ngu-hanh-son.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ngu-hanh-son.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/6e26132451332bfb77.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/6e26132451332bfb77.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/b21af391b9c20d9977.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/b21af391b9c20d9977.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Cảnh quan là điểm mình chú ý nhất.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member12@hotspot.test') AND places.title IN ('Bãi biển Mỹ Khê','Bãi biển Mỹ Khê') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='48',title='Bãi biển Mỹ Khê',description='Bãi biển nằm ở phía đông Đà Nẵng, có không gian bờ cát ven biển.',address='Đường Võ Nguyên Giáp, Đà Nẵng',latitude='16.0704',longitude='108.2461' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/bai-bien-my-khe.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/bai-bien-my-khe.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ngu-hanh-son.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ngu-hanh-son.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ban-dao-son-tra.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ban-dao-son-tra.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Cảnh quan là điểm mình chú ý nhất.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Cảnh quan là điểm mình chú ý nhất.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member01@hotspot.test') AND places.title IN ('Bán đảo Sơn Trà','Bán đảo Sơn Trà') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='48',title='Bán đảo Sơn Trà',description='Bán đảo với cảnh quan rừng và biển ở phía đông bắc Đà Nẵng.',address='Bán đảo Sơn Trà, Đà Nẵng',latitude='16.1197',longitude='108.2868' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ban-dao-son-tra.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ban-dao-son-tra.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/409e82219ba3625dc3.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/409e82219ba3625dc3.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ngu-hanh-son.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ngu-hanh-son.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Cảnh quan là điểm mình chú ý nhất.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member02@hotspot.test') AND places.title IN ('Hồ Xuân Hương','Hồ Xuân Hương') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'68' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='68',title='Hồ Xuân Hương',description='Hồ nước nằm giữa trung tâm Đà Lạt, gắn với các tuyến đường dạo bộ và cảnh quan đô thị cao nguyên. Có thể kết hợp ghé quảng trường Lâm Viên hoặc nghỉ chân quanh hồ.',address='Hồ Xuân Hương, Đà Lạt, Lâm Đồng',latitude='11.9450803',longitude='108.4487222' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ho-xuan-huong.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ho-xuan-huong.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/f298bbf665175f52d9.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/f298bbf665175f52d9.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/a97ab83e10a6795a99.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/a97ab83e10a6795a99.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Cảnh quan là điểm mình chú ý nhất.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member03@hotspot.test') AND places.title IN ('Hồ Tuyền Lâm','Hồ Tuyền Lâm') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'68' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='68',title='Hồ Tuyền Lâm',description='Hồ nước nằm giữa cảnh quan đồi thông ở khu vực Đà Lạt.',address='Khu vực hồ Tuyền Lâm, Đà Lạt, Lâm Đồng',latitude='11.893',longitude='108.425' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ho-tuyen-lam.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ho-tuyen-lam.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/a97ab83e10a6795a99.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/a97ab83e10a6795a99.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/0d1f4ca5c5c0a22c10.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/0d1f4ca5c5c0a22c10.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member04@hotspot.test') AND places.title IN ('Thác Cam Ly','Thác Cam Ly') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'68' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='68',title='Thác Cam Ly',description='Thác nước tại khu vực Đà Lạt, một địa danh gắn với cảnh quan cao nguyên.',address='Khu vực thác Cam Ly, Đà Lạt, Lâm Đồng',latitude='11.946721',longitude='108.422635' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/thac-cam-ly.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/thac-cam-ly.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/a97ab83e10a6795a99.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/a97ab83e10a6795a99.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ho-xuan-huong.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ho-xuan-huong.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Cảnh quan là điểm mình chú ý nhất.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member05@hotspot.test') AND places.title IN ('Khu du lịch Tràng An','Khu du lịch Tràng An') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'37' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='37',title='Khu du lịch Tràng An',description='Cảnh quan núi đá vôi, sông nước và hang động thuộc quần thể Tràng An.',address='Quần thể danh thắng Tràng An, Ninh Bình',latitude='20.2557',longitude='105.9156' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/khu-du-lich-trang-an.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/khu-du-lich-trang-an.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/1ed3a31ba5cd2a66d7.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/1ed3a31ba5cd2a66d7.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/18999ea08fef7f9fe2.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/18999ea08fef7f9fe2.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Cảnh quan là điểm mình chú ý nhất.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Có thể dành thêm thời gian đi chậm và ngắm cảnh.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Có thể dành thêm thời gian đi chậm và ngắm cảnh.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member06@hotspot.test') AND places.title IN ('Hang Múa','Hang Múa') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'37' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='37',title='Hang Múa',description='Điểm tham quan tại Ninh Bình với đường bậc thang lên núi và góc nhìn xuống đồng ruộng, núi đá vôi. Khi tham quan nên chọn giày thuận tiện và tự chuẩn bị nước uống.',address='Khu du lịch Hang Múa, Ninh Bình',latitude='20.228611',longitude='105.931667' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/hang-mua.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/hang-mua.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/cad68fa72ca43d47b5.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/cad68fa72ca43d47b5.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/75af31c1c32ff82611.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/75af31c1c32ff82611.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Cảnh quan là điểm mình chú ý nhất.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Nên giữ gìn vệ sinh và hạn chế mang nhiều đồ khi di chuyển.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên kiểm tra thời tiết và mang giày thuận tiện trước khi đi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Cảnh quan là điểm mình chú ý nhất.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Cảnh quan là điểm mình chú ý nhất.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member07@hotspot.test') AND places.title IN ('Thảo Cầm Viên Sài Gòn','Thảo Cầm Viên Sài Gòn') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Vui chơi'),province_code='79',title='Thảo Cầm Viên Sài Gòn',description='Không gian tham quan động vật và cây xanh trong trung tâm thành phố.',address='2 Nguyễn Bỉnh Khiêm, Thành phố Hồ Chí Minh',latitude='10.7880556',longitude='106.7047222' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/thao-cam-vien-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/thao-cam-vien-sai-gon.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-vien-van-hoa-dam-sen.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-vien-van-hoa-dam-sen.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/khu-du-lich-suoi-tien.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/khu-du-lich-suoi-tien.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Phù hợp để đi cùng bạn bè hoặc người thân.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Phù hợp để đi cùng bạn bè hoặc người thân.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Nên chủ động giờ đến để có lịch trình thoải mái.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Nên chủ động giờ đến để có lịch trình thoải mái.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Phù hợp để đi cùng bạn bè hoặc người thân.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Phù hợp để đi cùng bạn bè hoặc người thân.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member08@hotspot.test') AND places.title IN ('Công viên Văn hóa Đầm Sen','Công viên Văn hóa Đầm Sen') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Vui chơi'),province_code='79',title='Công viên Văn hóa Đầm Sen',description='Công viên có các khu tham quan và hoạt động vui chơi tại Thành phố Hồ Chí Minh.',address='3 Hòa Bình, Thành phố Hồ Chí Minh',latitude='10.76611',longitude='106.63806' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-vien-van-hoa-dam-sen.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-vien-van-hoa-dam-sen.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/6ed2947c3f9ce9dc98.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/6ed2947c3f9ce9dc98.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/thao-cam-vien-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/thao-cam-vien-sai-gon.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên chủ động giờ đến để có lịch trình thoải mái.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên chủ động giờ đến để có lịch trình thoải mái.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên chủ động giờ đến để có lịch trình thoải mái.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên chủ động giờ đến để có lịch trình thoải mái.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member09@hotspot.test') AND places.title IN ('Khu du lịch Suối Tiên','Khu du lịch Suối Tiên') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Vui chơi'),province_code='79',title='Khu du lịch Suối Tiên',description='Khu vui chơi có các công trình và hoạt động mang chủ đề văn hóa Việt Nam.',address='120 Xa lộ Hà Nội, Thành phố Hồ Chí Minh',latitude='10.863885',longitude='106.802173' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/khu-du-lich-suoi-tien.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/khu-du-lich-suoi-tien.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/thao-cam-vien-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/thao-cam-vien-sai-gon.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-vien-van-hoa-dam-sen.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-vien-van-hoa-dam-sen.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Phù hợp để đi cùng bạn bè hoặc người thân.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Phù hợp để đi cùng bạn bè hoặc người thân.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Nên chủ động giờ đến để có lịch trình thoải mái.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Nên chủ động giờ đến để có lịch trình thoải mái.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member10@hotspot.test') AND places.title IN ('Sun World Bà Nà Hills','Sun World Bà Nà Hills') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Vui chơi'),province_code='48',title='Sun World Bà Nà Hills',description='Khu du lịch trên núi có các công trình tham quan và hệ thống cáp treo.',address='Khu du lịch Bà Nà, Đà Nẵng',latitude='15.9955556',longitude='107.9941667' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/sun-world-ba-na-hills.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/sun-world-ba-na-hills.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/ecff844c7f5606c0d5.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/ecff844c7f5606c0d5.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/7518a1edb778da915f.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/7518a1edb778da915f.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Phù hợp để đi cùng bạn bè hoặc người thân.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Phù hợp để đi cùng bạn bè hoặc người thân.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên chủ động giờ đến để có lịch trình thoải mái.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên chủ động giờ đến để có lịch trình thoải mái.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member11@hotspot.test') AND places.title IN ('VinWonders Nha Trang','VinWonders Nha Trang') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'56' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Vui chơi'),province_code='56',title='VinWonders Nha Trang',description='Khu vui chơi trên đảo Hòn Tre, nằm trong không gian vịnh Nha Trang.',address='Đảo Hòn Tre, Nha Trang, Khánh Hòa',latitude='12.2173958',longitude='109.2412179' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/vinwonders-nha-trang.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/vinwonders-nha-trang.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/3c16b914fe3b5ad68d.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/3c16b914fe3b5ad68d.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/8b0f71f2ff79bb38e8.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/8b0f71f2ff79bb38e8.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Phù hợp để đi cùng bạn bè hoặc người thân.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Phù hợp để đi cùng bạn bè hoặc người thân.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Có thể sắp xếp thêm thời gian nghỉ giữa các hoạt động.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member12@hotspot.test') AND places.title IN ('Sun World Hạ Long','Sun World Hạ Long') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'22' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Vui chơi'),province_code='22',title='Sun World Hạ Long',description='Tổ hợp vui chơi và tham quan ở khu vực Hạ Long.',address='Khu vực Bãi Cháy, Quảng Ninh',latitude='20.955479',longitude='107.049652' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/sun-world-ha-long.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/sun-world-ha-long.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/5573057866d249340e.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/5573057866d249340e.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/cb602e1757e3c3cdde.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/cb602e1757e3c3cdde.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Phù hợp để đi cùng bạn bè hoặc người thân.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Phù hợp để đi cùng bạn bè hoặc người thân.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Nên chủ động giờ đến để có lịch trình thoải mái.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Nên chủ động giờ đến để có lịch trình thoải mái.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên xem trước lịch trình và chọn hoạt động phù hợp với nhóm.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Phù hợp để đi cùng bạn bè hoặc người thân.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Phù hợp để đi cùng bạn bè hoặc người thân.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member01@hotspot.test') AND places.title IN ('Cộng Cà Phê - Bạch Đằng','Cộng Cà Phê - Bạch Đằng') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Cà phê'),province_code='48',title='Cộng Cà Phê - Bạch Đằng',description='Cửa hàng Cộng Cà Phê trên đường Bạch Đằng, khu trung tâm Đà Nẵng gần sông Hàn. Không gian mang phong cách hoài niệm, thuận tiện kết hợp cùng hành trình đi bộ ven sông.',address='96 Bạch Đằng, Phường Hải Châu, Thành phố Đà Nẵng.',latitude='16.0690255',longitude='108.2249712' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-bach-dang-1.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-bach-dang-1.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-bach-dang-2.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-bach-dang-2.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-bach-dang-3.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-bach-dang-3.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Nên chọn món và thời điểm ghé theo sở thích của mình.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Nên chọn món và thời điểm ghé theo sở thích của mình.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member02@hotspot.test') AND places.title IN ('Cộng Cà Phê - Lý Tự Trọng','Cộng Cà Phê - Lý Tự Trọng') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Cà phê'),province_code='79',title='Cộng Cà Phê - Lý Tự Trọng',description='Cộng Cà Phê tại số 26 Lý Tự Trọng, khu trung tâm Thành phố Hồ Chí Minh. Không gian mang phong cách hoài niệm, phù hợp để nghỉ chân trong hành trình khám phá khu vực quanh Đồng Khởi và Nhà hát Thành phố.',address='26 Lý Tự Trọng- Phường Sài Gòn- Thành Phố Hồ Chí Minh.',latitude='10.7783026',longitude='106.7010504' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-ly-tu-trong-1.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-ly-tu-trong-1.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-bach-dang-1.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-bach-dang-1.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-bach-dang-2.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-bach-dang-2.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên chọn món và thời điểm ghé theo sở thích của mình.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Nên chọn món và thời điểm ghé theo sở thích của mình.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Nên chọn món và thời điểm ghé theo sở thích của mình.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Nên chọn món và thời điểm ghé theo sở thích của mình.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member03@hotspot.test') AND places.title IN ('Cộng Cà Phê - Nguyễn Thái Học','Cộng Cà Phê - Nguyễn Thái Học') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Cà phê'),province_code='48',title='Cộng Cà Phê - Nguyễn Thái Học',description='Cửa hàng Cộng Cà Phê trên đường Nguyễn Thái Học, Đà Nẵng.',address='39 - Nguyễn Thái Học, phường Hải Châu, tp Đà Nẵng.',latitude='16.0679317',longitude='108.2234571' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-nguyen-thai-hoc-1.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-nguyen-thai-hoc-1.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-nguyen-thai-hoc-2.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-nguyen-thai-hoc-2.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-nguyen-thai-hoc-3.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-nguyen-thai-hoc-3.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Nên chọn món và thời điểm ghé theo sở thích của mình.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Nên chọn món và thời điểm ghé theo sở thích của mình.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member04@hotspot.test') AND places.title IN ('Cộng Cà Phê - Nhà Thờ','Cộng Cà Phê - Nhà Thờ') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'01' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Cà phê'),province_code='01',title='Cộng Cà Phê - Nhà Thờ',description='Cửa hàng Cộng Cà Phê trên đường Nhà Thờ ở trung tâm Hà Nội.',address='27 Nhà Thờ, Phường Hoàn Kiếm, Tp. Hà Nội',latitude='21.0288138',longitude='105.8496411' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-nha-tho-1.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-nha-tho-1.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-nha-tho-2.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-nha-tho-2.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-nha-tho-3.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-nha-tho-3.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member04@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình rất hài lòng khi ghé nơi này. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình rất hài lòng khi ghé nơi này. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Nên chọn món và thời điểm ghé theo sở thích của mình.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Nên chọn món và thời điểm ghé theo sở thích của mình.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member05@hotspot.test') AND places.title IN ('Cộng Cà Phê - Phan Đình Phùng','Cộng Cà Phê - Phan Đình Phùng') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'68' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Cà phê'),province_code='68',title='Cộng Cà Phê - Phan Đình Phùng',description='Cửa hàng Cộng Cà Phê tại Đà Lạt, thuộc tỉnh Lâm Đồng.',address='94-96 Phan Đình Phùng , Phường Xuân Hương , Tỉnh Lâm Đồng',latitude='11.945845',longitude='108.4326936' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-phan-dinh-phung-1.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-phan-dinh-phung-1.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-phan-dinh-phung-2.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-phan-dinh-phung-2.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-phan-dinh-phung-3.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-phan-dinh-phung-3.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member05@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên chọn món và thời điểm ghé theo sở thích của mình.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm đáng ghé nếu có dịp. Nên chọn món và thời điểm ghé theo sở thích của mình.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member06@hotspot.test') AND places.title IN ('Cộng Cà Phê - Bến Nghé','Cộng Cà Phê - Bến Nghé') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'46' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Cà phê'),province_code='46',title='Cộng Cà Phê - Bến Nghé',description='Cửa hàng Cộng Cà Phê trên đường Bến Nghé tại Huế.',address='22 Bến Nghé, phường Thuận Hoá , Thành phố Huế',latitude='16.4662887',longitude='107.5944385' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-ben-nghe-1.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-ben-nghe-1.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-ben-nghe-2.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-ben-nghe-2.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cong-ca-phe-ben-nghe-3.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cong-ca-phe-ben-nghe-3.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận của mình là khá ổn. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member06@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận của mình là khá ổn. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm ở mức vừa phải với mình. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm ở mức vừa phải với mình. Mình thích có một chỗ ngồi nghỉ trong hành trình khám phá phố.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Có thể kết hợp ghé quán với việc đi bộ quanh khu vực.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Khẩu vị đồ uống và cảm nhận không gian tùy từng người.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member07@hotspot.test') AND places.title IN ('Phở Gia Truyền Bát Đàn','Phở Gia Truyền Bát Đàn') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'01' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Ăn uống'),province_code='01',title='Phở Gia Truyền Bát Đàn',description='Quán phở tại số 49 Bát Đàn, thuộc khu phố cổ Hà Nội. Đây là một địa chỉ ẩm thực để tìm hiểu món phở bò trong hành trình đi bộ quanh trung tâm thành phố.',address='49 Bát Đàn, Hà Nội',latitude='21.0336377',longitude='105.8463792' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/pho-gia-truyen-bat-dan.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/pho-gia-truyen-bat-dan.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/54be38b705544e4558.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/54be38b705544e4558.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/4f36608935a042dc7d.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/4f36608935a042dc7d.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy ổn cho một lần ghé. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member07@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy ổn cho một lần ghé. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình cần chọn thời điểm khác để ghé lại. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member08@hotspot.test') AND places.title IN ('Cơm Tấm Ba Ghiền','Cơm Tấm Ba Ghiền') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Ăn uống'),province_code='79',title='Cơm Tấm Ba Ghiền',description='Địa chỉ cơm tấm tại số 84 Đặng Văn Ngữ, Thành phố Hồ Chí Minh. Có thể ghé để tìm hiểu món cơm tấm với sườn nướng và các món ăn kèm quen thuộc của ẩm thực Sài Gòn.',address='84 Đặng Văn Ngữ, Thành phố Hồ Chí Minh',latitude='10.79441',longitude='106.66941' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/com-tam-ba-ghien.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/com-tam-ba-ghien.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/5e96d55e0cff446b26.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/5e96d55e0cff446b26.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/0589edf0068ff314b0.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/0589edf0068ff314b0.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member08@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có trải nghiệm thật sự ưng ý. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có lẽ mình sẽ chọn một điểm khác cho lần đi tới. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member09@hotspot.test') AND places.title IN ('Bánh Xèo Bà Dưỡng','Bánh Xèo Bà Dưỡng') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Ăn uống'),province_code='48',title='Bánh Xèo Bà Dưỡng',description='Quán bánh xèo nằm trong kiệt đường Hoàng Diệu tại Đà Nẵng. Địa chỉ phù hợp cho hành trình khám phá món ăn miền Trung; nên đối chiếu số kiệt khi tìm đường.',address='K280/23 Hoàng Diệu, Đà Nẵng',latitude='16.0652255',longitude='108.2194342' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/banh-xeo-ba-duong.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/banh-xeo-ba-duong.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/53bd472fbb9896c221.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/53bd472fbb9896c221.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/3d3aeb340041cf6f1a.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/3d3aeb340041cf6f1a.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member09@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình thấy nơi này chưa hợp sở thích cá nhân. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Đây là một điểm dừng chân đáng nhớ. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member10@hotspot.test') AND places.title IN ('Mì Quảng Bà Mua - Lê Đình Dương','Mì Quảng Bà Mua - Lê Đình Dương') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Ăn uống'),province_code='48',title='Mì Quảng Bà Mua - Lê Đình Dương',description='Điểm phục vụ mì Quảng của hệ thống Bà Mua trên đường Lê Đình Dương.',address='44 Lê Đình Dương, Đà Nẵng',latitude='16.0612678',longitude='108.2212735' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/mi-quang-ba-mua-le-dinh-duong.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/mi-quang-ba-mua-le-dinh-duong.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/c7c652a34b8d1ce783.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/c7c652a34b8d1ce783.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/067725ff6e3e8f7e59.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/067725ff6e3e8f7e59.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Một địa điểm mình muốn quay lại. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member10@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Một địa điểm mình muốn quay lại. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm nhìn chung khá tốt. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm nhìn chung khá tốt. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member11@hotspot.test') AND places.title IN ('Mì Quảng Bà Mua - Trưng Nữ Vương','Mì Quảng Bà Mua - Trưng Nữ Vương') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Ăn uống'),province_code='48',title='Mì Quảng Bà Mua - Trưng Nữ Vương',description='Điểm phục vụ mì Quảng Bà Mua trên đường Trưng Nữ Vương.',address='169 Trưng Nữ Vương, Đà Nẵng',latitude=NULL,longitude=NULL WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/mi-quang-ba-mua-trung-nu-vuong.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/mi-quang-ba-mua-trung-nu-vuong.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/c7c652a34b8d1ce783.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/c7c652a34b8d1ce783.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/067725ff6e3e8f7e59.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/067725ff6e3e8f7e59.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình khá thích nơi này. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member11@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình khá thích nơi này. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Nơi này có nét riêng, cảm nhận tùy vào sở thích mỗi người. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Chưa phù hợp lắm với điều mình mong đợi. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Trải nghiệm lần này chưa phù hợp với mong đợi của mình. Nên đối chiếu địa chỉ và gọi phần ăn phù hợp.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member12@hotspot.test') AND places.title IN ('Mì Quảng 1A','Mì Quảng 1A') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Ăn uống'),province_code='48',title='Mì Quảng 1A',description='Quán mì Quảng trên đường Hải Phòng, khu trung tâm Đà Nẵng. Món ăn mang nét đặc trưng xứ Quảng với mì, rau sống, bánh tráng và phần nước dùng ít hơn các món mì nước thông thường.',address='1A Hải Phòng, Đà Nẵng',latitude='16.0723814',longitude='108.2191279' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/mi-quang-ba-mua-trung-nu-vuong.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/mi-quang-ba-mua-trung-nu-vuong.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/c7c652a34b8d1ce783.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/c7c652a34b8d1ce783.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/067725ff6e3e8f7e59.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/067725ff6e3e8f7e59.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member12@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Có điểm thú vị nhưng chưa thật sự hợp sở thích của mình. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member01@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Cảm nhận lần này chưa được như kỳ vọng. Khẩu vị là cảm nhận cá nhân, nên xem món trước khi gọi.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member02@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Mình chưa có nhiều điều muốn giới thiệu sau lần ghé này. Có thể kết hợp trong hành trình khám phá ẩm thực địa phương.');

UPDATE reviews JOIN users reviewer ON reviewer.user_id=reviews.user_id SET reviews.content='Rất thích trải nghiệm ở đây. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.' WHERE reviews.place_id=@hotspot_seed_place_id AND reviewer.email='demo-20261107-member03@hotspot.test' AND (reviews.content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%' OR reviews.content='Rất thích trải nghiệm ở đây. Đây là một trải nghiệm ẩm thực để tự mình cảm nhận.');

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member01@hotspot.test') AND places.title IN ('[TEST] Hồ sơ PENDING 01','Đường sách Nguyễn Văn Bình') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='79',title='Đường sách Nguyễn Văn Bình',description='Không gian đi bộ và các gian sách trên đường Nguyễn Văn Bình, cạnh Bưu điện Trung tâm. Có thể kết hợp tìm sách, nghỉ chân và tham quan các công trình kiến trúc ở trung tâm thành phố.',address='Đường Nguyễn Văn Bình, Thành phố Hồ Chí Minh',latitude='10.7805735',longitude='106.6996948' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dinh-doc-lap.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dinh-doc-lap.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/bao-tang-chung-tich-chien-tranh.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/bao-tang-chung-tich-chien-tranh.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member02@hotspot.test') AND places.title IN ('[TEST] Hồ sơ PENDING 02','Nhà hát Thành phố Hồ Chí Minh') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='79',title='Nhà hát Thành phố Hồ Chí Minh',description='Công trình kiến trúc tại Công trường Lam Sơn, gần đường Đồng Khởi và phố đi bộ Nguyễn Huệ. Khu vực phía ngoài nhà hát là một điểm dừng chân để ngắm kiến trúc và tìm hiểu không gian trung tâm Sài Gòn.',address='7 Công trường Lam Sơn, Thành phố Hồ Chí Minh',latitude='10.7767437',longitude='106.7032488' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/cd7ed5d158a468634c.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/cd7ed5d158a468634c.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dinh-doc-lap.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dinh-doc-lap.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member03@hotspot.test') AND places.title IN ('[TEST] Hồ sơ PENDING 03','Cầu Nguyễn Văn Trỗi') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='48',title='Cầu Nguyễn Văn Trỗi',description='Cây cầu bắc qua sông Hàn, nằm gần cầu Trần Thị Lý. Khu vực cầu dành cho hoạt động đi bộ, ngắm cảnh sông và quan sát nhịp sống hai bờ Đà Nẵng.',address='Cầu Nguyễn Văn Trỗi, Đà Nẵng',latitude='16.0513135',longitude='108.2309046' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/9eb5490fdbbfd064d0.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/9eb5490fdbbfd064d0.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/309034288f57366c69.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/309034288f57366c69.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/pho-co-hoi-an.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/pho-co-hoi-an.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='cuoi-tuan' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member04@hotspot.test') AND places.title IN ('[TEST] Hồ sơ PENDING 04','Chợ Hàn') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='48',title='Chợ Hàn',description='Khu chợ ở trung tâm Đà Nẵng, gần đường Bạch Đằng và sông Hàn. Các quầy hàng cung cấp thực phẩm, đặc sản và đồ dùng; thích hợp kết hợp trong lịch trình khám phá đời sống địa phương.',address='119 Trần Phú, Đà Nẵng',latitude='16.0683525',longitude='108.224283' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/56089828220a4be884.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/56089828220a4be884.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/pho-co-hoi-an.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/pho-co-hoi-an.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cau-rong-da-nang.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cau-rong-da-nang.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='cuoi-tuan' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member01@hotspot.test') AND places.title IN ('[TEST] Hồ sơ REJECTED 05','Bảo tàng Mỹ thuật Thành phố Hồ Chí Minh') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'79' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='79',title='Bảo tàng Mỹ thuật Thành phố Hồ Chí Minh',description='Bảo tàng tại khu trung tâm Thành phố Hồ Chí Minh, giới thiệu các tác phẩm mỹ thuật trong không gian kiến trúc nhiều chi tiết trang trí. Có thể kết hợp tham quan khu vực gần chợ Bến Thành.',address='97A Phó Đức Chính, Thành phố Hồ Chí Minh',latitude='10.769596',longitude='106.69927',rejection_reason='Cần mô tả rõ hơn các nội dung trưng bày và thông tin tham quan.' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/0722a628848307e340.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/0722a628848307e340.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/buu-dien-trung-tam-sai-gon.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dinh-doc-lap.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dinh-doc-lap.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member02@hotspot.test') AND places.title IN ('[TEST] Hồ sơ REJECTED 06','Chùa Trấn Quốc') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'01' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='01',title='Chùa Trấn Quốc',description='Ngôi chùa nằm trên một đảo nhỏ phía đông hồ Tây, gắn với cảnh quan đường Thanh Niên. Không gian kiến trúc và mặt nước phù hợp cho một điểm dừng tìm hiểu văn hóa, tín ngưỡng Hà Nội.',address='Đường Thanh Niên, Hà Nội',latitude='21.0478837',longitude='105.8368375',rejection_reason='Bổ sung lưu ý ứng xử và trang phục khi ghé không gian tín ngưỡng.' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/71249ea333f2fde1ec.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/71249ea333f2fde1ec.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/10bd5814c836a10943.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/10bd5814c836a10943.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/van-mieu-quoc-tu-giam.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/van-mieu-quoc-tu-giam.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member03@hotspot.test') AND places.title IN ('[TEST] Hồ sơ REJECTED 07','Lăng Khải Định') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'46' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='46',title='Lăng Khải Định',description='Công trình lăng tẩm tại Huế, nổi bật với các lớp sân, bậc thang và chi tiết trang trí khảm. Đây là một điểm tham quan để tìm hiểu kiến trúc và mỹ thuật cung đình thời Nguyễn.',address='Khu vực núi Châu Chữ, Huế',latitude='16.3990174',longitude='107.5903393',rejection_reason='Cần rà soát lại thông tin đường đi và điểm đón khách.' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/ecd6c0eb413dcf9290.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/ecd6c0eb413dcf9290.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/dai-noi-hue.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/dai-noi-hue.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/chua-thien-mu.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/chua-thien-mu.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member04@hotspot.test') AND places.title IN ('[TEST] Hồ sơ REJECTED 08','Thiền viện Trúc Lâm Đà Lạt') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'68' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='68',title='Thiền viện Trúc Lâm Đà Lạt',description='Thiền viện nằm gần hồ Tuyền Lâm, có khuôn viên cây xanh và các công trình kiến trúc Phật giáo. Có thể kết hợp cùng hành trình khám phá cảnh quan phía nam Đà Lạt.',address='Đường Trúc Lâm Yên Tử, Đà Lạt, Lâm Đồng',latitude='11.9041575',longitude='108.4347865',rejection_reason='Bổ sung nội dung giới thiệu, tránh mô tả quá chung chung.' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/4344e07511beb75c02.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/4344e07511beb75c02.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ga-da-lat.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ga-da-lat.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/cho-da-lat.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/cho-da-lat.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member01@hotspot.test') AND places.title IN ('[TEST] Hồ sơ HIDDEN 09','Cầu đi bộ Ninh Kiều') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'92' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='92',title='Cầu đi bộ Ninh Kiều',description='Cây cầu đi bộ nối các không gian ven sông tại trung tâm Cần Thơ. Có thể kết hợp dạo bến Ninh Kiều, ngắm sông và chụp ảnh cảnh quan thành phố.',address='Khu vực bến Ninh Kiều, Cần Thơ',latitude='10.0370351',longitude='105.7912377' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ben-ninh-kieu.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ben-ninh-kieu.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/358de90dc272524754.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/358de90dc272524754.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/583f71f0e055ea0763.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/583f71f0e055ea0763.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='cuoi-tuan' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member02@hotspot.test') AND places.title IN ('[TEST] Hồ sơ HIDDEN 10','Nhà cổ Bình Thủy') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'92' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Văn hóa'),province_code='92',title='Nhà cổ Bình Thủy',description='Ngôi nhà cổ gắn với gia đình họ Dương, có sự kết hợp giữa kiến trúc và trang trí Đông – Tây. Đây là điểm tham quan để tìm hiểu nếp sống và không gian nhà ở Nam Bộ.',address='142–144 Bùi Hữu Nghĩa, Cần Thơ',latitude='10.067026',longitude='105.749572' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ben-ninh-kieu.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ben-ninh-kieu.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/358de90dc272524754.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/358de90dc272524754.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/583f71f0e055ea0763.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/583f71f0e055ea0763.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member03@hotspot.test') AND places.title IN ('[TEST] Hồ sơ HIDDEN 11','Bãi biển Non Nước') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'48' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Thiên nhiên'),province_code='48',title='Bãi biển Non Nước',description='Dải bãi biển ở phía nam Đà Nẵng, gần khu danh thắng Ngũ Hành Sơn. Không gian biển phù hợp cho các hoạt động ngoài trời và lịch trình khám phá cảnh quan ven biển.',address='Khu vực ven biển Ngũ Hành Sơn, Đà Nẵng',latitude='16.0056684',longitude='108.2687971' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/e33376aaa7cf5034ec.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/e33376aaa7cf5034ec.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/ngu-hanh-son.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/ngu-hanh-son.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/demo-real-20261107/bai-bien-my-khe.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/demo-real-20261107/bai-bien-my-khe.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='ngoai-troi' AND @hotspot_seed_place_id IS NOT NULL;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='view-dep' AND @hotspot_seed_place_id IS NOT NULL;

SET @hotspot_seed_place_id=(SELECT place_id FROM places WHERE places.owner_id=(SELECT user_id FROM users WHERE email='demo-20261107-member04@hotspot.test') AND places.title IN ('[TEST] Hồ sơ HIDDEN 12','Chợ Đông Ba') LIMIT 1);

UPDATE places SET ward_code=CASE WHEN province_code<>'46' THEN NULL ELSE ward_code END,category_id=(SELECT category_id FROM categories WHERE name='Du lịch'),province_code='46',title='Chợ Đông Ba',description='Khu chợ lớn ở trung tâm Huế, gần sông Hương và cầu Trường Tiền. Các gian hàng đặc sản, thực phẩm và đồ dùng tạo nên một điểm dừng tìm hiểu sinh hoạt, ẩm thực địa phương.',address='Đường Trần Hưng Đạo, Huế',latitude='16.4724474',longitude='107.5886452' WHERE place_id=@hotspot_seed_place_id;

UPDATE place_images SET image_path='assets/images/seed-real-20261108/ea14aaac1928c23b61.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=1 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/ea14aaac1928c23b61.webp',1 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=1);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/a79da77d17c315fcd8.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=2 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/a79da77d17c315fcd8.webp',2 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=2);

UPDATE place_images SET image_path='assets/images/seed-real-20261108/3347b23642a813745d.webp' WHERE place_id=@hotspot_seed_place_id AND sort_order=3 AND (image_path LIKE 'assets/images/demo-real-20261107/%' OR image_path LIKE 'assets/images/seed-real-20261108/%');

INSERT INTO place_images (place_id,image_path,sort_order) SELECT @hotspot_seed_place_id,'assets/images/seed-real-20261108/3347b23642a813745d.webp',3 FROM DUAL WHERE @hotspot_seed_place_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM place_images WHERE place_id=@hotspot_seed_place_id AND sort_order=3);

DELETE FROM place_tags WHERE place_id=@hotspot_seed_place_id;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='check-in' AND @hotspot_seed_place_id IS NOT NULL;

INSERT INTO place_tags (place_id,tag_id) SELECT @hotspot_seed_place_id,tag_id FROM tags WHERE slug='cuoi-tuan' AND @hotspot_seed_place_id IS NOT NULL;

COMMIT;
