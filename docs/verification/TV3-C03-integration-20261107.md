# TV3 — Kiểm tra C03 và góp ý leader còn mở

Kiểm tra ngày 07/11/2026 trên máy XAMPP của Thành, sau khi đồng bộ `origin/main` tại `e58e021` vào nhánh `thanhCT`.

## Board

Đọc trực tiếp workspace của [Mission Control](https://hotspot-team-mission-control.khangba153.chatgpt.site/board): TV3-01 đến TV3-07 đều `DONE`; TV3-08 còn `IN_REVIEW`. Review mới nhất của TV3-08 ngày 07/11 ghi code rõ hơn nhưng Nominatim và tile OSM thật vẫn lỗi kết nối. Cần kiểm chứng tìm địa chỉ thật, chọn kết quả, kéo ghim, lưu; empty/timeout và đổi địa chỉ lúc chờ; quyền cache bằng user PHP trên hosting.

TV2-06 và TV3-02 đã được leader chấp thuận sau PR #41. Review đó chạy trong môi trường riêng của reviewer, không chứng minh routine trên máy Thành đã đồng bộ.

## C03 trên máy Thành

Trước khi đồng bộ, `GET /hotspot/index.php?route=api/locations/wards&province_code=01` trả **404** vì nhánh chưa có route/Controller/Model mới.

Sau đồng bộ, form nạp `locations.js` trước `place-form.js` và chỉ gọi `HotSpotLocations.bindWardSelect()`; logic tải ward trùng trong form đã được loại bỏ bởi bản main. Các script Leaflet/map/AI description vẫn được giữ.

| Gọi HTTP thật trên localhost | Kết quả |
|---|---|
| GET province_code=01 | 200, success=true, 126 ward |
| GET province_code=48 | 200, success=true, 94 ward |
| GET province_code=79 | 200, success=true, 168 ward |
| GET thiếu mã, mã không hợp lệ, mã dạng mảng | 422 |
| POST cùng route | 405 |

Đếm read-only trong bảng `wards` khớp 126/94/168 và tất cả đang active. Gọi Model/routine 5 lần xen `SELECT 1` không có lỗi result set chưa đọc hết. Mã ward đầu Hà Nội `00004` vẫn là chuỗi.

**Vấn đề chưa giải quyết:** response của DB local chỉ có `ward_code`, `ward_name`, `ward_type`. `SHOW CREATE PROCEDURE PTH_LIST_WARDS_BY_PROVINCE` xác nhận phần SELECT cũng chỉ trả ba cột này. Trong khi đó `locations.js` chỉ nhận phần tử có `wardItem.province_code === provinceCode`.

Chạy chính `locations.js` trong Node VM với response HTTP thật từ localhost: API trả 126 phần tử nhưng số option ward được render là **0**, select bị disabled và báo không có phường/xã. Đây là kiểm tra JavaScript mô phỏng DOM kết hợp HTTP thật, chưa phải trình duyệt thật.

Kết luận: **route/API đã kết nối, nhưng dropdown chưa hoạt động đầy đủ trên DB máy Thành**. Cần đồng bộ đầu ra routine/provider để mỗi ward có thêm `province_code` dạng chuỗi, rồi kiểm tra lại dropdown và lưu ward hợp lệ/rỗng/sai tỉnh. Không nên kết luận hoàn tất chỉ vì API trả HTTP 200.

## TV3-08 và phạm vi

Lượt kiểm tra hiện tại: DNS của `nominatim.openstreetmap.org` và `tile.openstreetmap.org` đều không phân giải được; PHP cURL gọi Nominatim thật nhận HTTP 0, errno 6. Vì vậy chưa xác nhận được luồng thành công với dịch vụ công cộng, và chưa kiểm quyền cache trên hosting.

Chỉ chạy truy vấn đọc database và GET API; POST ward bị Router chặn 405, không ghi dữ liệu. Không thay schema/routine DB, không sửa module API/locations.js của TV2, không thay cấu hình kết nối và không cập nhật board. Kiểm tra PHP/JS các file mới đạt cú pháp; `git diff --check` đạt. Không tạo file script test tạm trong lượt này.
