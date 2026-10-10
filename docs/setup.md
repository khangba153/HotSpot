> Website A04/A05 đã có hướng dẫn chạy XAMPP tại **11_MVC_AUTH_HANDOFF.md**, PHP 8.0.30 đã kiểm chứng. Nội dung kiểm thử database bên dưới giữ làm tài liệu A02/A03; không yêu cầu tăng tests/CI cho mọi task.

# Thiết lập MariaDB/XAMPP và kiểm thử A02

## Môi trường chính thức

MariaDB 10.4.32 (XAMPP), InnoDB, utf8mb4_unicode_ci; PHP >=8.1 + PDO pdo_mysql. Nút MySQL trong XAMPP chỉ là tên trên giao diện; xác minh engine bằng `SELECT VERSION()`. phpMyAdmin tại http://localhost/phpmyadmin/index.php?route=/server/databases là công cụ quản trị.

Không thay engine sang SQL Server hoặc yêu cầu MySQL 8. `db_hospot` cũ chỉ đọc để đối chiếu. Không import SQL vào database này.

## Config cá nhân

```powershell
Copy-Item app/config/database.example.php app/config/database.php
php -m
```

Chỉ sao chép nếu chưa có config local cần giữ. File mẫu không có mật khẩu thật. File `database.php` bị ignore; thiết lập HOTSPOT_DB_HOST/PORT/NAME/USER/PASSWORD/SSL_CA bằng môi trường hoặc config local. Đây là hợp đồng cho core PDO A04; core chưa được triển khai trong A02. Không dùng root cho ứng dụng/shared DB.

## Chạy A02

```powershell
$env:HOTSPOT_TEST_DB_HOST = '127.0.0.1'
$env:HOTSPOT_TEST_DB_PORT = '3306'
$env:HOTSPOT_TEST_DB_USER = 'root'
php tests/schema_mariadb.php
```

HOTSPOT_TEST_DB_PASSWORD lấy từ môi trường cục bộ nếu cần; không ghi vào script/Git. Root ở đây chỉ là tài khoản kiểm thử local hiện có, không phải config ứng dụng. Runner chỉ chấp nhận localhost/127.0.0.1 và đúng MariaDB 10.4.32; tự tạo database mới ngẫu nhiên, không tái sử dụng DB cũ. Fixture rollback; không tự DROP database. Xem tên DB và report JSON được in cuối lệnh. Mã thoát 0 chỉ khi mọi kiểm tra pass.

Import thủ công chỉ được chọn một database kiểm thử **mới, rỗng**, sau đó import `database/schema.sql`. Không chạy trên shared DB hoặc db_hospot. Runner A02 là cách kiểm chứng đầy đủ, bao gồm negative cases và CHECK/FK enforcement. Không tắt foreign_key_checks/check_constraint_checks.

## CI

Workflow `.github/workflows/mariadb.yml` dùng MariaDB 10.4.32 service và PDO. Runner tạo database mới như local; server CI không có db_hospot, kiểm tra draft được đánh dấu absent. Upload báo cáo JSON kể cả thất bại. CI cấu hình schema A02 và A03 seed/12 CALL/rollback/result-set trên cùng MariaDB; chưa có kết quả Actions remote, không kiểm thử MVC.

## Shared database

Giữ nguyên quy trình chung của 4 người; local/CI chỉ là môi trường kiểm thử cô lập. Trước thay đổi cấu trúc shared DB: xác định đích, backup, xin xác nhận nhóm trưởng, review SQL rồi một người điều phối áp dụng. A02 không thay đổi shared DB và không chứng minh kết nối từ máy thành viên khác.

## A03 — seed/routine local

Cần PHP intl/mbstring để sinh seed NFC. Chạy `python -X utf8 tests/admin_seed_generator.py` và `php tests/routines_mariadb.php`. Runner tự tạo database A03 mới; seed commit, mọi fixture test rollback; giữ database để đối chiếu. Xem `../database/README.md` cho hợp đồng caller transaction/2 rowsets/cursor và `reports/A03_MARIADB.md` cho 158/158 kết quả. CI hiện có cả A02/A03/generator nhưng chưa chạy remote.
