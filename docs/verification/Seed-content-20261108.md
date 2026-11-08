# Cập nhật nội dung seed — 08/11/2026

Đã sao lưu database trước thay đổi tại thư mục Downloads/Web/database/backups; file chứa dữ liệu riêng không đưa vào Git hoặc gói chia sẻ.

Kết quả local:
- 60 bản ghi seed đổi nội dung, giữ nguyên ID/owner/trạng thái và ngày duyệt/ngày tạo.
- 13 tài khoản seed có tên và bio tự nhiên; giữ email, password_hash, vai trò và tất cả tài khoản ngoài seed.
- 224 đánh giá bỏ tiền tố kỹ thuật, nội dung phù hợp số sao. Vẫn là nội dung được biên soạn cho đồ án, không phải đánh giá khách thật; có chú thích ở tài liệu seed/trang nguồn ảnh.
- Đủ 180 dòng ảnh cho 60 địa điểm (116 file thực được dùng, một số ảnh liên quan dùng chung); cộng 3 ảnh của địa điểm tự đăng = 183 dòng ảnh local.
- 59 địa điểm seed có tọa độ có nguồn. Một địa điểm Mì Quảng Bà Mua – Trưng Nữ Vương giữ NULL vì chưa xác minh được. Không lấy tọa độ tuyến phố làm vị trí chính xác của cửa hàng.
- Giữ nguyên địa điểm tự đăng, danh sách yêu thích, sao/trạng thái đánh giá. Sửa tỉnh TP.HCM cho Cộng – Lý Tự Trọng khớp địa chỉ; giữ nguyên business/routine (12).

Kiểm tra:
- Trên database clone: patch chạy hai lần không tạo trùng/đổi ID ảnh; nội dung ngoài seed được giữ.
- Trên database mới: seed chạy hai lần vẫn 60 địa điểm/180 ảnh/224 đánh giá.
- HTTP: 60 trang chi tiết + 6 trang khác + 116 URL ảnh đều thành công.
- Browser: 6 gallery ở các trạng thái có 3 ảnh, chọn ảnh thứ ba và xem lớn thành công; nhãn cũ không còn; danh sách chờ duyệt có tên địa điểm thường; nền bản đồ và ghim tải được; kiểm tra mobile 375px không tràn ngang.
- ZIP kiểm tra CRC và đủ ảnh được catalog tham chiếu, không có config/backup chứa dữ liệu riêng.

Gói chia sẻ: C:/Users/Admin/Downloads/Web/database/HotSpot_Seed_Content_2026-11-08.zip.
Database hiện có chỉ chạy 05_refresh_existing_seed.sql, đọc HUONG_DAN_IMPORT.md trước. Chưa commit/push/deploy.
