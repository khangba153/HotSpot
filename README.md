# Hot Spot

Đồ án website chia sẻ địa điểm dùng PHP thuần OOP/MVC. Source đã có Router, PDO, Session, CSRF và các module của nhóm. Phần tài khoản gồm đăng ký, đăng nhập bằng mật khẩu hoặc Google, hồ sơ, yêu thích, ghi nhớ đăng nhập và đặt lại mật khẩu qua email.

## Công nghệ và quy ước

- Controller xử lý request, Model truy vấn dữ liệu, View hiển thị giao diện.
- Bootstrap có sẵn trong `public/assets/vendor/`, cấu hình database mẫu và `.gitignore`. Không commit config cá nhân, mật khẩu, dump database hoặc dữ liệu upload.

Công nghệ: PHP 8.4, Composer, PDO `pdo_mysql`, MariaDB đi kèm XAMPP, Bootstrap, JavaScript/AJAX; các module bản đồ dùng Leaflet/OpenStreetMap và Nominatim theo task của nhóm. Giữ nghiệp vụ và 12 routine đã thống nhất.

## Chuẩn bị môi trường

1. Cài PHP 8.4 và Composer. Bật các extension trong `composer.json`; kiểm tra `php -v` để tránh dùng nhầm PHP 8.0 của XAMPP.
2. Chạy `composer install`, sau đó `composer check-platform-reqs` trong thư mục dự án. `vendor/` được tạo tại máy, không commit vào Git.
3. Bật MySQL/MariaDB; copy `app/config/database.example.php` thành `app/config/database.php` và điền cấu hình riêng.
4. Copy `app/config/app.example.php` thành `app/config/app.php`. Điền Google Client ID và thông tin Gmail gửi thư khi sử dụng các chức năng tương ứng. Gmail SMTP dùng Mật khẩu ứng dụng, không dùng mật khẩu Gmail thông thường.
5. Đặt `app_url` là địa chỉ cố định của website: local `http://127.0.0.1:8000`, hosting `https://hotspot.alwaysdata.net`. Google Client phải cho phép đúng origin đang chạy.
6. Trỏ web root vào `public/`. Để chạy local bằng PHP 8.4:

```powershell
php -S 127.0.0.1:8000 -t public
```

Mở `http://127.0.0.1:8000/index.php?route=login`. Giữ Terminal chạy server khi thử website. PHP cần chứng chỉ CA hợp lệ để kết nối HTTPS đến Google và Gmail; cấu hình đường dẫn CA trong `php.ini` riêng của máy.

## Database và cấu hình riêng

SQL/schema/seed/routine được lưu riêng ngoài repository. Nhận bản cập nhật đã được nhóm trưởng chốt trước khi chạy source mới; không import đè seed hoặc tự sửa database dùng chung.

Các bổ sung Auth cần có ngoài schema nền 13 bảng:

- `users.google_subject`: mã Google có thể NULL, có unique index.
- `user_remember_tokens`: bản băm token, tài khoản và thời hạn ghi nhớ đăng nhập.
- `user_password_reset_tokens`: bản băm token, tài khoản, thời hạn, thời điểm tạo và thời điểm đã sử dụng.

Thời hạn token được lưu theo UTC. Không cần thêm routine cho các truy vấn token; code dùng PDO với tham số. Cập nhật source qua Git không tự cập nhật cấu trúc database.

Không commit `app/config/app.php`, `app/config/database.php`, mật khẩu ứng dụng Gmail hoặc Client Secret. Google sign-in hiện dùng ID token, không cần Client Secret. Khi deploy, cài dependency bằng Composer trên hosting hoặc chuẩn bị `vendor/` tương ứng, chọn PHP 8.4 và cấu hình riêng cho hosting.

Kiểm thử luồng chính của Google, ghi nhớ đăng nhập và khôi phục mật khẩu đã được Khang báo đạt trên local. Điều này không xác nhận các tính năng mới đã được deploy lên hosting hoặc tất cả module của nhóm đã hoàn thành.

## Git

PR bàn giao phải được review và merge trước khi nhóm lấy phiên bản từ `main`. Khi đã merge:

```powershell
git switch main
git pull --ff-only origin main
git switch -c feature/ten-chuc-nang
# Viết code, kiểm tra phần thay đổi rồi commit.
git add <file-da-kiem-tra>
git commit -m "feat(auth): implement user registration"
git push -u origin feature/ten-chuc-nang
```

Tạo PR, chờ review; không push trực tiếp vào `main`, không reset/ghi đè thay đổi chưa đồng bộ. Chỉ dùng lệnh trên khi không có thay đổi local chưa xử lý. Không tự thay đổi cấu trúc database dùng chung; cần backup và xác nhận nhóm trưởng.
