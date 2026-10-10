# Đính chính môi trường Hot Spot — 10/10/2026

> Cập nhật quyết định nhóm 10/10/2026: engine chính thức MariaDB 10.4.32 (XAMPP). Nội dung bên dưới là lịch sử audit trước A01/A02; kết quả mới xem `reports/A01_A02_MARIADB.md`.

Người dùng xác nhận dự án làm trong `D:\UEH\Dự án\HotSpot`, database quản lý qua `http://localhost/phpmyadmin/index.php?route=/server/databases`.

## Môi trường chính thức đã kiểm tra

- Repository thực tế: `D:\UEH\Dự án\HotSpot\HotSpot` (thư mục con), remote `https://github.com/khangba153/HotSpot.git`.
- Branch tài liệu audit: `docs/audit-current-environment`; chưa commit/push hoặc tạo PR.
- DB server tại `127.0.0.1:3306`: **10.4.32-MariaDB**, truy vấn `SELECT VERSION()` thành công. phpMyAdmin là công cụ quản trị; cần phân biệt với engine MariaDB đang chạy.
- Database **`db_hospot`** tồn tại, có **10 bảng / 12 stored procedure**; `users` và `places` hiện có 0 dòng. Các bảng khác chưa kiểm đếm dữ liệu.
- Các truy vấn DB của lượt này chỉ SELECT metadata/count; không CALL procedure, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP hoặc import.

## Không áp dụng nhầm kết luận báo cáo trước

Báo cáo trước audit bộ `HotSpot_Project_Foundation_v3` trong Downloads và chạy SQL của bộ đó trên MySQL 8.0.46 cô lập. Lỗi 3823, 30 PHP lint pass và HTTP smoke pass là kết quả của **bộ v3**, không phải kết quả của repository hoặc MariaDB hiện tại.

Bộ v3 vẫn là nguồn yêu cầu đã khóa theo chỉ dẫn ban đầu: 13 bảng, 12 procedure, phân công 4 người, Nominatim/Leaflet, shared DB, PR/review và 21 ngày. Không tự thay các quyết định đó bằng README cũ.

## Sai khác của repository hiện tại

- README mô tả SQL Server, districts, phân công Review cho TV3 và map tùy chọn: khác yêu cầu v3 đã khóa.
- Controllers/Models/helpers chỉ có phương thức rỗng. Không có xử lý nghiệp vụ được triển khai trong các phương thức đã đọc.
- `public/index.php`, `app/config/database.php`, `app/views/layouts/main.php`, `database/schema.sql`, `database/seed.sql`, `docs/setup.md`, `.gitignore` đều rỗng.
- `app/config/database.php` hiện được Git theo dõi; tuyệt đối không điền thông tin kết nối trước khi có quy tắc ignore và bỏ theo dõi phù hợp.
- Chưa có Router/PDO/Session/CSRF hoạt động, tools/tests/CI như bộ v3. PHP lint không chứng minh ứng dụng chạy vì nhiều file/phương thức rỗng.
- `services/ProductService.php` rỗng và không thuộc kiến trúc nghiệp vụ v3; chưa thay đổi/xóa.

## Database hiện tại

Bảng: `categories`, `districts`, `favorites`, `places`, `place_images`, `place_statuses`, `reviews`, `review_statuses`, `roles`, `users`.

Procedure:

- `BTCK_CREATE_USER`, `BTCK_GET_USER_BY_EMAIL`, `BTCK_UPDATE_USER_PROFILE`
- `CT_LIST_ACTIVE_CATEGORIES`, `CT_SEARCH_APPROVED_PLACES`, `CT_UPDATE_PLACE_STATUS`
- `NDHT_HIDE_REVIEW`, `NDHT_LIST_MY_REVIEWED_PLACES`, `NDHT_SAVE_OR_UPDATE_REVIEW`
- `NPT_ADD_PLACE_IMAGE`, `NPT_SET_FAVORITE`, `NPT_SUBMIT_PLACE`

Số procedure đúng 12 nhưng tên/phân công/chức năng khác v3; không thể xem là đạt yêu cầu chỉ nhờ đủ số lượng. Lượt đối chiếu tiếp theo đã đọc đủ thân/parameters 12 procedure; xem [báo cáo đối chiếu](audit/COMPARISON_AND_PLAN.md).

Metadata constraints hiện không có UNIQUE(user,place) trên reviews hoặc UNIQUE(place,sort) trên place_images; không thấy CHECK trong danh sách constraints. PK/FK cơ bản tồn tại. Cần audit SHOW CREATE TABLE/PROCEDURE đầy đủ để lập phương án chuyển đổi chính xác; chưa nghiệm thu database hiện tại.

## Thứ tự tiếp theo

1. Tiếp tục audit đầy đủ cấu trúc/procedure MariaDB hiện tại bằng thao tác chỉ đọc; lưu schema metadata để so sánh v3, tránh xuất dữ liệu/mật khẩu nhạy cảm vào Git.
2. Lập đề xuất đưa nền tảng v3 vào repository chính thức, giữ nguyên các quyết định khóa; xác định khác biệt tương thích MySQL/MariaDB và các thay đổi DB cần thiết. Chưa tự áp dụng.
3. Sau khi nhóm trưởng xác nhận triển khai: sửa/tích hợp nền tảng trên feature branch, kiểm thử database cô lập đúng engine/version dự kiến. Backup và xác nhận thao tác trước bất kỳ thay đổi cấu trúc DB hiện tại, kể cả khi bảng users/places đang trống.
4. Mỗi thay đổi qua PR/review; không overwrite toàn bộ repository hoặc import v3 thẳng vào `db_hospot`.

## Bàn giao lượt đính chính

Đã xác minh thư mục/repo/server/database bằng đọc file và truy vấn SELECT. Chưa sửa source/SQL hoặc cấu hình kết nối, chưa thay đổi dữ liệu DB. Lint 13/13 PHP pass; trang chủ HTTP200 nhưng body rỗng. Lượt tiếp theo chỉ bổ sung tài liệu/công cụ/bằng chứng trong docs/audit; xem báo cáo đối chiếu để biết kết quả mới nhất.
