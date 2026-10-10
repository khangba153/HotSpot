# 06 — Phân công, quy trình Git và kế hoạch 21 ngày

> **Đã chốt:** Khang tích hợp; TV4 **tự làm toàn bộ Admin và đánh giá**. Thành viên khác **chỉ hỗ trợ kiểm thử** phần đó. Làm trực tiếp trên **shared MariaDB**, PR/review trước khi merge.

## A. Bảng phụ trách và trách nhiệm chính

| Người | 13 bảng chia theo 3–4–3–3 | 3 routine logic | Chức năng UI/backend | Nghĩa vụ ngoài code |
|---|---|---|---|---|
| **Khang (leader)** | `roles`, `users`, `favorites` | `CREATE_USER`, `GET_USER_BY_EMAIL`, `SET_FAVORITE` | Khung MVC, Router/PDO, Session/Auth, Profile, Favorites | Tổ chức GitHub, review, tích hợp PR, đảm bảo main chạy |
| **TV2** | `categories`, `provinces`, `wards`, `tags` | `LIST_ACTIVE_CATEGORIES`, `SEARCH_APPROVED_PLACES`, `LIST_WARDS_BY_PROVINCE` | Home, tìm kiếm/lọc, phân trang, AJAX tỉnh/phường; hiển thị danh mục/nhãn | Seed hành chính, phối hợp tên cột/schema |
| **TV3** | `places`, `place_images`, `place_tags` | `SUBMIT_PLACE`, `ADD_PLACE_IMAGE`, `RESUBMIT_PLACE` | Trang chi tiết, đăng/sửa/gửi lại, upload, bản đồ Leaflet, tích hợp geocoding | Kiểm thử transaction và file rollback |
| **TV4** | `reviews`, `review_statuses`, `place_statuses` | `SAVE_OR_UPDATE_REVIEW`, `SET_REVIEW_STATUS`, `SET_PLACE_STATUS` | Đánh giá, nơi đã bình luận, **toàn bộ Admin**: dashboard, categories CRUD, moderation | Tự làm module Admin; cả nhóm chỉ test hỗ trợ |

**Chống chồng chéo:** Chủ bảng chịu trách nhiệm schema/routine chính, nhưng thành viên khác có thể đọc bảng; mọi thay đổi hợp đồng cột/route phải qua Issue + PR. Tên routine logic sẽ được gắn prefix theo tên viết tắt thật khi tạo SQL.

## B. Kế hoạch 3 tuần (ngày tính từ ngày bắt đầu nhóm xác nhận)

Gate cập nhật: A01 → A02 schema trên MariaDB 10.4.32 → báo cáo/review → A03 seed/12 CALL → core/Auth và nghiệp vụ. Lịch task chi tiết mới tại `10_ARCHITECTURE_AND_IMPLEMENTATION.md` được ưu tiên khi bảng mốc bên dưới gộp nhiều task. A02 đã pass local; PR/CI remote chưa xác nhận.

| Mốc | Khang | TV2 | TV3 | TV4 | Kết quả chung |
|---|---|---|---|---|---|
| **Ngày 1–3** | Repo, Router, PDO, Session, base layout | Danh mục/tỉnh/phường/tags, phần tìm kiếm | Place/Images/Tags model + form cơ bản | Review và status model + giao diện admin khung | Chốt schema 13 bảng; seed/routine kiểm tra import; PR đầu |
| **Ngày 4–7** | Auth và phân quyền, DB setup | Trang chủ + danh sách + lọc ban đầu | Form địa điểm và upload 1–3 ảnh | Review CRUD logic và danh sách Admin | Đăng nhập được, DB chung có seed, main chạy được |
| **Ngày 8–10** | Hồ sơ + Favorites | Search/pagination, AJAX tỉnh/phường | Submit/Resubmit, transaction | Duyệt/từ chối/ẩn/khôi phục Place | Luồng đăng bài → duyệt → xem công khai |
| **Ngày 11–14** | Review PR/tích hợp + kiểm tra role | Tìm kiếm AJAX và responsive | Bản đồ, geocode, tags, chi tiết | Đánh giá AJAX, xem đã bình luận, danh mục Admin | Luồng đánh giá + yêu thích; API ngoài có fallback |
| **Ngày 15–17** | Tích hợp toàn hệ thống, debug quyền | Responsive và tối ưu tìm kiếm | Kiểm thử ảnh/API và sửa lỗi | Dashboard và test quản trị | Hoàn thành chức năng, sẵn sàng đóng băng |
| **Ngày 18–21** | Test, hỗ trợ đóng gói/demo/báo cáo | Test + ảnh giao diện | Test + hỗ trợ demo | Test + ảnh Admin/báo cáo | **Không thêm chức năng mới**, fix bugs, test, bản local + hosting nếu hỗ trợ |

Nên tích hợp PR nhỏ liên tục; **không đợi** TV4 hoàn thành mọi màn hình Admin mới ghép code.

## C. Phụ thuộc và bàn giao module

1. **Schema + seed cơ bản và quy ước route/model trước** để cả 4 người không gọi tên cột khác nhau.
2. **Router + PDO + Auth/Session** cung cấp cho đăng/sửa, đánh giá, yêu thích, kiểm duyệt. Trước khi Auth hoàn chỉnh, dùng tài khoản test hợp lệ trong DB nhưng không bypass kiểm tra quyền khi merge.
3. TV2 và TV3 cần thống nhất `SEARCH_APPROVED_PLACES` với field `places`/`place_images` và tỉnh/phường trước khi làm UI tìm kiếm.
4. TV3 tạo dữ liệu Place `PENDING` để TV4 test quy trình duyệt. TV4 cung cấp `SET_PLACE_STATUS` sớm để TV3 test hiển thị công khai.
5. TV4 viết review flow song song nhưng chỉ hiển thị public khi Place `APPROVED`.
6. Khang kiểm tra luồng end-to-end mỗi khi một module đạt acceptance; không sửa hộ nghiệp vụ khác trừ khi chủ module chấp thuận qua PR.

## D. GitHub Workflow

- Branch: `feature/auth`, `feature/favorites`, `feature/place-search`, `feature/place-submit`, `feature/admin-moderation`, `fix/...`.
- Workflow: tạo Issue → tạo branch từ `main` → commit rõ ràng → PR → ít nhất một người khác review → chạy checklist → merge; chặn push thẳng vào `main` nếu tài khoản GitHub cho phép thiết lập.
- PR mô tả: **Mục tiêu / Route và màn hình / SQL/schema thay đổi / Test thủ công / Ảnh bằng chứng / Tác động thành viên khác**.
- Trạng thái GitHub Projects: `Todo` → `In Progress` → `In Review` → `Done`; trao đổi, họp ngắn và xử lý blockers qua Teams.
- Quy ước commit: `feat: ...`, `fix: ...`, `docs: ...`, `db: ...`, `test: ...`. Không commit mật khẩu hoặc dump dữ liệu nhạy cảm.

## E. Shared MariaDB — quy định bắt buộc

- Cả nhóm làm trực tiếp **một shared MariaDB** (không thay đổi quyết định sang DB local). Tạo tài khoản truy cập có quyền tối thiểu phù hợp, không phát `root`/admin DB rộng rãi.
- Không tự ý thay schema qua phpMyAdmin rồi quên file SQL. Mọi thay đổi qua SQL được version trong Git, review và áp dụng vào DB theo một người điều phối.
- **Backup** trước thay đổi cấu trúc; chia tài khoản/dữ liệu test bằng tiền tố hoặc owner ID; không xóa dữ liệu test của người khác vô cớ.
- Seed cần idempotent hoặc hướng dẫn chạy một lần; không xóa/truncate production-like demo data khi thành viên khác đang kiểm thử.
- Tự kiểm tra các stored procedure theo thứ tự, chia prefix không trùng. Cấu hình DB kết nối chỉ nằm ở máy người sử dụng/secret host, không nằm trong Git.
- Nếu schema hỏng: ngừng merge, rollback migration/khôi phục backup, thông báo Teams, ghi Issue xử lý.

## F. Kiểm tra hoàn thành theo người

| Người | Điều kiện đóng task quan trọng |
|---|---|
| Khang | Auth đăng nhập đúng/sai, phân quyền 401/403, Favorites idempotent, core MVC chạy từ root public, PR tích hợp không vỡ |
| TV2 | Tìm kiếm keyword/category/province/ward/pagination, API JSON, danh mục disable không ẩn bài cũ, phường đúng tỉnh |
| TV3 | 1–3 ảnh bắt buộc, submit/resubmit đúng status và owner, 0–5 tag, Nominatim fallback, lỗi ảnh rollback DB/dọn file |
| TV4 | Review 1 user/1 Place + HIDDEN edit, Admin duyệt/từ chối có lý do, ẩn/khôi phục, danh mục CRUD, không cho Member gọi Admin |

## G. Rủi ro dự án

- **TV4 quá tải:** đã được nhóm trưởng chấp nhận; biện pháp là ưu tiên duyệt bài + reviews trước, dashboard giữ mức cơ bản; người khác chỉ test.
- **DB chung bị xung đột:** dùng review SQL, backup và seed có kiểm soát.
- **Hosting miễn phí không đủ quyền upload/SSL/MariaDB:** đánh giá khả năng host trước ngày 15; local demo vẫn phải ổn định.
- **Nominatim rate-limit/không có kết quả:** cache, kiểm soát lưu lượng, marker thủ công, không làm hỏng chức năng gửi Place khi không có tọa độ.
