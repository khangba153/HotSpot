-- Hot Spot seed (run ONCE on an EMPTY schema, BEFORE generated seed_admin_full.sql)
-- All places and addresses below are FICTIONAL DEMO DATA, not real businesses.
-- Demo accounts contain fixture bcrypt hashes only; regenerate locally before any web deployment.
-- Photo records below point to the planned demo placeholder path; no images bundled in the DB stage.
SET NAMES utf8mb4;
SET time_zone = '+00:00';
INSERT INTO roles(role_code,role_name) VALUES ('ADMIN','Quản trị viên'),('MEMBER','Thành viên');
INSERT INTO place_statuses(status_code,display_name,is_public) VALUES
('PENDING','Chờ duyệt',0),('APPROVED','Đã duyệt',1),('REJECTED','Từ chối',0),('HIDDEN','Đã ẩn',0);
INSERT INTO review_statuses(status_code,display_name,is_public) VALUES
('VISIBLE','Hiển thị',1),('HIDDEN','Đã ẩn',0);
INSERT INTO categories(category_id,name,is_active) VALUES
(1,'Ăn uống',1),(2,'Cà phê',1),(3,'Du lịch',1),(4,'Vui chơi',1),(5,'Thiên nhiên',1),(6,'Văn hóa',1);
INSERT INTO tags(tag_id,name,slug,is_active) VALUES
(1,'Check-in','check-in',1),(2,'Yên tĩnh','yen-tinh',1),(3,'Gia đình','gia-dinh',1),
(4,'Ngoài trời','ngoai-troi',1),(5,'Giá hợp lý','gia-hop-ly',1),(6,'View đẹp','view-dep',1),
(7,'Đi nhóm','di-nhom',1),(8,'Cuối tuần','cuoi-tuan',1);

INSERT INTO provinces(province_code,province_name,is_active) VALUES
('01','Thành phố Hà Nội',1),
('04','Tỉnh Cao Bằng',1),
('08','Tỉnh Tuyên Quang',1),
('11','Tỉnh Điện Biên',1),
('12','Tỉnh Lai Châu',1),
('14','Tỉnh Sơn La',1),
('15','Tỉnh Lào Cai',1),
('19','Tỉnh Thái Nguyên',1),
('20','Tỉnh Lạng Sơn',1),
('22','Tỉnh Quảng Ninh',1),
('24','Tỉnh Bắc Ninh',1),
('25','Tỉnh Phú Thọ',1),
('31','Thành phố Hải Phòng',1),
('33','Tỉnh Hưng Yên',1),
('37','Tỉnh Ninh Bình',1),
('38','Tỉnh Thanh Hóa',1),
('40','Tỉnh Nghệ An',1),
('42','Tỉnh Hà Tĩnh',1),
('44','Tỉnh Quảng Trị',1),
('46','Thành phố Huế',1),
('48','Thành phố Đà Nẵng',1),
('51','Tỉnh Quảng Ngãi',1),
('52','Tỉnh Gia Lai',1),
('56','Tỉnh Khánh Hòa',1),
('66','Tỉnh Đắk Lắk',1),
('68','Tỉnh Lâm Đồng',1),
('75','Tỉnh Đồng Nai',1),
('79','Thành phố Hồ Chí Minh',1),
('80','Tỉnh Tây Ninh',1),
('82','Tỉnh Đồng Tháp',1),
('86','Tỉnh Vĩnh Long',1),
('91','Tỉnh An Giang',1),
('92','Thành phố Cần Thơ',1),
('96','Tỉnh Cà Mau',1);

INSERT INTO users(user_id,role_code,full_name,email,password_hash) VALUES
(1,'ADMIN','Quản trị Demo','admin@hotspot.test','$2y$12$bUOdU6rq.hekKC9ynQBDCe.IOCExtO2D3oKH8O0zmfx7uk6aQpH/G'),
(2,'MEMBER','Thành viên Demo A','member1@hotspot.test','$2y$12$bUOdU6rq.hekKC9ynQBDCe.IOCExtO2D3oKH8O0zmfx7uk6aQpH/G'),
(3,'MEMBER','Thành viên Demo B','member2@hotspot.test','$2y$12$bUOdU6rq.hekKC9ynQBDCe.IOCExtO2D3oKH8O0zmfx7uk6aQpH/G');

INSERT INTO places(place_id,owner_id,category_id,province_code,ward_code,status_code,title,description,address,latitude,longitude,rejection_reason) VALUES
(1,2,1,'79',NULL,'APPROVED','Địa điểm demo 01 - TP.HCM','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #01.','Địa chỉ minh họa số 1, TP.HCM',NULL,NULL,NULL),
(2,3,2,'01',NULL,'APPROVED','Địa điểm demo 02 - Hà Nội','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #02.','Địa chỉ minh họa số 2, Hà Nội',NULL,NULL,NULL),
(3,2,3,'48',NULL,'APPROVED','Địa điểm demo 03 - Đà Nẵng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #03.','Địa chỉ minh họa số 3, Đà Nẵng',NULL,NULL,NULL),
(4,3,4,'92',NULL,'APPROVED','Địa điểm demo 04 - Cần Thơ','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #04.','Địa chỉ minh họa số 4, Cần Thơ',NULL,NULL,NULL),
(5,2,5,'46',NULL,'APPROVED','Địa điểm demo 05 - Huế','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #05.','Địa chỉ minh họa số 5, Huế',NULL,NULL,NULL),
(6,3,6,'56',NULL,'APPROVED','Địa điểm demo 06 - Khánh Hòa','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #06.','Địa chỉ minh họa số 6, Khánh Hòa',NULL,NULL,NULL),
(7,2,1,'68',NULL,'APPROVED','Địa điểm demo 07 - Lâm Đồng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #07.','Địa chỉ minh họa số 7, Lâm Đồng',NULL,NULL,NULL),
(8,3,2,'31',NULL,'APPROVED','Địa điểm demo 08 - Hải Phòng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #08.','Địa chỉ minh họa số 8, Hải Phòng',NULL,NULL,NULL),
(9,2,3,'79',NULL,'APPROVED','Địa điểm demo 09 - TP.HCM','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #09.','Địa chỉ minh họa số 9, TP.HCM',NULL,NULL,NULL),
(10,3,4,'01',NULL,'APPROVED','Địa điểm demo 10 - Hà Nội','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #10.','Địa chỉ minh họa số 10, Hà Nội',NULL,NULL,NULL),
(11,2,5,'48',NULL,'APPROVED','Địa điểm demo 11 - Đà Nẵng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #11.','Địa chỉ minh họa số 11, Đà Nẵng',NULL,NULL,NULL),
(12,3,6,'92',NULL,'APPROVED','Địa điểm demo 12 - Cần Thơ','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #12.','Địa chỉ minh họa số 12, Cần Thơ',NULL,NULL,NULL),
(13,2,1,'46',NULL,'APPROVED','Địa điểm demo 13 - Huế','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #13.','Địa chỉ minh họa số 13, Huế',NULL,NULL,NULL),
(14,3,2,'56',NULL,'APPROVED','Địa điểm demo 14 - Khánh Hòa','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #14.','Địa chỉ minh họa số 14, Khánh Hòa',NULL,NULL,NULL),
(15,2,3,'68',NULL,'APPROVED','Địa điểm demo 15 - Lâm Đồng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #15.','Địa chỉ minh họa số 15, Lâm Đồng',NULL,NULL,NULL),
(16,3,4,'31',NULL,'APPROVED','Địa điểm demo 16 - Hải Phòng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #16.','Địa chỉ minh họa số 16, Hải Phòng',NULL,NULL,NULL),
(17,2,5,'79',NULL,'APPROVED','Địa điểm demo 17 - TP.HCM','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #17.','Địa chỉ minh họa số 17, TP.HCM',NULL,NULL,NULL),
(18,3,6,'01',NULL,'APPROVED','Địa điểm demo 18 - Hà Nội','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #18.','Địa chỉ minh họa số 18, Hà Nội',NULL,NULL,NULL),
(19,2,1,'48',NULL,'PENDING','Địa điểm demo 19 - Đà Nẵng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #19.','Địa chỉ minh họa số 19, Đà Nẵng',NULL,NULL,NULL),
(20,3,2,'92',NULL,'PENDING','Địa điểm demo 20 - Cần Thơ','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #20.','Địa chỉ minh họa số 20, Cần Thơ',NULL,NULL,NULL),
(21,2,3,'46',NULL,'PENDING','Địa điểm demo 21 - Huế','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #21.','Địa chỉ minh họa số 21, Huế',NULL,NULL,NULL),
(22,3,4,'56',NULL,'REJECTED','Địa điểm demo 22 - Khánh Hòa','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #22.','Địa chỉ minh họa số 22, Khánh Hòa',NULL,NULL,'Thiếu thông tin minh họa'),
(23,2,5,'68',NULL,'REJECTED','Địa điểm demo 23 - Lâm Đồng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #23.','Địa chỉ minh họa số 23, Lâm Đồng',NULL,NULL,'Thiếu thông tin minh họa'),
(24,3,6,'31',NULL,'HIDDEN','Địa điểm demo 24 - Hải Phòng','Dữ liệu giả lập phục vụ kiểm thử chức năng, không đại diện địa điểm có thật. Mẫu #24.','Địa chỉ minh họa số 24, Hải Phòng',NULL,NULL,NULL);

-- The photo path is a future frontend placeholder, not a real uploaded photo.
-- Replace with locally stored test photos before visual demo.

INSERT INTO place_images(place_id,image_path,sort_order) VALUES
(1,'assets/images/demo-placeholder.svg',1),
(2,'assets/images/demo-placeholder.svg',1),
(3,'assets/images/demo-placeholder.svg',1),
(4,'assets/images/demo-placeholder.svg',1),
(5,'assets/images/demo-placeholder.svg',1),
(6,'assets/images/demo-placeholder.svg',1),
(7,'assets/images/demo-placeholder.svg',1),
(8,'assets/images/demo-placeholder.svg',1),
(9,'assets/images/demo-placeholder.svg',1),
(10,'assets/images/demo-placeholder.svg',1),
(11,'assets/images/demo-placeholder.svg',1),
(12,'assets/images/demo-placeholder.svg',1),
(13,'assets/images/demo-placeholder.svg',1),
(14,'assets/images/demo-placeholder.svg',1),
(15,'assets/images/demo-placeholder.svg',1),
(16,'assets/images/demo-placeholder.svg',1),
(17,'assets/images/demo-placeholder.svg',1),
(18,'assets/images/demo-placeholder.svg',1),
(19,'assets/images/demo-placeholder.svg',1),
(20,'assets/images/demo-placeholder.svg',1),
(21,'assets/images/demo-placeholder.svg',1),
(22,'assets/images/demo-placeholder.svg',1),
(23,'assets/images/demo-placeholder.svg',1),
(24,'assets/images/demo-placeholder.svg',1);

INSERT INTO place_tags(place_id,tag_id) VALUES
(1,1),
(2,2),
(3,3),
(4,4),
(5,5),
(6,6),
(7,7),
(8,8),
(9,1),
(10,2),
(11,3),
(12,4),
(13,5),
(14,6),
(15,7),
(16,8),
(17,1),
(18,2),
(19,3),
(20,4),
(21,5),
(22,6),
(23,7),
(24,8);

INSERT INTO reviews(user_id,place_id,status_code,rating,content) VALUES
(3,1,'VISIBLE',2,'Bình luận demo hợp lệ cho địa điểm số 1.'),
(2,2,'VISIBLE',3,'Bình luận demo hợp lệ cho địa điểm số 2.'),
(3,3,'HIDDEN',4,'Bình luận demo hợp lệ cho địa điểm số 3.'),
(2,4,'VISIBLE',5,'Bình luận demo hợp lệ cho địa điểm số 4.'),
(3,5,'VISIBLE',1,'Bình luận demo hợp lệ cho địa điểm số 5.'),
(2,6,'VISIBLE',2,'Bình luận demo hợp lệ cho địa điểm số 6.'),
(3,7,'VISIBLE',3,'Bình luận demo hợp lệ cho địa điểm số 7.'),
(2,8,'VISIBLE',4,'Bình luận demo hợp lệ cho địa điểm số 8.'),
(3,9,'VISIBLE',5,'Bình luận demo hợp lệ cho địa điểm số 9.'),
(2,10,'VISIBLE',1,'Bình luận demo hợp lệ cho địa điểm số 10.'),
(3,11,'VISIBLE',2,'Bình luận demo hợp lệ cho địa điểm số 11.'),
(2,12,'VISIBLE',3,'Bình luận demo hợp lệ cho địa điểm số 12.'),
(3,13,'VISIBLE',4,'Bình luận demo hợp lệ cho địa điểm số 13.'),
(2,14,'HIDDEN',5,'Bình luận demo hợp lệ cho địa điểm số 14.'),
(3,15,'VISIBLE',1,'Bình luận demo hợp lệ cho địa điểm số 15.'),
(2,16,'VISIBLE',2,'Bình luận demo hợp lệ cho địa điểm số 16.'),
(3,17,'VISIBLE',3,'Bình luận demo hợp lệ cho địa điểm số 17.'),
(2,18,'VISIBLE',4,'Bình luận demo hợp lệ cho địa điểm số 18.');

INSERT INTO favorites(user_id,place_id) VALUES
(2,1),
(3,2),
(2,3),
(3,4),
(2,5),
(3,6),
(2,7),
(3,8),
(2,9),
(3,10),
(2,11),
(3,12),
(2,13),
(3,14),
(2,15),
(3,16),
(2,17),
(3,18);
