# 08 — Nhật ký quyết định sau 8 giai đoạn

> Đây là **bản khóa phạm vi**, được xây từ 8 lần xác nhận trong Project. Không hiểu các lựa chọn mặc định là nội dung mới chưa hỏi: chúng đã được người dùng chốt trong các lượt trước.

| Giai đoạn | Quyết định đã khóa |
|---|---|
| 1. Yêu cầu | Chủ đề website cộng đồng/mạng xã hội; PHP OOP + MVC + AJAX + dùng API ngoài. **Chỉ tiêu nhóm:** tối thiểu 12 bảng, 3 routine/người; phân chia ít nhất 2 bảng/người |
| 2. Phạm vi | Cả Việt Nam; sửa Place `PENDING`/`REJECTED`; Admin quản lý danh mục, từ chối có lý do, ẩn/khôi phục; Nominatim + Leaflet/OSM; chỉ các tính năng cộng đồng cốt lõi |
| 3. Nghiệp vụ | Bắt buộc 1–3 ảnh; review cần 1–5 sao + comment; sửa review `HIDDEN` vẫn `HIDDEN`; danh mục ngừng hoạt động không ẩn bài cũ; cảnh báo trùng do Admin quyết định; địa giới tỉnh/phường |
| 4. UI | Trang chủ có tìm kiếm/lọc/danh sách; trang login/register riêng; form Place thống nhất; bản đồ ở chi tiết và form; thành viên tab hồ sơ, Admin dashboard/sidebar |
| 5. DB | **13 bảng**, thay districts bằng provinces/wards, thêm tags + place_tags; tỉnh bắt buộc/ward tùy chọn, 0–5 tag seed; reason từ chối gần nhất; schema/seed tạo mới; 12 routine phân công mới |
| 6. Kiến trúc | Front controller `index.php?route=...`; Model PDO + routine chọn lọc + SQL thường; AJAX search/favorite/ward/review; PHP→Nominatim + cache/rate-limit; ảnh server, lưu path; JSON `success,message,data,errors` |
| 7. Quản lý | **21 ngày**, Khang tích hợp; TV4 làm toàn bộ Admin+Review, người khác chỉ test; cả nhóm **trực tiếp trên shared MariaDB**; PR/review/merge main; GitHub Projects+Teams; kiểm thử/tích hợp liên tục |
| 8. Nghiệm thu | Demo local + hosting miễn phí; test thủ công + ảnh; 20–30 địa điểm nhiều tỉnh, 1 Admin + ≥2 Member; không video; đóng băng chức năng từ ngày 18/21 |

## Nguồn và mức độ xác nhận

- **Nguồn môn học:** File `Yêu-cầu-đồ-án.txt` người dùng cung cấp. Có quy định Bootstrap và Backend OOP bắt buộc; OOP+MVC/AJAX/Webservice được đánh giá cao; Admin cơ bản; có shared host DB/GitHub branch/Teams và minh chứng; hosting free khuyến khích.
- **Nguồn thiết kế cũ:** File `README (1).md` (10 bảng, routine và sơ đồ cũ); đã được **thay thế về logic** bởi bộ tài liệu này, không dùng nó làm spec triển khai.
- **Nguồn 8 giai đoạn:** File `Văn bản đã dán (1)(2).txt` và 8 vòng xác nhận trực tiếp trong Project.
- **Không xác thực là yêu cầu giảng viên:** chỉ tiêu tối thiểu 12 bảng, 3 routine/người, deadline 21 ngày. Đây là nội dung nhóm đã quyết định, trừ khi giảng viên bổ sung yêu cầu riêng.

## Thay đổi so với README cũ

| Mục | Cũ | Mới |
|---|---|---|
| DB | 10 bảng | 13 bảng |
| Địa giới | `districts` | `provinces`, `wards` hai cấp; toàn Việt Nam |
| Nhãn | Không có | `tags` và `place_tags`, tối đa 5 từ seed |
| Địa điểm | Submit, Admin status đơn giản | Owner sửa/gửi lại; lý do từ chối; cảnh báo trùng; khôi phục |
| Đánh giá | Tạo/sửa/ẩn | Thêm khôi phục; sửa bài ẩn không tự công khai |
| Routine | Tập routine cũ | 12 routine logic mới, 3/người |
| Chia DB | 2–3–3–2 | 3–4–3–3 |
| Kiến trúc | MVC khái quát | Định tuyến, JSON hợp đồng, upload transaction, Nominatim cache |
| Triển khai | Chưa chốt hoàn toàn | Shared MariaDB trực tiếp, 21 ngày, freeze ngày 18, test+demo |

## Quyết định khóa bổ sung — 10/10/2026

Nhóm trưởng xác nhận MariaDB **10.4.32 đi kèm XAMPP** là hệ quản trị chính thức, thay thế yêu cầu MySQL 8. Không lấy MySQL 8 làm điều kiện nghiệm thu. PHP vẫn dùng PDO `pdo_mysql`, phpMyAdmin và MVC hiện tại.

Giữ nguyên 13 bảng, 12 routine (3/người), mọi PK/FK/UNIQUE/CHECK, nghiệp vụ, phân công và thời hạn 21 ngày. Shared database vẫn là cách cộng tác; database cục bộ mới chỉ phục vụ kiểm thử cô lập. `db_hospot` cũ chỉ đối chiếu, không import/ghi/xóa. Thay đổi shared database cần backup và xác nhận nhóm trưởng trước khi áp dụng.

## Trạng thái repository thực tế

- A01: config mẫu và ignore; config cá nhân bỏ khỏi Git, giữ file local.
- A02: schema 13 bảng đã chạy thật trên MariaDB 10.4.32; 64/64 kiểm tra pass. Xem báo cáo A01/A02.
- A03 đã kiểm chứng local: 34 tỉnh/3.321 cấp xã/24 Place demo, 12 routine, 158/158 PDO runtime checks + 17/17 generator checks. Chưa push/PR/Actions; dừng trước A04.
- Source MVC ở repository này vẫn là skeleton. Các tài liệu route/class/flow là hợp đồng triển khai, chưa chứng minh chức năng chạy được.
- Chưa xác nhận shared database/tài khoản của 4 người, hosting, prefix tên thật TV2–TV4, deployment hoặc E2E.

## Quyết định ưu tiên sau A03

Nhóm trưởng chấp thuận A03 local và chuyển sang hoàn thành đồ án sinh viên năm 3 trong 21 ngày. Từ A04: chức năng chạy trước, OOP/MVC đơn giản, không Service/Middleware/Repository, không tăng automated/concurrency/CI hoặc logging/tuning nếu chưa có nhu cầu cụ thể. Vẫn PDO/password hash/Session/quyền/CSRF và mọi nghiệp vụ/13 bảng/12 routine/phân công. A04+A05 Auth đã chạy trên XAMPP PHP 8.0.30; xem 11_MVC_AUTH_HANDOFF.md. Các ghi chú source skeleton phía trên là lịch sử trước A04; modules nghiệp vụ còn stub.
