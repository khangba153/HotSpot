# TV3 — Làm rõ code theo quy ước nhóm

Kiểm tra ngày **07/11/2026**, đối chiếu với commit `7d2f68901703bfba2216473c11cc952b3e1e0474` trên nhánh `thanhCT`.

## Thay đổi

- `place-form.js`: thay các biến `i/j/k/idx/err` bằng tên rõ vai trò; tách việc tạo thẻ nhãn và thẻ ảnh thành `appendSelectedTag()` / `appendImagePreview()`. Mỗi lần gọi giữ riêng checkbox hoặc chỉ số ảnh cho nút xóa.
- `PlaceController`: tách kiểm tra CSRF, trường được phép nhận và mã bài sang `readEditedPlaceId()`. Luồng lưu ghi rõ ba bước: validation, giao dịch và dọn ảnh cũ sau commit. Khởi tạo Model/Service bằng biến riêng trước khi gọi phương thức.
- `PlaceImage`: tách kiểm tra một ảnh sang `validateSingleUploadedImage()` và dùng chung `normalizeSingleFile()` cho ba cấu trúc upload. Đổi tên biến đường dẫn tạm; giữ nguyên các khóa chuẩn của `$_FILES` và tham số public.
- `PlaceGeocoder`: đặt tên biến theo dữ liệu/handle thực tế; tách tạo thư mục, ghi thời điểm và flush file thành từng bước. Giữ kiểm tra trường hợp tiến trình khác vừa tạo thư mục.
- `places.css`: bỏ 10 rule gallery/bản đồ placeholder/icon cũ không còn được tham chiếu trong mã ứng dụng. Form tăng phiên bản URL JavaScript để trình duyệt tải bản sửa.

## Kết quả

**653/653 kiểm tra đạt**, chi tiết và SHA-256 source sau sửa nằm trong [TV3-readability-results.json](TV3-readability-results.json).

| Phạm vi | Số kiểm tra | Cách kiểm tra |
|---|---:|---|
| Chuẩn hóa và validation ảnh | 584 | So sánh bản trước/sau: 500 trường hợp cấu trúc dữ liệu với seed cố định; 80 tổ hợp lô ảnh và 4 điều kiện kỳ vọng. Dùng file ảnh thật có sẵn và file tạm rỗng/giả/quá lớn; kiểm tra yêu cầu HTTP upload. |
| Tương tác nhãn và ảnh | 21 | Chạy JavaScript trước/sau trong Node VM với DOM/DataTransfer mô phỏng; kiểm tra chọn quá 5 nhãn, bỏ nhãn giữa danh sách, chọn bằng Enter, xóa ảnh giữa, đổi ảnh bìa, giới hạn số lượng/loại/dung lượng và giải phóng object URL. |
| Controller chỉnh sửa/gửi lại | 30 | Model và kết nối giao dịch mô phỏng; đối chiếu CSRF, trường lạ, quyền sở hữu, trạng thái thay đổi dưới khóa, validation, các lỗi ghi/commit/rollback và thứ tự dọn ảnh mới/cũ. |
| Geocoding | 18 | Upstream cURL mô phỏng, cache và file lock thật trong thư mục tạm riêng; đối chiếu thành công/rỗng/lỗi mạng/JSON/HTTP, cache hit, throttle, giải phóng khóa, cấu hình URL và lỗi tạo thư mục. |

Ngoài ra, 9 file PHP và 3 file JavaScript qua kiểm tra cú pháp; PHP tokenizer không tìm thấy lambda/arrow function, các dấu `?` đơn còn lại là kiểu nullable. Acorn AST xác nhận cả 3 file JavaScript không có `ConditionalExpression`. `git diff --check` đạt.

## Giới hạn và dọn dẹp

Đây là kiểm tra hồi quy cho việc tổ chức lại code; không chạy lại HTTP upload, trình duyệt thật, Nominatim công cộng hoặc kiểm thử database trong lượt này. Các báo cáo TV3-04/07/08 trước đó vẫn mô tả source và môi trường tại thời điểm kiểm tra riêng của chúng.

Không mở kết nối database, không thay dữ liệu hoặc cấu hình kết nối. Các script kiểm tra tạm, file fixture và thư mục cache tạm đã được xóa sau khi chạy theo yêu cầu; chỉ giữ báo cáo kết quả. Không sửa API phụ thuộc của thành viên khác.
