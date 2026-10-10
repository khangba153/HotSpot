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

Workflow `.github/workflows/mariadb.yml` dùng MariaDB 10.4.32 service và PDO. Runner tạo database mới như local; server CI không có db_hospot, kiểm tra draft được đánh dấu absent. Upload báo cáo JSON kể cả thất bại. CI kiểm thử schema A02, chưa kiểm thử routine hoặc MVC. A03 sẽ thêm 12 CALL, rollback/result-set và seed tests trên cùng phiên bản MariaDB.

## Shared database

Giữ nguyên quy trình chung của 4 người; local/CI chỉ là môi trường kiểm thử cô lập. Trước thay đổi cấu trúc shared DB: xác định đích, backup, xin xác nhận nhóm trưởng, review SQL rồi một người điều phối áp dụng. A02 không thay đổi shared DB và không chứng minh kết nối từ máy thành viên khác.
