# Việc cần phối hợp cho C03 trên máy Thành

Kiểm tra 07/11/2026 trên source `10b2cbb`, đọc API thật và `SHOW CREATE PROCEDURE` của DB local; không thay routine hoặc module TV2 trong lượt này.

## Thuần / TV2-06

API và JS đã có trên main, không còn thiếu route. Vướng mắc còn lại trên DB máy Thành là hợp đồng kết quả:

- `PTH_LIST_WARDS_BY_PROVINCE(IN p_province_code VARCHAR(10))` local chỉ SELECT `ward_code, ward_name, ward_type`.
- `Ward::findActiveByProvinceCode()` trả nguyên dữ liệu routine.
- `locations.js` chỉ render phần tử có `wardItem.province_code === provinceCode`; vì thiếu trường nên 126 ward Hà Nội bị loại hết dù HTTP 200.

**Thuần cần thống nhất và bảo đảm mỗi phần tử response có thêm `province_code` dạng chuỗi.** Cách chính là đồng bộ routine vào DB sử dụng bằng script SQL chính thức của TV2, giữ validation và chữ ký hiện có; phần SELECT trả ít nhất:

```sql
SELECT ward_code, province_code, ward_name, ward_type
FROM wards
WHERE province_code = p_province_code AND is_active = 1
ORDER BY ward_name, ward_code;
```

Đây là hình dạng SELECT cần đạt, không phải script thay toàn bộ routine để chạy ngay. Cần lấy định nghĩa chính thức của Thuần, sao lưu rồi kiểm tra trên DB sao chép trước khi áp dụng DB chính. Nếu TV2 muốn hỗ trợ routine cũ, có thể chuẩn hóa response tại Ward Model từ tham số đã bind; vẫn giữ routine đọc đúng tỉnh và không viết thêm API/SQL trùng ở TV3.

Sau đồng bộ, kiểm tra:

1. API mã `01` trả `province_code: "01"`, `ward_code` dạng chuỗi và đúng tỉnh; ward inactive không xuất hiện.
2. Dropdown có lựa chọn; đổi tỉnh xóa ward cũ; A→B nhanh chỉ hiện ward B; mở form sửa khôi phục đúng ward ban đầu.
3. Tạo/sửa PENDING/REJECTED chấp nhận ward hợp lệ hoặc rỗng; từ chối ward sai tỉnh ở server.
4. Không có ward, timeout hoặc offline vẫn cho để trống; mỗi script locations/form/map/AI chỉ tải một lần.

## Thuận / TV4

Board hiện ghi TV4-01 đến TV4-08 đều DONE. TV4-04 còn chuỗi `blocked_reason: "TV3"` dù status DONE; đây là thông tin cần leader rà lại, không tự suy thành một blocker code đang tồn tại. Không có căn cứ trong lượt đọc board để yêu cầu Thuận làm API C03, vì C03 thuộc Thuần/TV2.

Nếu cần nghiệm thu tích hợp cuối, phối hợp TV4 kiểm tra chuỗi Thành tạo/sửa/gửi lại bài → Admin duyệt/từ chối/ẩn → Home/Detail/Review cập nhật đúng; không tự làm lại module Admin/Review.
