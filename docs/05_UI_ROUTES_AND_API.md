# 05 — Màn hình, Route/Controller/Model và API

> Đặc tả triển khai từ foundation v3; source hiện tại chưa có đầy đủ các lớp/route/luồng này. A01/A02 chỉ config và schema; xem báo cáo A02.

> Quy ước URL được xác nhận: **một front controller** `public/index.php?route=...`. Các tên route bên dưới là quy ước tài liệu chuẩn để nhóm lập trình, không phải endpoint hiện đã chạy.

## A. Danh sách giao diện

| Khu vực | Màn hình/khối UI | Hiển thị và thao tác bắt buộc |
|---|---|---|
| Công khai | Trang chủ | Ô tìm kiếm, chọn category/tỉnh/phường, phân trang, cards ảnh và trạng thái loading/empty |
| Công khai | Chi tiết địa điểm | Ảnh, danh mục, địa chỉ, bản đồ, nhãn, sao trung bình, danh sách review, form review và yêu thích |
| Auth | Đăng nhập | Email + mật khẩu, lỗi sai, redirect quay về hành động ban đầu |
| Auth | Đăng ký | Tên, email, mật khẩu, kiểm tra lỗi từng trường |
| Thành viên | Hồ sơ dạng tab | Thông tin tài khoản, đổi tên/email; các tab bài đã đăng/yêu thích/đã bình luận |
| Thành viên | Địa điểm của tôi | Danh sách trạng thái, lý do từ chối, nút sửa khi `PENDING`/`REJECTED` |
| Thành viên | Form địa điểm | Dùng chung form đăng/sửa; tỉnh bắt buộc, phường tùy chọn, 1–3 ảnh, tối đa 5 nhãn, bản đồ/Nominatim |
| Thành viên | Yêu thích | Chỉ hiển thị thông tin chi tiết những địa điểm hiện `APPROVED`; bỏ yêu thích |
| Thành viên | Đã bình luận | Review của tôi + địa điểm tương ứng; trạng thái bài bị ẩn |
| Admin | Dashboard | Số liệu đếm cơ bản, lối tắt bài chờ duyệt; không thống kê nâng cao |
| Admin | Danh sách/chi tiết địa điểm | Duyệt, từ chối có lý do, ẩn/khôi phục, cảnh báo địa điểm trùng |
| Admin | Danh mục | Danh sách, thêm, sửa, ngừng sử dụng |
| Admin | Đánh giá | Danh sách, ẩn/khôi phục, hiển thị chủ review/địa điểm |

**Responsive:** ưu tiên desktop; mobile phải dùng được navbar, filter, card, form upload, map, bảng Admin (table responsive) và tab hồ sơ.

## B. Bảng route chính thức để triển khai

| HTTP | `route` | Quyền | Controller | Model / procedure | Kết quả |
|---|---|---|---|---|---|
| GET | `home` | Public | `PlaceController::index` | Place / `SEARCH_APPROVED_PLACES` | HTML |
| GET | `places/show` (+`id`) | Public, chỉ bài approved | `PlaceController::show` | Place, PlaceImage, Tag, Review | HTML |
| GET | `auth/login` | Public | `AuthController::loginForm` | — | HTML |
| POST | `auth/login` | Public | `AuthController::login` | User / `GET_USER_BY_EMAIL` | Redirect + Session |
| GET | `auth/register` | Public | `AuthController::registerForm` | — | HTML |
| POST | `auth/register` | Public | `AuthController::register` | User / `CREATE_USER` | Redirect |
| POST | `auth/logout` | Signed in | `AuthController::logout` | Session | Redirect |
| GET | `profile` | Member | `AuthController::profile` | User | HTML |
| POST | `profile/update` | Member | `AuthController::updateProfile` | User / SQL | Redirect |
| GET | `places/mine` | Member | `PlaceController::mine` | Place / SQL | HTML |
| GET | `places/create` | Member | `PlaceController::create` | Category, Province, Tag | HTML |
| GET | `places/edit` (+`id`) | Chủ bài PENDING/REJECTED | `PlaceController::edit` | Place, PlaceImage, PlaceTag | HTML |
| POST | `places/submit` | Member | `PlaceController::submit` | `SUBMIT_PLACE` + `ADD_PLACE_IMAGE` + SQL PlaceTag | Redirect |
| POST | `places/resubmit` | Chủ bài PENDING/REJECTED | `PlaceController::resubmit` | `RESUBMIT_PLACE` + ảnh/tag trong transaction | Redirect |
| GET | `profile/favorites` | Member | `FavoriteController::listMine` | Favorite + Place / SQL | HTML |
| GET | `profile/reviewed` | Member | `ReviewController::listMine` | Review + Place / SQL | HTML |
| GET | `api/places/search` | Public | `PlaceController::searchAjax` | `SEARCH_APPROVED_PLACES` | JSON |
| POST | `api/favorites/set` | Member | `FavoriteController::setFavorite` | `SET_FAVORITE` | JSON |
| POST | `api/reviews/save` | Member | `ReviewController::saveAjax` | `SAVE_OR_UPDATE_REVIEW` | JSON |
| GET | `api/locations/wards` | Public | `LocationController::listWardsAjax` | `LIST_WARDS_BY_PROVINCE` | JSON |
| POST | `api/locations/geocode` | Member | `LocationController::geocodeAjax` | `NominatimClient` + cache | JSON |
| GET | `admin` | Admin | `AdminController::dashboard` | SQL tổng hợp | HTML |
| GET | `admin/places` | Admin | `AdminController::places` | Place / SQL | HTML |
| GET | `admin/places/show` (+`id`) | Admin | `AdminController::placeDetail` | Place, PlaceImage, Review | HTML |
| POST | `admin/places/status` | Admin | `AdminController::setPlaceStatus` | `SET_PLACE_STATUS` | Redirect hoặc JSON chuẩn |
| GET | `admin/categories` | Admin | `AdminController::categories` | Category / SQL | HTML |
| POST | `admin/categories/save` | Admin | `AdminController::saveCategory` | Category / SQL | Redirect |
| POST | `admin/categories/deactivate` | Admin | `AdminController::deactivateCategory` | Category / SQL | Redirect |
| GET | `admin/reviews` | Admin | `AdminController::reviews` | Review / SQL | HTML |
| POST | `admin/reviews/status` | Admin | `AdminController::setReviewStatus` | `SET_REVIEW_STATUS` | Redirect hoặc JSON chuẩn |

**Hợp đồng route:** `?route=home` mặc định (không có route), route chưa biết → 404, route hợp lệ nhưng sai HTTP method → 405. Mọi POST phải kiểm tra CSRF. Tên route và action ở bảng này là *source of truth* cho dự án trước khi bắt đầu lập trình; nếu đổi, sửa tài liệu và thông báo nhóm.

## C. Quy chuẩn JSON AJAX

```json
{
  "success": true,
  "message": "Đã cập nhật yêu thích",
  "data": {"place_id": 15, "is_favorite": true},
  "errors": {}
}
```

```json
{
  "success": false,
  "message": "Dữ liệu không hợp lệ",
  "data": null,
  "errors": {"rating": ["Số sao phải từ 1 đến 5"]}
}
```

- Luôn có đủ 4 trường; `success` là boolean; `errors` là object (rỗng khi thành công); `data` object/list/null.
- HTTP 200 thành công; 201 khi tạo mới API; 400 input format lỗi; 401 chưa đăng nhập; 403 không có quyền; 404 không có; 409 xung đột; 422 validation; 429 hạn mức; 500 lỗi server.
- AJAX sử dụng `fetch`; gửi CSRF header hoặc field; nếu đang xử lý thì disable nút; tránh UI báo thành công khi server lỗi.
- Tìm kiếm là GET, giữ query string có thể chia sẻ; `page` nguyên dương, kích thước trang cố định từ config (ví dụ 9/12), xử lý keyword rỗng.
- Yêu thích dùng `is_favorite` mong muốn, không đảo trạng thái mù để chống double-click.
- Review `HIDDEN` được lưu mà vẫn `HIDDEN`; response không được công khai review vừa sửa như thể đã được duyệt lại.

## D. Nominatim / Leaflet / OSM

- Người dùng nhập địa chỉ rồi **bấm nút “Tìm tọa độ”**; PHP server gọi Nominatim, không autocomplete từng phím.
- Cache kết quả theo địa chỉ chuẩn hóa tại `storage/cache/geocoding/` (ngoài `public`), dùng khóa và khóa truy cập đồng bộ; tổng lưu lượng của **toàn site** không vượt 1 request/giây; nên rate-limit tập trung nếu nhiều web workers.
- Gửi User-Agent/Referer nhận diện Hot Spot, có thông tin liên hệ khi triển khai, hiển thị nguồn OSM. Xử lý timeout, không có kết quả, lỗi giới hạn API, không trả nguyên thông báo nhạy cảm.
- Leaflet hiển thị map trên chi tiết và form, cho phép đặt marker thủ công. `latitude`/`longitude` có thể NULL; nếu NULL thì hiển thị địa chỉ mà không hiện marker.
- OpenStreetMap tiles phải có attribution và không tải/prefetch hàng loạt; hosting phải cho PHP kết nối HTTPS ra ngoài.

Nguồn: https://operations.osmfoundation.org/policies/nominatim/ và https://operations.osmfoundation.org/policies/tiles/.

## E. Quy chuẩn code nhóm

- Đặt tên file PSR-like theo class: `PlaceController.php`, `Place.php`; route dùng chữ thường, dấu `/`, không trộn chữ Việt trong URL.
- Controller không viết SQL; Model không echo HTML; View không sửa DB.
- `user_id` lấy từ Session; SQL tham số hóa bằng PDO; mật khẩu hash; output HTML escape; CSRF trên POST, kiểm tra quyền ở server.
- Cấu hình dùng `database.example.php`; `database.php` ngoài Git. Log và uploaded files ngoài repository, không commit đường dẫn chứa bí mật.
- Đổi schema qua Pull Request, có SQL chỉnh sửa và hướng dẫn cho DB chung; không tự ý DROP bảng trên shared DB.
