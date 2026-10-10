# 04 — Business Flow

> Thiết kế đã chốt để nhóm tham khảo. Core MVC/Auth đã có; các module nghiệp vụ trong tài liệu này chưa được triển khai đầy đủ.

> Luồng sử dụng đã chốt. Mọi kiểm tra truy cập phải xảy ra trên server, kể cả khi người dùng tự gọi URL hoặc AJAX endpoint.

## 1. Khám phá địa điểm

```mermaid
flowchart TD
    A([Khách hoặc thành viên]) --> B[Trang chủ: từ khóa + danh mục + tỉnh/phường]
    B --> C[AJAX tìm kiếm/lọc/phân trang]
    C --> D[Chỉ lấy Place APPROVED]
    D --> E{Có kết quả?}
    E -- Không --> F[Trạng thái không có kết quả]
    E -- Có --> G[Danh sách địa điểm]
    G --> H[Chi tiết: ảnh - bản đồ - đánh giá VISIBLE]
    H --> I{Chọn hành động cần tài khoản?}
    I -- Không --> J([Kết thúc xem])
    I -- Có --> K{Đăng nhập?}
    K -- Không --> L[Trang đăng nhập riêng]
    L --> M[Quay về đúng trang/hành động đã chọn]
    M --> K
    K -- Có --> N[Đánh giá hoặc yêu thích]
```

- Màn hình tìm kiếm chính nằm **ngay trên trang chủ**. Kết quả hỗ trợ loading, empty, error.
- Chỉ xem `APPROVED`; review công khai phải `VISIBLE`. Bài thuộc category `is_active=0` vẫn tìm thấy nếu đã duyệt.
- Nếu khách chọn đánh giá/yêu thích, hiển thị login riêng và quay lại chức năng ban đầu.

## 2. Gửi mới, sửa và kiểm duyệt địa điểm

```mermaid
flowchart TD
    A([Member muốn đăng hoặc sửa]) --> B{Đã đăng nhập?}
    B -- Không --> C[Đăng nhập rồi quay lại form]
    C --> B
    B -- Có --> D{Tạo mới hay sửa?}
    D -- Tạo mới --> E[Form thông tin - tỉnh/phường - 1 đến 3 ảnh - tối đa 5 nhãn]
    D -- Sửa --> F{Là chủ và trạng thái PENDING/REJECTED?}
    F -- Không --> G[403 hoặc thông báo không được sửa]
    F -- Có --> E
    E --> H{Dữ liệu hợp lệ?}
    H -- Không --> E
    H -- Có --> I[Transaction PDO: lưu Place - ảnh - nhãn]
    I --> J{Lưu đồng bộ thành công?}
    J -- Không --> K[Rollback DB + dọn file mới + báo lỗi]
    K --> E
    J -- Có --> L[Place PENDING - chờ Admin]
    L --> M[Admin mở danh sách chờ duyệt]
    M --> N{Phát hiện trùng hoặc nội dung không hợp lệ?}
    N -- Có --> O[Admin kiểm tra và quyết định]
    N -- Không --> O
    O --> P{Duyệt?}
    P -- Có --> Q[APPROVED - công khai]
    P -- Không --> R[REJECTED + lý do bắt buộc]
    R --> S[Chủ xem lý do - sửa - gửi lại]
    S --> F
    Q --> T{Admin ẩn sau này?}
    T -- Có --> U[HIDDEN - không công khai]
    U --> V{Admin khôi phục?}
    V -- Có --> Q
```

- Khi sửa bài `PENDING`, dữ liệu cập nhật vẫn ở `PENDING`. Khi sửa/gửi lại `REJECTED`, trở về `PENDING`, xóa lý do cũ sau khi thành công.
- Mỗi lần gửi kiểm tra 1–3 ảnh, tối đa 5 tag, tỉnh bắt buộc, phường hợp lệ và category không bị vô hiệu với bài mới.
- Quyền sửa chỉ dành cho **chủ bài**, không theo role member chung. Admin thực hiện duyệt/từ chối và đổi trạng thái, không thay tác giả sửa mô tả.
- Nếu cùng tên/địa chỉ: cảnh báo, **không tự từ chối**; admin quyết định.
- API tìm tọa độ bằng PHP/Nominatim sau khi bấm nút; thất bại có thể chọn điểm thủ công trên bản đồ hoặc không có tọa độ.

## 3. Đánh giá, yêu thích và hoạt động cá nhân

```mermaid
flowchart TD
    A([Member ở chi tiết APPROVED]) --> B{Chọn hành động}
    B -- Viết đánh giá --> C[Nhập rating 1 đến 5 + bình luận]
    C --> D{Hợp lệ?}
    D -- Không --> C
    D -- Có --> E[SAVE_OR_UPDATE_REVIEW]
    E --> F{Review cũ HIDDEN?}
    F -- Có --> G[Cập nhật nội dung nhưng giữ HIDDEN]
    F -- Không --> H[Tạo hoặc cập nhật VISIBLE]
    B -- Yêu thích --> I[AJAX gửi trạng thái mong muốn]
    I --> J[SET_FAVORITE idempotent]
    J --> K[Giữ một quan hệ user/place hoặc xóa quan hệ]
    B -- Xem hoạt động --> L[Danh sách yêu thích hoặc nơi đã bình luận]
    L --> M[Lọc user từ Session; chi tiết chỉ công khai nếu APPROVED]
    G --> N[Admin có thể khôi phục VISIBLE]
    H --> O[Admin có thể ẩn HIDDEN]
```

- Đánh giá bắt buộc sao + bình luận. Review đã ẩn chỉ admin được khôi phục; chủ review sửa không làm công khai.
- Yêu thích gửi **trạng thái mong muốn** `is_favorite=true/false`, chống thêm lặp. Chỉ nơi `APPROVED` cho thêm yêu thích.
- Danh sách nơi đã bình luận là truy vấn `reviews JOIN places`, không phải bảng lịch sử mới.

## 4. Quản trị danh mục và đánh giá

```mermaid
flowchart TD
    A([Admin đăng nhập]) --> B[Dashboard]
    B --> C{Chọn module}
    C -- Danh mục --> D[Thêm / sửa / ngừng dùng category]
    D --> E[Bài cũ đã duyệt vẫn công khai]
    C -- Địa điểm --> F[Danh sách + chi tiết duyệt]
    F --> G[SET_PLACE_STATUS: duyệt / từ chối có lý do / ẩn / khôi phục]
    C -- Đánh giá --> H[Danh sách review]
    H --> I[SET_REVIEW_STATUS: HIDDEN hoặc VISIBLE]
```

## 5. Trạng thái lỗi bắt buộc trên UI

- `401`: khách chưa đăng nhập → login, có thông tin điều hướng quay về.
- `403`: không phải chủ bài hoặc không phải Admin → chặn ở Controller, không thay đổi DB.
- `404`: không có địa điểm công khai hoặc không tồn tại.
- `422`: dữ liệu form không hợp lệ → nêu lỗi từng trường, giữ dữ liệu đã nhập hợp lý.
- `409`: xung đột (ví dụ trạng thái đã thay đổi, dữ liệu trùng email).
- `429/502/503`: geocoding/API lỗi hoặc vượt hạn mức → hiển thị thông báo, vẫn cho chọn vị trí thủ công nếu có thể.
- Nút submit có loading/disable trong lúc gửi; không tạo trùng dữ liệu khi người dùng bấm liên tiếp.
