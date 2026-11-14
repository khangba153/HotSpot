# Hot Spot

Hot Spot là đồ án môn Phát triển ứng dụng Web của Nhóm 6. Website cho phép người dùng khám phá địa điểm, chia sẻ bài viết kèm ảnh và đánh giá. Bài mới xuất hiện trong tìm kiếm công khai sau khi quản trị viên duyệt.

[Xem website Hot Spot](https://hotspot.alwaysdata.net/index.php?route=home)

## Chức năng

| Vai trò | Thao tác |
| --- | --- |
| Khách | Tìm địa điểm theo từ khóa, danh mục, tỉnh/thành và nhãn. Xem ảnh, vị trí và đánh giá công khai. |
| Thành viên | Đăng ký, đăng nhập bằng mật khẩu hoặc Google, ghi nhớ đăng nhập và đặt lại mật khẩu qua email. Quản lý hồ sơ, lưu địa điểm yêu thích, viết đánh giá và gửi bài kèm ảnh để duyệt. |
| Quản trị viên | Quản lý danh mục, duyệt hoặc từ chối bài, ẩn và khôi phục địa điểm hoặc đánh giá. Có thể gửi bài của mình qua cùng luồng chờ duyệt. |

Thành viên đã đăng nhập có thể dùng Claude để tóm tắt đánh giá công khai, tạo bản nháp từ nội dung đang nhập hoặc gợi ý địa điểm. Với gợi ý, Claude diễn giải câu hỏi thành bộ lọc. PHP kiểm tra bộ lọc trước khi truy vấn các địa điểm đã duyệt trong MariaDB. Ví dụ có thể thử “Tìm 3 quán cà phê được đánh giá cao ở Đà Nẵng”.

## Công nghệ và cấu trúc

Website dùng PHP hướng đối tượng theo MVC, MariaDB qua PDO, Bootstrap và JavaScript. Các luồng như tìm kiếm, phân trang và yêu thích dùng AJAX với phản hồi JSON. Bản đồ dùng Leaflet và OpenStreetMap. Các tích hợp phía ứng dụng gồm Google Identity Services, Gmail SMTP qua PHPMailer, Nominatim và Claude Messages API.

| Đường dẫn | Vai trò |
| --- | --- |
| [public/](public/) | Web root, điểm vào `index.php`, tài nguyên tĩnh và ảnh tải lên. |
| [app/routes.php](app/routes.php) | Đăng ký phương thức HTTP và controller cho từng route. |
| [app/core/](app/core/) | Router, Controller, View, kết nối PDO và các lớp gửi email, gọi Claude. |
| [app/controllers/](app/controllers/) | Kiểm tra yêu cầu và quyền, điều phối xử lý và trả HTML hoặc JSON. |
| [app/models/](app/models/) và [app/views/](app/views/) | Truy vấn hoặc gọi routine từ model và hiển thị giao diện từ view. |
| [app/services/](app/services/) | Xử lý bộ lọc gợi ý AI, tìm tọa độ và ảnh danh mục. |
| [app/config/](app/config/) | Mẫu cấu hình database và dịch vụ cho từng môi trường. |
| [storage/](storage/) | Cache và dữ liệu chạy ở phía máy chủ. |

## Chạy trên máy

**Môi trường** gồm PHP 8.4, Composer và MariaDB. Bật các extension `curl`, `fileinfo`, `mbstring`, `openssl` và `pdo_mysql`. Nếu dùng XAMPP cho MariaDB, chạy `php -v` để kiểm tra bản PHP đang được Terminal sử dụng.

1. Cài thư viện và tạo cấu hình từ mẫu. Các lệnh dưới đây dùng PowerShell trên Windows.

   ```powershell
   git clone https://github.com/khangba153/HotSpot.git
   cd HotSpot
   php -v
   composer install
   composer check-platform-reqs
   Copy-Item app/config/database.example.php app/config/database.php
   Copy-Item app/config/app.example.php app/config/app.php
   ```

2. Nhận `HotSpot_FULL_TEAM_20261110.sql` từ nhóm trưởng. Tạo một database MariaDB rỗng rồi import file qua phpMyAdmin. Bản bàn giao có **15 bảng và 12 stored procedure**. File chứa lệnh xóa và tạo lại bảng, vì vậy hãy chọn đúng database mới trước khi import. SQL lưu đường dẫn ảnh. Nếu dữ liệu tham chiếu ảnh trong `public/uploads/`, chép các tệp ảnh tương ứng vào đúng thư mục.
3. Điền thông tin kết nối trong `app/config/database.php`. Đặt `app_url` trong `app/config/app.php` khớp địa chỉ website đang chạy. Xem [cấu hình tích hợp](#cấu-hình-tích-hợp) nếu cần thử Google, email hoặc AI.
4. Bật MariaDB và chạy server từ thư mục dự án.

   ```powershell
   .\run-local.ps1
   ```

   Script chạy PHP tại [127.0.0.1:8000](http://127.0.0.1:8000/index.php?route=home) với web root `public/`, giới hạn 5 MB mỗi ảnh và 20 MB mỗi request. Nếu chạy PHP CLI trực tiếp, truyền các tham số tương ứng vì PHP CLI không đọc `.user.ini`.

   ```powershell
   php -d upload_max_filesize=5M -d post_max_size=20M -S 127.0.0.1:8000 -t public
   ```

## Cấu hình tích hợp

| Tính năng | Cấu hình và cách hoạt động |
| --- | --- |
| Đăng nhập Google | Điền `google_client_id` và khai báo đúng JavaScript origin đang chạy, ví dụ `http://127.0.0.1:8000`. PHP xác minh ID token và tạo session Hot Spot. Luồng hiện tại chỉ cần Client ID. |
| Đặt lại mật khẩu | Điền mục `mail` và `app_url`. Gmail SMTP dùng mật khẩu ứng dụng. Liên kết đặt lại có hiệu lực 15 phút. |
| Claude | Điền `claude.api_key` phía server. Mặc định giới hạn đầu ra 250 token và timeout 20 giây. Cache có hạn một giờ. Các lượt gọi mới dùng chung khoảng cách 10 giây trong cùng session. |
| Tìm tọa độ | Nominatim được gọi từ PHP khi người dùng yêu cầu tìm địa chỉ. Người dùng chọn kết quả, đặt ghim hoặc nhập thủ công. Tọa độ có thể để trống. |

Cài chứng chỉ CA hợp lệ cho PHP khi môi trường gặp lỗi xác minh TLS. Git bỏ qua các file cấu hình thật, SQL bàn giao, `vendor/`, ảnh tải lên và cache. Giữ thông tin xác thực trong cấu hình của từng môi trường.

Khi dùng máy chủ Nominatim công khai, ứng dụng dùng User-Agent nhận diện, cache và giới hạn tần suất dùng chung theo [chính sách Nominatim](https://operations.osmfoundation.org/policies/nominatim/). Chỉ gọi khi người dùng yêu cầu, tối đa một yêu cầu mỗi giây cho ứng dụng, tránh autocomplete và tìm hàng loạt. Kết quả có dữ liệu được cache 24 giờ, kết quả rỗng một giờ. Có thể đặt `HOTSPOT_GEOCODE_ENDPOINT` thành URL HTTPS `/search` của máy chủ tương thích Nominatim. Giữ dòng ghi công OpenStreetMap khi hiển thị bản đồ.

## Triển khai và cập nhật

- Trên Alwaysdata, dùng PHP 8.4, đặt web root vào `public/`, cài dependency bằng Composer và cấp quyền ghi cho thư mục ảnh tải lên và cache.
- Chuẩn bị cấu hình database, `app_url` và dịch vụ cho hosting. Có thể kiểm tra giới hạn tải lên trong [public/.user.ini](public/.user.ini) và đối chiếu với cấu hình PHP thực tế.
- Cập nhật mã qua Git và áp dụng SQL tương ứng khi phiên bản có đổi cấu trúc hoặc routine. Sao lưu database đang chạy trước khi cập nhật.
- Kiểm tra lại trang Khám phá, đăng nhập, yêu thích và luồng gửi bài → duyệt → xem công khai. Kiểm tra Google, email, bản đồ và Claude với cấu hình của môi trường đích.

## Đóng góp

Tạo nhánh từ `main`, commit các tệp liên quan rồi mở pull request để nhóm review. Các bản SQL và tài liệu nội bộ được chia sẻ riêng trong nhóm.
