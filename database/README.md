# Database — MariaDB 10.4.32 (XAMPP)

Giữ nguyên 13 bảng và 12 stored procedure (3/người). PHP sử dụng PDO `pdo_mysql`. Không import vào `db_hospot` cũ hoặc ghi đè database đã có dữ liệu.

## Import trên máy mới

Trong phpMyAdmin, chọn một database phát triển/demo **mới và rỗng**, dùng `utf8mb4_unicode_ci`. Import lần lượt:

1. `schema.sql`: 13 bảng và PK/FK/UNIQUE/CHECK.
2. `seed.sql`: dữ liệu trạng thái, tài khoản bcrypt và demo.
3. `seed_admin_full.sql`: 34 tỉnh/thành và 3.321 xã/phường.
4. `routines.sql`: bản gộp 12 stored procedure.

Có thể dùng bốn file `routines_member*.sql` thay cho bước 4; **không import cả bản gộp lẫn bản chia người**. Giữ cả hai dạng để import chung và tham khảo phần mỗi thành viên. Không tắt FK/CHECK. Khi viết lớp PDO, dùng cấu hình `app/config/database.php` trỏ vào database đã import. Backup trước mọi thay đổi trên database dùng chung.

Seed ban đầu có 3.488 dòng: 34 tỉnh, 3.321 xã/phường, 3 tài khoản, 24 địa điểm, 24 ảnh, 24 liên kết tag, 18 review, 18 favorite và dữ liệu lookup. Chức năng đăng ký chưa được triển khai trong bộ khung. Địa điểm là demo giả lập; path ảnh demo chưa có file ảnh tương ứng.

## 12 routine đã chốt

| Người | Procedure |
|---|---|
| Khang | `KH_CREATE_USER`, `KH_GET_USER_BY_EMAIL`, `KH_SET_FAVORITE` |
| TV2 | `TV2_LIST_ACTIVE_CATEGORIES`, `TV2_SEARCH_APPROVED_PLACES`, `TV2_LIST_WARDS_BY_PROVINCE` |
| TV3 | `TV3_SUBMIT_PLACE`, `TV3_ADD_PLACE_IMAGE`, `TV3_RESUBMIT_PLACE` |
| TV4 | `TV4_SAVE_OR_UPDATE_REVIEW`, `TV4_SET_REVIEW_STATUS`, `TV4_SET_PLACE_STATUS` |

## Gọi từ PHP

8 routine ghi yêu cầu `PDO::beginTransaction()` trước CALL; Model quyết định `commit()` hoặc `rollBack()` toàn bộ chuỗi khi lỗi. Procedure không tự COMMIT/ROLLBACK. Tạo routine cần quyền CREATE ROUTINE; gọi `SQL SECURITY INVOKER` cần EXECUTE và quyền dữ liệu tương ứng.

Dùng prepared statement. Drain các result set bằng `nextRowset()` và đóng cursor trước CALL tiếp theo. `TV2_SEARCH_APPROVED_PLACES` trả hai result set: trang dữ liệu và `total_count`; PDO còn trả gói kết thúc CALL. Kiểm tra `columnCount()` và `getColumnMeta(0)` trước khi đọc dữ liệu.

Actor ID lấy từ Session, không lấy từ request. PHP vẫn chịu trách nhiệm xác thực, password hashing, CSRF, kiểm quyền HTTP, tối thiểu một ảnh khi hoàn tất submit, tối đa 5 tag và kiểm tra/dọn file upload. Routine kiểm owner/admin theo ID nhưng không xác thực người dùng HTTP.

## Nguồn seed hành chính

Snapshot offline và thông tin commit/SHA256 nằm trong `sources/`. Đây là dữ liệu nguồn để tái tạo seed, không phải kết quả test. Import bằng SQL đã có; file `tools/generate_admin_seed.php` hiện chỉ là khung rỗng, không sinh lại seed. Seed hiện có giữ mã chuỗi, normalize NFC và loại đơn vị, bao gồm đặc khu Hoàng Sa. Chi tiết nguồn và giới hạn đối chiếu nằm trong `sources/manifest.json`.
