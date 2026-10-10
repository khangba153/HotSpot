# Báo cáo A01 → A02 — MariaDB chính thức

Ngày 10/10/2026. Repo `D:\UEH\Dự án\HotSpot\HotSpot`. Branch `feature/mariadb-foundation-a01-a02`, dựa trên origin/main `db39d65`. Báo cáo này cập nhật trạng thái của audit baseline, không tuyên bố chức năng MVC đã chạy.

## 1. Đã thực hiện

Ghi nhận MariaDB 10.4.32 (XAMPP) là quyết định khóa, thay MySQL 8. README/tài liệu kiến trúc/ERD/phân công/kế hoạch/test acceptance chuyển engine chính thức sang MariaDB; giữ shared DB, PDO pdo_mysql, phpMyAdmin và toàn bộ phạm vi nghiệp vụ.

A01: config mẫu dùng biến môi trường, không có secret; bỏ theo dõi `app/config/database.php` nhưng giữ file local. Ignore config/env/uploads/cache/logs/report/backup. Feature branch riêng, không sửa main trực tiếp. A01 đã commit `7677c15`; A02 có commit riêng trên cùng branch. Working tree sạch sau commit; config local và JSON kết quả bị ignore.

A02: tái sử dụng schema v3 thay file rỗng của repository. MariaDB import đủ 13 bảng; giữ 13 PK, 15 FK, 7 UNIQUE, 15 CHECK, các FK CASCADE/RESTRICT và cặp province/ward. Bật strict SQL mode và UTC trong connection import. CHECK chuỗi/lý do dùng REGEXP để thực thi quy tắc không chỉ khoảng trắng. Không bỏ bất kỳ ràng buộc nào để import. Schema v3 đã chạy trên MariaDB dù audit MySQL 8 trước đó có lỗi 3823; lỗi lịch sử đó không là gate MariaDB.

Runner chỉ ghi vào database mới tự tạo trên localhost; dữ liệu fixture trong transaction và rollback. Không import, CALL, UPDATE hoặc DDL vào db_hospot. Fingerprint trước/sau kiểm tra metadata/routine body/số dòng, không chứng minh checksum từng nội dung bản ghi; DB nháp hiện trống theo audit baseline.

## 2. File thay đổi

| File/nhóm | Mục đích |
|---|---|
| .gitignore, app/config/database.example.php; untrack database.php | Bảo vệ config cá nhân, hợp đồng PDO |
| database/schema.sql | Schema 13 bảng MariaDB, giữ constraints và business invariants |
| tests/schema_mariadb.php, tests/results/.gitkeep | Import mới, positive/negative/boundary/rollback và đối chiếu draft |
| .github/workflows/mariadb.yml | PHP lint + MariaDB 10.4.32 service + report artifact |
| README.md, docs/setup.md, docs/01–10, docs/diagrams/*.mmd | Quyết định khóa, thiết kế đích, hướng dẫn và trạng thái thực tế |
| docs/audit/*, AUDIT_ENVIRONMENT_CORRECTION.md | Lưu audit trước đó, cập nhật engine/gate; metadata lịch sử giữ nguyên |
| Báo cáo này | Kết quả chạy thật và giới hạn |

Giữ nguyên Controller/Model/View/helper/front controller và seed placeholder; không triển khai module mới, không thay PNG lịch sử, không đưa routine vào trước A03.

## 3. Kiểm thử thực tế

Môi trường PHP CLI 8.4 với pdo_mysql; server trả `10.4.32-MariaDB` (XAMPP localhost:3306).

| Lệnh | Kết quả |
|---|---|
| php tests/schema_mariadb.php — lần đầu | 60/64 pass, 4 fail: so sánh thứ tự tên bảng do collation và 3 ca chỉ tab/newline được chấp nhận |
| php tests/schema_mariadb.php — sau sửa | **64/64 PASS**, exit 0 |
| PHP lint toàn app/public/tests | **14/14 pass**, bao gồm config local rỗng; không chứng minh MVC hoạt động |
| git diff --check | Pass |
| Kiểm tra parity schema so với v3 | Tên bảng, tên constraints, hành vi FK giữ nguyên |
| Parse YAML workflow | Pass (PyYAML cài tạm ngoài repo); chưa chạy Actions |
| git check-ignore config/report | Pass; file config local giữ nguyên ngoài Git |

Database lần đầu: `hotspot_test_a02_20261010_121236_1f7e0d20`.
Database sau sửa: **`hotspot_test_a02_20261010_121432_20f259a1`**.
Cả hai được giữ lại; fixture rollback và 13 bảng không còn dòng fixture. Báo cáo chi tiết JSON local tại `tests/results/<tên database>.json`, bị ignore để không commit dữ liệu môi trường.

64 kiểm tra gồm version/enforcement/mode/timezone, 13 bảng/InnoDB/collation, số constraints, 15 negative FK, 15 CHECK + whitespace regression, UNIQUE/PK, RESTRICT, NOT NULL/strict length, tọa độ biên/cặp NULL, province/ward, lý do hợp lệ và 1–3 ảnh/ảnh thứ 4 bị từ chối. Draft fingerprint trước/sau bằng nhau. A02 có **0 routine** trong database test vì 12 routine và CALL thuộc A03; không có kết quả kiểm thử stored procedure chính thức ở lượt này.

## 4. Vấn đề còn lại

CI đã cấu hình nhưng chưa có kết quả GitHub Actions; local pass không thay thế remote CI/review. Git push --dry-run và git push -u origin feature/mariadb-foundation-a01-a02 đều đã thử và thất bại vì GitHub chưa có credential (cannot read Username, terminal prompts disabled). Chưa tạo Issue/PR, chưa push, review hoặc merge. Cần người dùng đăng nhập GitHub để hoàn tất bước này. Shared database và quyền của 4 thành viên chưa kiểm chứng. PHP XAMPP web SAPI/chức năng trang web chưa test ở A02; source vẫn skeleton.

A03 còn phải kiểm cú pháp 12 procedure trên MariaDB 10.4.32: SIGNAL, SELECT/UPDATE, upsert, parameter types, result sets/cursor PDO, SQL mode tạo routine, transaction không COMMIT, quyền/trạng thái và seed. Không suy từ schema pass sang routine pass. Các invariant liên bảng tối thiểu 1 ảnh/public, tối đa 5 tag/quyền/chuyển trạng thái chưa được thực thi bởi schema đơn thuần.

## 5. Bước tiếp theo

Hoàn tất feature PR/review/CI, nhóm trưởng xem báo cáo A02, sau đó mới A03. A03 dùng database mới, không đụng db_hospot; kiểm chứng seed/12 CALL đủ 3/người trên MariaDB 10.4.32. Chưa triển khai A03 trong lượt này.
