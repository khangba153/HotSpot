# Hot Spot — bộ khung dự án

Repository bàn giao để nhóm bắt đầu triển khai, **chưa có chức năng website**. Các file PHP trong Controller, Model, View, core, helper, route và `public/index.php` được để rỗng. Không có đăng ký, đăng nhập, Session, CSRF hoặc kết nối PDO đã triển khai.

## Những gì được giữ

- Cấu trúc thư mục/file MVC để các thành viên viết code.
- Bootstrap có sẵn trong `public/assets/vendor/`, cấu hình database mẫu và `.gitignore`. Không commit config cá nhân, mật khẩu, dump database hoặc dữ liệu upload.

Công nghệ đã chốt: PHP thuần OOP + MVC, PDO `pdo_mysql`, MariaDB 10.4.32 đi kèm XAMPP, Bootstrap, JavaScript/AJAX, Nominatim + Leaflet/OpenStreetMap. Giữ schema 13 bảng và 12 routine hiện có.

## Chuẩn bị môi trường

1. Bật Apache và MySQL (MariaDB) trong XAMPP; PHP cần extension `pdo_mysql`.
2. Trỏ web root vào `public/`. Hiện `public/index.php` rỗng nên mở website sẽ chỉ có trang trắng; các thành viên tự triển khai Router/MVC.
3. Sử dụng MariaDB dùng chung của nhóm; lấy thông tin kết nối từ nhóm trưởng qua kênh riêng. Không tự import lại SQL, sửa cấu trúc hoặc xóa dữ liệu dùng chung.
4. Copy `app/config/database.example.php` thành `database.php` và cấu hình riêng trên máy. Mẫu cấu hình có sẵn nhưng lớp PDO chưa được viết.

SQL/schema/seed/routine được lưu riêng ngoài repository. Việc bỏ thư mục SQL khỏi repository không thay đổi database trên server.

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
