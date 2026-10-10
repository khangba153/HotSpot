# Hot Spot

Đồ án PHP thuần OOP + MVC, PDO (`pdo_mysql`), Bootstrap và JavaScript/AJAX. Database chính thức: **MariaDB 10.4.32 đi kèm XAMPP**. Giữ thiết kế 13 bảng, 12 stored procedure và nghiệp vụ đã chốt; không yêu cầu MySQL 8.

## Cài đặt và chạy trên XAMPP

1. Cài XAMPP, bật **Apache** và **MySQL** (dịch vụ MariaDB). Website đang chạy với PHP 8.0.30; bật extension `pdo_mysql`.
2. Clone repository. Trỏ web root vào thư mục **`public/`**, không trỏ vào toàn bộ repository. Có thể đặt project trong `htdocs` và mở `/HotSpot/public/index.php?route=home`, hoặc tạo junction trên Windows:

   ```powershell
   New-Item -ItemType Junction -Path 'C:\xampp\htdocs\hotspot' -Target 'D:\UEH\Dự án\HotSpot\HotSpot\public'
   ```

   Chỉ tạo junction khi `htdocs\hotspot` chưa tồn tại; không ghi đè website khác.
3. Mở phpMyAdmin tại http://localhost/phpmyadmin/. Nếu cài trên máy mới, tạo **một database phát triển/demo rỗng** với `utf8mb4_unicode_ci`, rồi import theo [database/README.md](database/README.md). Không import lại vào database đang có dữ liệu; không dùng hoặc xóa `db_hospot` cũ.
4. Copy `app/config/database.example.php` thành `app/config/database.php`; sửa `host`, `port`, `database`, `username`, `password` theo tài khoản MariaDB trên máy. File cấu hình cá nhân bị Git ignore; không commit mật khẩu hoặc dump database.
5. Mở **http://localhost/hotspot/index.php?route=home** nếu dùng junction trên.

**Máy hiện tại:** repository `D:\UEH\Dự án\HotSpot\HotSpot`; database đang dùng `hotspot_demo_20261010_125857_78cf50`. Đây là database phát triển/demo được giữ lại, không cần tạo thêm database kiểm thử.

## Chức năng hiện có và cấu trúc

Trang chủ, kết nối MariaDB, đăng ký, đăng nhập/đăng xuất, Session Member/Admin, CSRF form và thông báo lỗi cơ bản đã hoạt động. Route `admin` hiện chỉ là trang kiểm quyền. Các module địa điểm, tìm kiếm, bản đồ, Profile, Favorites, Reviews và quản trị chưa triển khai.

- `public/index.php`: front controller `?route=...`; Bootstrap nằm trong `public/assets/vendor/`.
- `app/routes.php`, `app/core/`: Router, PDO, Controller, Model, View dùng chung.
- `app/controllers/`, `app/models/`, `app/views/`, `app/helpers/`: mã MVC/Auth đang sử dụng.
- `database/`: schema, seed, 12 procedure và nguồn hành chính; `tools/generate_admin_seed.php` để sinh lại seed khi cần.
- `docs/`: thiết kế cốt lõi đã chốt và sơ đồ Mermaid. Đây là tài liệu tham khảo thiết kế, không chứng minh mọi chức năng đã được lập trình.

Kiểm tra nhanh khi sửa code: mở trang chủ, đăng ký/login/logout, thử Member vào Admin (403); dùng `C:\xampp\php\php.exe -l <file.php>` cho file PHP thay đổi. Không có bộ test chuyên sâu hoặc CI trong bản bàn giao.

## Sử dụng Git

Sau khi PR bàn giao được review và merge, lấy phiên bản mới rồi tạo branch riêng:

```powershell
git switch main
git pull --ff-only origin main
git switch -c feature/ten-chuc-nang
# Sửa và kiểm tra các file liên quan trước khi commit.
git add <file-da-kiem-tra>
git commit -m "feat: mo ta thay doi"
git push -u origin feature/ten-chuc-nang
```

Tạo Pull Request vào `main`, ghi chức năng và kết quả kiểm tra, chờ người khác review. Không push trực tiếp/merge tự động vào `main`, không ghi đè thay đổi chưa đồng bộ. Khi `pull --ff-only` báo lỗi, giữ thay đổi local và giải quyết riêng; không reset cưỡng bức. Thay đổi cấu trúc shared MariaDB cần backup và xác nhận nhóm trưởng.

Tham khảo [nghiệp vụ](docs/01_REQUIREMENTS_AND_RULES.md), [ERD](docs/02_DATABASE_ERD.md), [quyết định đã khóa](docs/08_DECISION_LOG.md) và [phân công/21 ngày đã chốt](docs/06_TEAM_PLAN.md).
