# Kiểm tra ảnh danh mục — 08/11/2026

Thực hiện trên snapshot và database QA riêng, không sửa dữ liệu người dùng để kiểm thử.

- 42 kiểm tra HTTP đạt: đăng nhập, đọc 6 ảnh, cập nhật mô tả/đường dẫn và đồng bộ Home, upload JPG thật và phục vụ file, tạo danh mục không ảnh và fallback, ngừng danh mục; chặn CSRF, guest/member, mảng thay chuỗi, trùng tên, mô tả dài, traversal, javascript/http URL, ảnh không tồn tại, script giả ảnh, ảnh quá 2 MB; HTML mô tả được escape.
- Browser thật: mở modal Sửa, dữ liệu DB điền đúng, chọn ảnh có xem trước, lưu qua form thật, Home dùng ảnh upload; modal vừa viewport và Home không tràn ngang tại 375/768/1440/1920px.
- SQL migration chạy hai lần thành công trên QA, routine vẫn 12. Sau khi kiểm tra đã backup danh mục/routine local rồi áp dụng migration vào database local.
- PHP lint và node --check cho các file cập nhật đạt.

Không commit/push/deploy. Dữ liệu QA được dọn sau kiểm tra.
