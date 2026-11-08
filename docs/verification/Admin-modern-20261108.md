# Admin sáng theo mẫu 2 — 08/11/2026

Áp dụng mẫu dashboard hiện đại đã được Khang chọn: nền xanh nhẹ, khối trắng bo góc, menu ngang trong header, cam Hot Spot cho thao tác chính và xanh teal cho thao tác phụ. Thay theme tối trước đó; dùng chung navigation ở layout, bỏ sidebar lặp trong sáu view. Chỉ thay giao diện, không sửa controller/database/routine.

Các màn hình: Tổng quan, Chờ duyệt, Quản lý địa điểm, Đánh giá, Danh mục và Xem xét địa điểm. Bộ đếm quản lý địa điểm dùng số thật của danh sách hiện có, không thêm số liệu mô phỏng từ ảnh tham khảo. Các bộ đếm bấm được vẫn giữ lọc trạng thái.

Kiểm tra:
- 42 lượt hiển thị: 6 màn hình × 320/375/768/1024/1280/1440/2560px; HTTP 200, không tràn ngang toàn trang; đủ 7 liên kết menu, đúng một mục active; các panel chuyển sang nền sáng. Bảng dài cuộn trong vùng riêng trên mobile.
- Modal sửa danh mục mở/đóng đúng, nền trắng và vừa viewport 375/1440px.
- Tìm kiếm, lọc trạng thái, lọc danh mục, phân trang và liên kết Admin mở form Đăng địa điểm đều đạt. Không gửi cập nhật dữ liệu khi kiểm tra UI.
- PHP lint các view và git diff --check đạt.

Chưa commit, push hoặc deploy.
