# Bộ dữ liệu nội dung Hot Spot — 08/11/2026

## Máy đang có bộ seed ngày 07/11

1. Sao lưu database đang dùng.
2. Lấy code và chép nội dung thư mục `public` của gói vào `public` dự án, giữ nguyên file upload người dùng.
3. Chọn đúng database trong phpMyAdmin, import **05_refresh_existing_seed.sql**.
4. Chạy **06_verify.sql**. Kết quả mong đợi: 60 địa điểm seed, 59 có tọa độ, không có địa điểm seed thiếu/thừa 3 ảnh; không còn nhãn cũ.

Không import 00/01/02/03 trên database đang có dữ liệu. Không dùng gói 07/11 cũ để ghi đè bộ mới.

## Database mới hoàn toàn

Import lần lượt 00_schema.sql → 01_reference_data.sql → 02_routines.sql → 03_seed_content.sql → 04_category_images.sql. Chép thư mục public vào dự án rồi chạy 06_verify.sql. Schema có đủ cột xác thực và ảnh danh mục hiện tại; vẫn 12 routine.

## Nội dung

- 60 địa điểm có thật (48 được duyệt và 12 cho luồng chờ duyệt/từ chối/ẩn), đủ 180 dòng ảnh thật liên quan. Một số ảnh là ảnh cùng khu vực hoặc món ăn/không gian liên quan, không khẳng định đều chụp đúng cơ sở.
- Tài khoản và đánh giá được biên soạn cho đồ án, không phải khách hàng và lời chứng thực thực tế. Nhãn kỹ thuật không còn xuất hiện trong tên, mô tả và bình luận; nguồn gốc được giữ ở tài liệu/trang nguồn ảnh.
- 59/60 địa điểm seed có tọa độ tham chiếu từ nguồn địa điểm hoặc điểm OpenStreetMap được đối chiếu. Riêng Mì Quảng Bà Mua – Trưng Nữ Vương chưa xác minh được vị trí cụ thể nên giữ NULL, không đoán tọa độ. Điểm giữa hồ/vịnh/khu danh thắng không nhất thiết là cổng vào.
- Cộng Cà Phê – Lý Tự Trọng được sửa tỉnh thành TP.HCM cho khớp địa chỉ 26 Lý Tự Trọng. Dữ liệu phường cũ được xóa nếu tỉnh thay đổi để tránh sai quan hệ.
- Patch giữ nguyên ID địa điểm/đánh giá, email/mật khẩu/vai trò, chủ bài, trạng thái duyệt, sao đánh giá và yêu thích. Chỉ khớp bản ghi seed theo email + tên cũ/tên mới. Chạy lại không tạo trùng ảnh.
- Patch chỉ thay ảnh trong các đường dẫn seed, không xóa ảnh upload của thành viên. Nếu thành viên đã chỉnh trực tiếp một bản ghi seed thì nên sao lưu trước: tên/địa chỉ/mô tả của bản ghi seed đó sẽ về catalog mới.

## Tài khoản dùng thử

Email giữ nguyên để không phá tài khoản cũ: demo-20261107-admin@hotspot.test và demo-20261107-member01@hotspot.test đến member12. Mật khẩu khi tạo database mới: HotSpotDemo2026!; patch không đổi mật khẩu đã có. Tên hiển thị là tên tiếng Việt thông thường.

Nguồn vị trí/ảnh/giấy phép trong catalog.json và public/assets/images/photo-credits.html. Không dùng các đánh giá này làm đánh giá khách hàng thật trong báo cáo.
