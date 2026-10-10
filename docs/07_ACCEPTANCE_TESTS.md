# 07 — Test case thủ công và tiêu chí nghiệm thu

> Chỉ kiểm thử thủ công theo test case, có ảnh minh chứng. Không yêu cầu video. Kết quả kiểm thử dưới đây đang là **Not run** vì chưa có code/database hoạt động.

## A. Test case bắt buộc

| ID | Chức năng / điều kiện | Bước test ngắn | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| AU-01 | Đăng ký hợp lệ | Nhập tên, email mới, password hợp lệ | Lưu hash, tạo tài khoản Member | Not run |
| AU-02 | Email trùng | Đăng ký email đã có | Báo lỗi, không tạo trùng | Not run |
| AU-03 | Đăng nhập sai | Nhập password sai | Không tạo session đăng nhập | Not run |
| AU-04 | Đăng nhập thành công | Đăng nhập đúng | Tạo session, chuyển đúng trang/luồng trước login | Not run |
| AU-05 | Phân quyền Admin | Member tự gọi route `admin/places` | HTTP 403, DB không đổi | Not run |
| AU-06 | Quyền chủ bài | Member B sửa bài Member A | 403, DB không đổi | Not run |
| AU-07 | Đăng xuất | Submit logout đúng CSRF | Session kết thúc, không vào trang Member | Not run |
| PL-01 | Đăng địa điểm | Nhập đủ dữ liệu, 1 ảnh | Place `PENDING`, có ảnh, owner đúng | Not run |
| PL-02 | 0 ảnh | Gửi bài không có ảnh | Báo lỗi, không tạo Place công khai | Not run |
| PL-03 | Quá 3 ảnh | Upload 4 ảnh | Báo lỗi, không lưu phần vượt | Not run |
| PL-04 | Ảnh không hợp lệ | Upload giả ảnh/định dạng cấm | Từ chối, không lưu file thực thi | Not run |
| PL-05 | Transaction lỗi | Cố ý tạo lỗi lưu ảnh lần 2 | DB rollback, xóa file mới đã tạo | Not run |
| PL-06 | Tỉnh/phường | Chọn ward thuộc tỉnh khác | Server bác bỏ dữ liệu sai | Not run |
| PL-07 | Tag | Chọn 6 nhãn | Báo lỗi; tối đa 5, không thêm trùng | Not run |
| PL-08 | Bài chưa duyệt | Truy cập URL Place `PENDING` | Khách bị 404/không thấy nội dung | Not run |
| PL-09 | Chủ sửa chờ duyệt | Sửa bài `PENDING` | Lưu đúng, vẫn `PENDING` | Not run |
| PL-10 | Từ chối và gửi lại | Admin nhập lý do; Member sửa/gửi lại | `REJECTED` có lý do → `PENDING` | Not run |
| PL-11 | Khôi phục | Admin ẩn rồi khôi phục | `APPROVED` → `HIDDEN` → `APPROVED` | Not run |
| PL-12 | Bài trùng | Thêm tên/địa chỉ gần trùng | Cảnh báo, admin tự quyết định | Not run |
| CT-01 | Danh mục inactive | Tắt danh mục có bài `APPROVED` | Bài cũ vẫn hiển thị, bài mới không chọn được | Not run |
| SE-01 | Search/filter | Tìm keyword + category + tỉnh | Chỉ Place `APPROVED`, phân trang đúng | Not run |
| SE-02 | Empty | Tìm từ khóa không tồn tại | Thông báo không có kết quả, không lỗi PHP | Not run |
| RV-01 | Tạo đánh giá | 1–5 sao và bình luận | Review `VISIBLE` | Not run |
| RV-02 | Đánh giá trùng | Cùng user đánh giá lại Place | Update dòng cũ, không insert thêm | Not run |
| RV-03 | Nội dung rỗng | Chỉ chọn sao, bình luận whitespace | Server báo validation | Not run |
| RV-04 | Review bị ẩn | Admin ẩn; chủ bài chỉnh nội dung | Review vẫn `HIDDEN`, không tính điểm công khai | Not run |
| RV-05 | Review khôi phục | Admin đổi `HIDDEN` → `VISIBLE` | Hiển thị lại công khai khi Place approved | Not run |
| FV-01 | Yêu thích trùng | Gửi add 2 lần | Chỉ 1 row Favorites, UI nhất quán | Not run |
| FV-02 | Bỏ yêu thích | Đặt `is_favorite=false` | Quan hệ biến mất | Not run |
| FV-03 | Place bị ẩn | Trước đó yêu thích Place rồi admin ẩn | Danh sách yêu thích không mở bài ẩn công khai | Not run |
| MY-01 | Nơi đã bình luận | Xem tài khoản Member A | Chỉ thấy reviews của A, không của B | Not run |
| API-01 | Danh sách wards | Chọn tỉnh | AJAX tải wards đúng tỉnh; lỗi có thông báo | Not run |
| API-02 | Geocode thành công | Bấm tìm tọa độ | PHP gọi Nominatim, hiển thị marker có ghi công | Not run |
| API-03 | Geocode timeout/rate-limit | Giả lập lỗi ngoài | Không crash, vẫn nhập địa chỉ/chọn marker thủ công | Not run |
| UI-01 | Desktop/mobile | Mở các màn hình chính ở 2 kích thước | Không tràn khung, form/menus/map dùng được | Not run |
| DP-01 | Máy khác | Thiết lập theo README trên máy thứ 2 | Chạy được với shared MariaDB, không cần secret trong Git | Not run |
| DP-02 | Hosting miễn phí | Deploy lên host đủ điều kiện | Có URL online, HTTPS/API/uploads/MariaDB hoạt động | Not run |

## B. Điều kiện nghiệm thu theo module

- **Auth:** đăng ký/đăng nhập/đăng xuất + đổi hồ sơ, băm mật khẩu, không có lộ thông tin tài khoản, chặn truy cập trái quyền.
- **Địa điểm:** submit, sửa PENDING, gửi lại REJECTED, 1–3 ảnh, 0–5 tags, location, trạng thái đúng và transaction ổn định.
- **Explore:** tìm/lọc/phân trang, chi tiết ảnh/map, không lộ PENDING/REJECTED/HIDDEN.
- **Reviews/Favorites:** AJAX chuẩn, 1 review và 1 favorite quan hệ trên mỗi user/place, hiển thị đúng khi bài bị ẩn.
- **Admin:** quản lý categories, duyệt/từ chối lý do, ẩn/khôi phục Place/Review, quyền Admin được kiểm tra ở server.
- **Nominatim:** có nhận diện ứng dụng, giới hạn API, cache, attribution, lỗi không làm gián đoạn form.
- **Deployment:** local đầy đủ, hosting miễn phí đạt nếu tài nguyên tương thích; có phương án ghi lại blocker nếu host không cho outbound API/upload.

## C. Dữ liệu và demo

- **Seed thật** (sau bước SQL): 34 tỉnh/thành và danh mục xã/phường/đặc khu theo nguồn nhà nước đã đối chiếu; 20–30 địa điểm thuộc nhiều tỉnh (đã duyệt và các trạng thái thử), categories và tags.
- **Tài khoản:** 1 Admin, tối thiểu 2 Member (demo credentials do nhóm lưu an toàn, không commit plaintext vào Git).
- **Demo trực tiếp**: khách tìm/chi tiết → Member login/đăng Place → Admin duyệt hoặc từ chối → Member sửa/gửi lại → Review + Favorites → Admin ẩn/khôi phục → responsive/map.
- **Không làm video**, tập trung website chạy, báo cáo và demo trực tiếp.

## D. Hồ sơ nộp và bằng chứng

| Nhóm tài liệu | Bằng chứng cần có |
|---|---|
| Mô tả yêu cầu | Đề tài, vai trò, phạm vi, yêu cầu giảng viên |
| Sơ đồ | Business Flow, chức năng, ERD, Class Diagram; đối chiếu screen/route |
| UI | Screenshot desktop và mobile của các màn hình đã chạy |
| Database | `schema.sql`, `seed.sql`, routine tổng hợp + mỗi người; ảnh PK/FK/relations/import/call |
| Source | Repo chạy được, `.gitignore`, README cài đặt, cấu hình mẫu |
| Teamwork | GitHub commit/branch/PR, GitHub Projects/Issues, Teams, shared MariaDB |
| Test | Bảng testcase có **Pass/Fail**, ảnh lỗi thành công/thất bại đã thực thi |
| Demo | Local; URL hosting nếu chạy; tài khoản demo bảo mật |

## E. Nguyên tắc đóng băng

- **Từ ngày 18 đến 21:** không thêm chức năng hoặc đổi scope; chỉ sửa lỗi, test, tối ưu trải nghiệm, chuẩn bị báo cáo/demo.
- Không có nút trên prototype mà không có route/xử lý; không có route chức năng mà thiếu kiểm thử tối thiểu.
- Chỉ đánh dấu Done khi chạy trên `main` dùng shared MariaDB và có bằng chứng test, không dựa trên việc code xong ở branch cá nhân.
