# Kiểm tra dải nhãn card theo mẫu 2 — 08/11/2026

## Đã kiểm tra

- PHP lint: AdminController, HomeController, PlaceSearchController, AdminCategory, Category, hai view thay đổi; node --check admin-categories.js; git diff --check đạt.
- Migration local chạy hai lần: sáu danh mục có ảnh nhãn, kết quả lần hai không đổi. So sánh toàn bộ cột cũ với bản sao trước thay đổi: giữ nguyên.
- 42 kiểm tra HTTP trên database QA riêng: lưu đường dẫn, upload, đọc ảnh, Home/AJAX, ảnh nhãn tùy chọn, tạo mới, URL HTTPS, CSRF, guest/member, sáu nhóm kết quả. Chặn đường dẫn vượt thư mục, JavaScript/HTTP URL, file không tồn tại, PNG giả và ảnh vượt 2 MB. Lưu thất bại ở trường thứ hai dọn file trường thứ nhất, giữ dữ liệu cũ.
- 36 kiểm tra trình duyệt: điền form và xem trước file; modal tại 320/375/768/1440; Home tại 320/375/768/1024/1440/1920 không tràn ngang, nhãn đủ 12 card; desktop 4 cột; ảnh sáu danh mục tải được; thao tác tìm AJAX thực giữ đúng dải nhãn; toàn bộ ảnh card trên local chính tải được.
- Xem trực tiếp ảnh chụp card và modal quản trị. Các ảnh địa điểm, liên kết chi tiết và dữ liệu đánh giá giữ nguyên. Không gọi Claude trong các kiểm tra này.

## Phạm vi và giới hạn

Đã áp dụng local tại port 8000; chưa commit/push/deploy. Upload và dữ liệu thử chỉ tạo trong database/source QA riêng. OAuth không kiểm tra lại: origin QA 8027 chưa đăng ký với Google, không liên quan thay đổi này. URL ảnh ngoài do quản trị nhập có thể không tải được; card vẫn giữ tên danh mục và nền khi ảnh lỗi.

Chứng cứ local ngoài Git: `D:/UEH/LTWEB/hotspot-mission-control/.sites-local/card-labels-20261108/` gồm HTTP results, script kiểm tra, ảnh Home và modal. Database QA được dọn sau khi chạy; server local chính tiếp tục hoạt động.
