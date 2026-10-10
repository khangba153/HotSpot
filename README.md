# Hot Spot

Dự án PHP thuần OOP + MVC, Bootstrap, JavaScript/AJAX; **MariaDB 10.4.32 đi kèm XAMPP** là engine chính thức theo quyết định khóa của nhóm ngày 10/10/2026. Giữ PHP PDO `pdo_mysql`, phpMyAdmin, Nominatim và Leaflet/OpenStreetMap. MySQL 8 không còn là điều kiện nghiệm thu.

## Chạy website hiện tại

Bật Apache + MySQL trong XAMPP, mở **http://localhost/hotspot/index.php?route=home**. Máy này đã cấu hình junction public và database demo mới; db_hospot giữ nguyên.

**A04+A05 chạy được:** home, PDO MariaDB, layout Bootstrap local, đăng ký/login/logout, Session Member/Admin, CSRF form, chặn Member vào admin và thông báo lỗi cơ bản. Admin hiện chỉ là cổng kiểm quyền, chưa có chức năng quản trị. Module địa điểm/search/upload/map/review/favorites/profile chưa triển khai.

[Hướng dẫn chạy và bàn giao 4 thành viên](docs/11_MVC_AUTH_HANDOFF.md). PHP website hỗ trợ XAMPP 8.0.30; config local ngoài Git. Ưu tiên chức năng đồ án trong 21 ngày, không mở rộng kiến trúc/test/CI khi chưa có nhu cầu.

Database A02/A03 đã kiểm chứng MariaDB 10.4.32: 13 bảng, 12 routine, 34 tỉnh/3.321 xã/phường/24 Place demo. [Báo cáo A03](docs/reports/A03_MARIADB.md), [A01/A02](docs/reports/A01_A02_MARIADB.md) giữ làm bằng chứng lịch sử.

- [Nghiệp vụ đã khóa](docs/01_REQUIREMENTS_AND_RULES.md), [Database](database/README.md).
- [Phân công và 21 ngày](docs/06_TEAM_PLAN.md).

## Cấu trúc

`app/` chứa Controller, Model, View và config; `public/` là document root dự kiến. `database/schema.sql` là schema chính thức A02; `tests/schema_mariadb.php` kiểm thử trực tiếp qua PDO. `docs/diagrams/*.mmd` là thiết kế đích từ foundation v3; PNG cũ giữ để đối chiếu. `.github/workflows/mariadb.yml` chạy lint và A02 với service MariaDB 10.4.32.

## Kiểm thử nhanh

Yêu cầu PHP >=8.1 với `pdo_mysql` và MariaDB **10.4.32** đang chạy localhost:3306. Từ thư mục repository:

```powershell
php tests/schema_mariadb.php
python -X utf8 tests/admin_seed_generator.py
php tests/routines_mariadb.php
```

Mỗi lượt tạo database `hotspot_test_a02_<UTC timestamp>_<random>` mới, kiểm thử fixture trong transaction rồi rollback. Không DROP database, không sửa `db_hospot`; database kiểm thử được giữ lại. Báo cáo JSON nằm ở `tests/results/` và không commit. Cần tài khoản local có quyền CREATE database và đọc metadata; cấu hình bằng biến môi trường, không đưa mật khẩu vào Git.

## Quy tắc cộng tác

13 bảng, 12 stored procedure (3/người), phân công và nghiệp vụ giữ nguyên. Khang: Auth/Profile/Favorites/core; TV2: lookup/search; TV3: Place/upload/map; TV4: toàn bộ Admin/Review. Cả nhóm tiếp tục phát triển trên shared MariaDB; kiểm thử ghi dữ liệu dùng database mới cô lập. Shared database chưa xác nhận tài khoản/kết nối của cả nhóm. Mọi thay đổi cấu trúc shared DB cần backup và xác nhận nhóm trưởng.

Issue → feature branch từ main → commit → PR → người khác review → CI/checklist → merge. Không push thẳng main, không commit config cá nhân. A01/A02 đã được nhóm trưởng chấp thuận local; A03 đã kiểm thử local trên branch riêng. GitHub xác thực đã kiểm chứng/dry-run pass; chưa push thật/PR/Actions. A03 đã được nhóm trưởng chấp thuận; A04+A05 hiện có commit local, chưa push thật/PR/merge.
