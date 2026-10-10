# 01 — Yêu cầu, phạm vi và quy tắc nghiệp vụ

> Bản thiết kế đã được chốt sau 8 giai đoạn. Phân biệt rõ **bắt buộc theo tài liệu giảng viên** và **cam kết của nhóm**.

## A. Đối chiếu yêu cầu môn học

| Nguồn | Nội dung | Cách Hot Spot đáp ứng | Minh chứng dự kiến |
|---|---|---|---|
| Đề giảng viên | Một trong 4 chủ đề: thương mại điện tử, mạng xã hội, tin tức, diễn đàn | Hot Spot thuộc hướng website cộng đồng/mạng xã hội chia sẻ địa điểm | Mô tả đề tài + chức năng đóng góp và đánh giá |
| Đề giảng viên — bắt buộc | Thiết kế dùng Bootstrap | Bootstrap responsive | Code, ảnh desktop/mobile |
| Đề giảng viên — bắt buộc | Backend viết theo hướng đối tượng | PHP OOP | Model, Controller, Class Diagram |
| Đề giảng viên — khuyến khích | OOP + MVC, tích hợp AJAX/Webservice **ở mức sử dụng**; XML/JSON được ưu tiên | PHP MVC + AJAX JSON + dùng Nominatim | Luồng HTTP, thao tác AJAX, log/demo API |
| Đề giảng viên | Admin cơ bản | Quản lý danh mục và kiểm duyệt nội dung (vượt mức CRUD cơ bản) | Demo Admin |
| Đề giảng viên | CSDL linh hoạt | MariaDB 10.4.32 (XAMPP) | SQL, ERD |
| Đề giảng viên | Shared host để dùng DB; GitHub branch; Teams/phần mềm tương tự; minh chứng trong báo cáo | Shared MariaDB, PR/review, GitHub Projects + Teams | Ảnh chụp quá trình thực hiện |
| Đề giảng viên — khuyến khích | Hosting miễn phí nếu được | Mục tiêu deploy online bên cạnh demo local | URL hoặc ghi nhận giới hạn host |
| **Nhóm tự chốt** | **13 bảng, 12 procedure (3/người), 21 ngày, 20–30 địa điểm** | Chỉ tiêu quản lý dự án; **không ghi thành yêu cầu trực tiếp của giảng viên** | Thiết kế, tiến độ, demo |

## B. Phạm vi chức năng

**Khách:** Xem trang chủ/danh sách; tìm kiếm theo từ khóa, danh mục, tỉnh/phường; phân trang; xem chi tiết `APPROVED`, ảnh, bản đồ và đánh giá công khai. Tương tác cần tài khoản điều hướng đến login rồi quay lại màn hình/luồng trước đó.

**Thành viên:** Đăng ký, đăng nhập, đăng xuất, cập nhật tên/email; đăng mới địa điểm 1–3 ảnh, tỉnh bắt buộc, phường tùy chọn, tối đa 5 nhãn seed; xem địa điểm đã đăng; sửa khi `PENDING`/`REJECTED`; xem lý do từ chối/gửi lại; viết/sửa đánh giá (1–5 sao kèm bình luận); thêm/bỏ/và xem yêu thích; xem nơi đã bình luận.

**Admin:** Dashboard đơn giản; tạo/sửa/ngừng sử dụng danh mục; danh sách và chi tiết xét duyệt; duyệt/từ chối kèm lý do; ẩn/khôi phục địa điểm; ẩn/khôi phục đánh giá. Admin cũng có quyền thành viên khi thao tác với nội dung cá nhân.

**Ngoài phạm vi MVP:** thanh toán, đặt bàn, chat, newsfeed, follow, bài nháp, báo cáo đánh giá, moderation logs, tự sửa nội dung `APPROVED`, thống kê nâng cao, video demo.

## C. Ma trận quyền

| Chức năng | Khách | Member | Chủ địa điểm / chủ đánh giá | Admin |
|---|:---:|:---:|:---:|:---:|
| Xem danh sách/chi tiết địa điểm `APPROVED` | ✓ | ✓ | ✓ | ✓ |
| Đăng/sửa hồ sơ của chính mình | — | ✓ | ✓ | ✓ |
| Đăng địa điểm | — | ✓ | ✓ | ✓ |
| Sửa địa điểm `PENDING`/`REJECTED` do mình đăng | — | — | ✓ | ✓* |
| Viết đánh giá địa điểm `APPROVED` | — | ✓ | ✓ | ✓ |
| Sửa đánh giá của chính mình, kể cả `HIDDEN` | — | — | ✓ | ✓* |
| Xem yêu thích/lịch sử bình luận cá nhân | — | ✓ | ✓ | ✓ |
| Quản lý danh mục | — | — | — | ✓ |
| Duyệt/từ chối/ẩn/khôi phục địa điểm | — | — | — | ✓ |
| Ẩn/khôi phục đánh giá bất kỳ | — | — | — | ✓ |

\* Admin chỉ có quyền **sửa nội dung với tư cách chủ bài** nếu đó là bài của chính admin. Khi quản trị bài của người khác, admin đổi trạng thái, không sửa thay nội dung người khác.

**Bất biến bảo mật:** mọi request phải kiểm tra quyền phía server dựa trên Session; người dùng không truyền `user_id` tuỳ ý để sửa dữ liệu người khác. Không coi việc ẩn nút là phân quyền.

## D. Trạng thái và chuyển trạng thái

### Địa điểm

| Trạng thái nguồn | Tác nhân + hành động | Trạng thái đích | Điều kiện |
|---|---|---|---|
| Mới | Member đăng | `PENDING` | Đủ trường bắt buộc, 1–3 ảnh |
| `PENDING` | Chủ bài sửa | `PENDING` | Giữ quyền sở hữu, kiểm tra lại toàn bộ dữ liệu |
| `PENDING` | Admin duyệt | `APPROVED` | Được công khai |
| `PENDING` | Admin từ chối | `REJECTED` | Lý do từ chối không rỗng |
| `REJECTED` | Chủ bài sửa và gửi lại | `PENDING` | Xóa lý do cũ khi gửi lại thành công |
| `APPROVED` | Admin ẩn | `HIDDEN` | Không còn công khai |
| `HIDDEN` | Admin khôi phục | `APPROVED` | Công khai trở lại |

Không tự cho người đăng sửa `APPROVED` hoặc `HIDDEN`. Admin không tự đổi `REJECTED` thành `APPROVED` ngoài luồng gửi lại đã chốt.

### Đánh giá

| Trạng thái nguồn | Hành động | Trạng thái đích |
|---|---|---|
| Chưa có | Member tạo trên địa điểm công khai | `VISIBLE` |
| `VISIBLE` | Chủ đánh giá sửa | `VISIBLE` |
| `VISIBLE` | Admin ẩn | `HIDDEN` |
| `HIDDEN` | Chủ đánh giá sửa | `HIDDEN` (không công khai tự động) |
| `HIDDEN` | Admin khôi phục | `VISIBLE` |

Chỉ đánh giá `VISIBLE` của địa điểm `APPROVED` được hiển thị công khai/tính điểm trung bình.

## E. Ràng buộc dữ liệu và tình huống đặc biệt

1. **Địa điểm:** tiêu đề, mô tả, địa chỉ, danh mục, tỉnh/thành là bắt buộc; phường/xã tùy chọn nhưng nếu có phải thuộc tỉnh đã chọn; cặp tọa độ cùng `NULL` hoặc cùng hợp lệ. Tối thiểu 1, tối đa 3 ảnh.
2. **Nhãn:** chọn từ `tags` đã seed và `is_active=1`, tối đa 5 nhãn/địa điểm; không cần màn Admin quản lý nhãn.
3. **Danh mục tắt:** chỉ chặn **bài mới chọn** danh mục đó; địa điểm cũ `APPROVED` vẫn xem/tìm được. Khi chủ bài sửa mà giữ nguyên danh mục cũ đã tắt, cần tránh ép đổi danh mục nếu không có yêu cầu thay đổi loại bài.
4. **Địa điểm trùng:** cảnh báo gần trùng theo tên/địa chỉ, không áp `UNIQUE(title,address)` cứng; admin quyết định khi duyệt.
5. **Đánh giá:** `rating` từ 1 đến 5, `content` không rỗng sau trim, `UNIQUE(user_id,place_id)`; nếu cùng người gửi lại thì **cập nhật** đánh giá cũ, không thêm dòng mới.
6. **Yêu thích:** thao tác đặt trạng thái yêu thích theo giá trị mong muốn (idempotent); không thêm trùng; không trả bài `HIDDEN` trong danh sách yêu thích công khai.
7. **Lịch sử bình luận:** từ `reviews JOIN places`, lọc `user_id` từ Session; nơi bị ẩn có thể hiển thị trạng thái trong lịch sử cá nhân nhưng không mở chi tiết công khai.
8. **Upload:** validate kích thước, MIME thực, đuôi file; tên ngẫu nhiên; không thực thi file tải lên; transaction DB + bù trừ xóa file nếu lỗi.
9. **Dữ liệu cá nhân:** mật khẩu chỉ lưu hash; session sau login phải tái tạo ID; logout kết thúc session; output escape HTML; state-changing POST có CSRF.

## F. Danh sách màn hình cần xây

| Nhóm | Màn hình |
|---|---|
| Công khai | Trang chủ + tìm kiếm/lọc; Chi tiết địa điểm (ảnh, bản đồ, đánh giá) |
| Auth | Đăng nhập; Đăng ký |
| Thành viên | Hồ sơ/tab; Địa điểm của tôi; Form đăng/sửa/gửi lại; Yêu thích; Nơi đã bình luận |
| Admin | Dashboard; Danh sách địa điểm + chi tiết xét duyệt; Quản lý danh mục; Quản lý đánh giá |

Chỉ có **một form đăng/sửa địa điểm** dùng lại cho hai trường hợp; đánh giá nằm trong trang chi tiết, không cần trang riêng. Mọi màn hình cần trạng thái tải, trống, lỗi, không có quyền và responsive.
