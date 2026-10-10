# Hot Spot — bộ khung dự án

Repository bàn giao để nhóm bắt đầu triển khai, **chưa có chức năng website**. Các file PHP trong Controller, Model, View, core, helper, route và `public/index.php` được để rỗng. Không có đăng ký, đăng nhập, Session, CSRF hoặc kết nối PDO đã triển khai.

## Những gì được giữ

- Cấu trúc thư mục/file MVC để các thành viên viết code.
- Tài liệu thiết kế và sơ đồ trong `docs/`; đây là yêu cầu tham khảo, không phải chức năng đã hoàn thành.
- `database/`: schema 13 bảng, seed và 12 stored procedure đã chốt, cùng nguồn dữ liệu hành chính. Xem [hướng dẫn import](database/README.md).
- Bootstrap có sẵn trong `public/assets/vendor/`, cấu hình database mẫu và `.gitignore`. Không commit config cá nhân, mật khẩu, dump database hoặc dữ liệu upload.

Công nghệ đã chốt: PHP thuần OOP + MVC, PDO `pdo_mysql`, MariaDB 10.4.32 đi kèm XAMPP, Bootstrap, JavaScript/AJAX, Nominatim + Leaflet/OpenStreetMap. Giữ nghiệp vụ, 13 bảng, 12 routine và phân công/21 ngày theo tài liệu hiện có.

## Chuẩn bị môi trường

1. Bật Apache và MySQL (MariaDB) trong XAMPP; PHP cần extension `pdo_mysql`.
2. Trỏ web root vào `public/`. Hiện `public/index.php` rỗng nên mở website sẽ chỉ có trang trắng; các thành viên tự triển khai Router/MVC theo tài liệu.
3. Import SQL vào một database phát triển/demo mới và rỗng nếu cài máy mới. Không import lại vào database có dữ liệu hoặc `db_hospot` cũ.
4. Copy `app/config/database.example.php` thành `database.php` và cấu hình riêng trên máy. Mẫu cấu hình có sẵn nhưng lớp PDO chưa được viết.

Máy đang thao tác: `D:\UEH\Dự án\HotSpot\HotSpot`. Database local `hotspot_demo_20261010_125857_78cf50` được giữ nguyên; lần chuyển sang bộ khung không sửa hoặc xóa database.

## Git

PR bàn giao phải được review và merge trước khi nhóm lấy phiên bản từ `main`. Khi đã merge:

```powershell
git switch main
git pull --ff-only origin main
git switch -c feature/ten-chuc-nang
# Viết code, kiểm tra phần thay đổi rồi commit.
git add <file-da-kiem-tra>
git commit -m "feat: mo ta thay doi"
git push -u origin feature/ten-chuc-nang
```

Tạo PR, chờ review; không push trực tiếp vào `main`, không reset/ghi đè thay đổi chưa đồng bộ. Chỉ dùng lệnh trên khi không có thay đổi local chưa xử lý. Không tự thay đổi cấu trúc database dùng chung; cần backup và xác nhận nhóm trưởng.
