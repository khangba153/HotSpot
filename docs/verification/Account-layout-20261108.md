# Đồng nhất bố cục trang cá nhân — 08/11/2026

Giữ bố cục Hồ sơ làm mẫu; Yêu thích và Địa điểm của tôi dùng chung banner, Bootstrap container/row/col-lg-3 + col-lg-9 và partial profile/_sidebar.php. Menu active riêng từng trang; form đăng xuất vẫn gửi CSRF. Không sửa controller, database hoặc nghiệp vụ.

Kiểm tra trên máy tại 127.0.0.1:8000 bằng tài khoản seed demo:
- 21 lượt kiểm tra bố cục: 3 trang × 320, 375, 768, 1024, 1280, 1440, 1920px. Không tràn ngang, không cắt hàng/nút; cùng vị trí và độ rộng sidebar/chiều cao banner; đúng một mục menu active.
- Bốn bộ lọc trạng thái ở Địa điểm của tôi có đúng menu active. Mở trang chỉnh sửa địa điểm PENDING của chính tài khoản: HTTP 200, có form. Không gửi cập nhật.
- Mở chi tiết từ card Yêu thích và mở lịch sử đánh giá qua menu thành công.
- PHP lint các view đã sửa và partial mới đạt. Không thay đổi dữ liệu tài khoản/địa điểm/yêu thích khi kiểm tra.

Đã khắc phục cột danh sách bị cắt nút ở 1024px bằng cách chuyển từng hàng sang thẻ ở vùng 992–1399px. Bảng trên desktop lớn và cách xếp thẻ trên mobile vẫn giữ chức năng cũ.

Chưa commit, push hoặc deploy.
