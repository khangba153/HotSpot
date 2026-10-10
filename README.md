# Hot Spot

Dự án PHP thuần OOP + MVC, Bootstrap, JavaScript/AJAX; **MariaDB 10.4.32 đi kèm XAMPP** là engine chính thức theo quyết định khóa của nhóm ngày 10/10/2026. Giữ PHP PDO `pdo_mysql`, phpMyAdmin, Nominatim và Leaflet/OpenStreetMap. MySQL 8 không còn là điều kiện nghiệm thu.

## Trạng thái thực tế

A01 đã bảo vệ config cá nhân khỏi Git. A02 đã import schema và kiểm thử trên MariaDB 10.4.32: **64/64 pass**, giữ 13 bảng, 13 PK, 15 FK, 7 UNIQUE, 15 CHECK. MVC hiện tại vẫn là skeleton; trang web chưa sẵn sàng sử dụng. `database/seed.sql` chưa triển khai, chưa có 12 routine chính thức trong repo. Không chạy seed rỗng rồi coi là đã có dữ liệu.

- [Báo cáo A01/A02](docs/reports/A01_A02_MARIADB.md).
- [Hướng dẫn môi trường và kiểm thử](docs/setup.md).
- [Kiến trúc và trạng thái triển khai](docs/10_ARCHITECTURE_AND_IMPLEMENTATION.md).
- [Nghiệp vụ đã khóa](docs/01_REQUIREMENTS_AND_RULES.md), [13 bảng và 12 routine](docs/02_DATABASE_ERD.md).
- [Phân công 4 người, GitHub PR/review và 21 ngày](docs/06_TEAM_PLAN.md).
- [Audit baseline, task và phụ thuộc](docs/audit/COMPARISON_AND_PLAN.md).

## Cấu trúc

`app/` chứa Controller, Model, View và config; `public/` là document root dự kiến. `database/schema.sql` là schema chính thức A02; `tests/schema_mariadb.php` kiểm thử trực tiếp qua PDO. `docs/diagrams/*.mmd` là thiết kế đích từ foundation v3; PNG cũ giữ để đối chiếu. `.github/workflows/mariadb.yml` chạy lint và A02 với service MariaDB 10.4.32.

## Kiểm thử nhanh

Yêu cầu PHP >=8.1 với `pdo_mysql` và MariaDB **10.4.32** đang chạy localhost:3306. Từ thư mục repository:

```powershell
php tests/schema_mariadb.php
```

Mỗi lượt tạo database `hotspot_test_a02_<UTC timestamp>_<random>` mới, kiểm thử fixture trong transaction rồi rollback. Không DROP database, không sửa `db_hospot`; database kiểm thử được giữ lại. Báo cáo JSON nằm ở `tests/results/` và không commit. Cần tài khoản local có quyền CREATE database và đọc metadata; cấu hình bằng biến môi trường, không đưa mật khẩu vào Git.

## Quy tắc cộng tác

13 bảng, 12 stored procedure (3/người), phân công và nghiệp vụ giữ nguyên. Khang: Auth/Profile/Favorites/core; TV2: lookup/search; TV3: Place/upload/map; TV4: toàn bộ Admin/Review. Cả nhóm tiếp tục phát triển trên shared MariaDB; kiểm thử ghi dữ liệu dùng database mới cô lập. Shared database chưa xác nhận tài khoản/kết nối của cả nhóm. Mọi thay đổi cấu trúc shared DB cần backup và xác nhận nhóm trưởng.

Issue → feature branch từ main → commit → PR → người khác review → CI/checklist → merge. Không push thẳng main, không commit config cá nhân. A03 chỉ bắt đầu sau báo cáo A02 được nhóm trưởng xem xét và bước GitHub review được xử lý.
