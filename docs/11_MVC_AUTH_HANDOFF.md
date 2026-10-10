# A04 + A05 — Bàn giao MVC/Auth đơn giản

Quyết định nhóm trưởng: ưu tiên đồ án chạy được trong 21 ngày; PHP OOP/MVC rõ ràng, bảo mật cơ bản. Không thêm Service/Middleware/Repository, không mở rộng automated tests/concurrency/CI nếu chưa có lỗi hoặc nhu cầu cụ thể. Giữ 13 bảng/12 routine/phân công/nghiệp vụ.

## Chạy ngay trên máy hiện tại

1. XAMPP: bật Apache và MySQL (engine thực tế MariaDB 10.4.32).
2. Mở **http://localhost/hotspot/index.php?route=home**.
3. Chọn Đăng ký → tạo tài khoản → Đăng nhập → Đăng xuất. Tài khoản đăng ký luôn MEMBER.

Source vẫn ở `D:\UEH\Dự án\HotSpot\HotSpot`. Junction local `C:\xampp\htdocs\hotspot` trỏ **chỉ vào public/** của repo; không sao chép toàn bộ source vào htdocs, không expose config/database.sql. Không sửa cấu hình Apache hoặc restart dịch vụ khác.

Config local ignored `app/config/database.php` đang trỏ database mới `hotspot_demo_20261010_125857_78cf50` trên 127.0.0.1:3306. File không commit. Database gồm schema/seed/12 routine A03, thêm tài khoản kiểm thử đăng ký qua web. Không ghi hoặc import vào db_hospot. XAMPP PHP 8.0.30 chạy được: đã bỏ readonly/never từ phần Foundation cần PHP 8.1. Bootstrap 5.3.3 CSS/JS lưu local để demo không cần CDN.

Máy khác: tạo database mới, import schema → seed → seed_admin_full → routines theo database/README; copy database.example.php thành database.php và điền cấu hình local. Document root chọn public, hoặc tạo junction tương tự:

```powershell
New-Item -ItemType Junction -Path 'C:\xampp\htdocs\hotspot' -Target 'D:\UEH\Dự án\HotSpot\HotSpot\public'
```

Chỉ chạy lệnh junction khi đường dẫn chưa tồn tại, không ghi đè project khác. PHP >=8.0 + pdo_mysql; công cụ sinh lại seed cần intl/mbstring, nhưng website Auth không cần intl. Chưa kiểm thử máy thành viên khác/shared database.

## Luồng và file dùng chung

- `public/index.php`: nhận route, kiểm CSRF mọi POST, dispatch Router và hiển thị lỗi 404/405/403/500.
- `app/bootstrap.php`, `autoload.php`: khởi động Session và nạp lớp theo namespace HotSpot.
- `app/core/Router.php`: bảng route GET/POST, không rewrite URL phức tạp.
- `Database.php`: một kết nối PDO cho request, utf8mb4, native prepared statements, strict mode/UTC.
- `Controller.php`/`Model.php`/`View.php`: render view và dùng chung PDO, không có tầng kiến trúc khác.
- `app/helpers/functions.php`: url/h/flash/CSRF/current_user/must_login/must_admin/input_text.
- `app/routes.php`: nơi thành viên đăng ký route. Các controller/model nghiệp vụ cũ vẫn stub, chưa gắn route; khi triển khai phải thêm namespace tương ứng như Auth/User.
- `AuthController.php`, `User.php`, `views/auth/*`: đăng ký/login/logout. User gọi KH_CREATE_USER trong transaction và KH_GET_USER_BY_EMAIL, drain result set/closeCursor. Password bcrypt, email validation/UNIQUE, session ID đổi khi login/logout.
- `HomeController.php`, `views/home/index.php`, `views/layouts/main.php`: home và layout Bootstrap. Home kiểm kết nối PDO thật; chưa hiển thị danh sách địa điểm.
- Route admin và `views/admin/index.php` chỉ là **cổng kiểm quyền** với trang chào, chưa dashboard/CRUD/moderation; AdminController hiện tại chưa triển khai, thuộc TV4.

Ví dụ thành viên thêm route (không có module mới được triển khai bởi ví dụ):

```php
// app/routes.php — controller có namespace HotSpot\Controllers
$router->add('GET', 'places/mine', [new PlaceController(), 'mine']);
// Controller gọi $user = must_login(); rồi lấy user_id từ $user.
// View link: url('places/mine'); form POST: csrf_field().
// Model extends HotSpot\Core\Model, dùng self::db()->prepare(...).
```

Chỉ ghi SQL trong Model; escape output bằng h(); không lấy actor ID từ request. Với routine ghi A03, Model phải beginTransaction/commit/rollback; file upload/tag phải đồng bộ trong cùng transaction theo nghiệp vụ. Không thêm service layer.

## Kiểm chứng thực tế — 10/10/2026

Qua trình duyệt trên Apache/XAMPP:

| Luồng | Kết quả |
|---|---|
| Home | Chạy, PDO nối MariaDB thật, Bootstrap local hiển thị |
| Đăng ký | Thành công, redirect login, tạo MEMBER với bcrypt |
| Login sai | Có thông báo email hoặc mật khẩu không chính xác |
| Login đúng | Session hoạt động, home chào tên tài khoản |
| Member truy cập admin trực tiếp | Trang 403, không chỉ ẩn nút |
| Admin từ seed đăng nhập và vào admin | Thành công, trang chào quản trị |
| Logout Member/Admin | Về home khách, phiên đăng nhập bị xóa |

Kiểm tra nhanh HTTP: POST logout thiếu CSRF=403, GET logout=405, route không tồn tại=404, Bootstrap CSS=200. PHP lint app/public **29/29 pass** bằng `C:\xampp\php\php.exe -l`, PHP 8.0.30. Không thêm bộ automated test/CI/concurrency mới ở task này.

Ảnh chạy thật: `reports/screenshots/a04-home.jpg`. Branch `feature/mvc-auth-a04-a05`, commit local; chưa push/PR/merge.

## Có thể giao ngay

| Người | Task tiếp theo | Dùng nền tảng |
|---|---|---|
| Khang | Profile và Favorites | User Session, layout, KH_SET_FAVORITE trong transaction; profile chưa làm trong A05 này |
| TV2 | Home danh sách, tìm kiếm/lọc/phân trang, AJAX tỉnh/phường | TV2 routines, Model PDO, route và layout |
| TV3 | Chi tiết, đăng/sửa/gửi lại, upload 1–3 ảnh; map sau luồng chính | TV3 routines/transaction, must_login, CSRF |
| TV4 | Dashboard/danh mục/moderation/Review | Thay cổng admin bằng AdminController thực tế; must_admin, TV4 routines |

Chưa hoàn thành module nghiệp vụ, Profile, upload/map/Review/Favorites/Admin CRUD và tích hợp E2E. Không tuyên bố đồ án hoàn tất từ Auth chạy được. Tiếp tục chức năng theo phân công; chỉ bổ sung kiểm thử khi có lỗi thật hoặc luồng quan trọng cần xác minh.
