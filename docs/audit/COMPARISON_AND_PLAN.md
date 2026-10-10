# Hot Spot — Đối chiếu DB nháp và kế hoạch nền tảng v3

> Đây là baseline trước A01/A02; các phát hiện source rỗng mô tả trạng thái trước thay đổi. Trạng thái mới ở `../reports/A01_A02_MARIADB.md`.

Ngày: **10/10/2026, Asia/Saigon**. Repository: `D:\UEH\Dự án\HotSpot\HotSpot`. Branch: `docs/audit-current-environment`.

Người dùng xác nhận `db_hospot` là DB nháp, **chỉ đối chiếu**. Toàn bộ thao tác DB lượt này là SELECT metadata/count. Không CALL, import hoặc thay đổi dữ liệu/cấu trúc. Quyết định khóa của v3 vẫn là yêu cầu chính thức.

## 1. Kết luận hiện trạng

- Source trên ổ D là skeleton: 4 Controller, 4 Model và auth helper có phương thức rỗng; front controller/config/layout/schema/seed rỗng. Chưa có Router/PDO/Session/CSRF hoạt động.
- DB nháp dùng **MariaDB 10.4.32**, có **10 bảng / 12 procedure**; **tất cả 10 bảng đều 0 dòng** tại thời điểm kiểm tra. Không cần chuyển đổi DB nháp để làm đối chiếu.
- Bộ v3 trong Downloads có nền tảng có thể tái sử dụng. Lỗi MySQL3823 của schema v3 đã tái hiện trên MySQL8.0.46 cô lập, không phải kết quả chạy trên MariaDB nháp.
- Chưa sẵn sàng tích hợp nghiệp vụ song song. Ưu tiên schema/routine tests và core/Auth trước module.

Quyết định khóa cập nhật 10/10/2026: MariaDB 10.4.32 (XAMPP) là engine nghiệm thu chính thức, thay MySQL 8. Kết quả MySQL trước đây chỉ là lịch sử audit. DB cô lập mới dùng kiểm thử, không thay quy trình shared DB; db_hospot giữ nguyên để đối chiếu. Xem báo cáo A01/A02 để biết trạng thái mới.

## 2. Kiểm tra và bằng chứng

| Lệnh/kiểm tra | Kết quả thực tế | Giới hạn |
|---|---|---|
| `python -X utf8 docs/audit/read_draft_db.py` | Thành công, đủ metadata/body/parameters của 12 procedure;10 bảng đều trống | Chỉ SELECT, không xuất bản ghi/hash mật khẩu |
| `python -X utf8 docs/audit/check_current_source.py` | Lint **13/13 PHP pass**; trang chủ **FAIL: HTTP200, body0 bytes** | Cú pháp pass không chứng minh skeleton hoạt động |
| README/source/SQL/setup/ignore/sơ đồ PNG | Đã đọc/xem | Không có tools/tests/CI nền tảng như bộ v3 |
| Git | Có remote HotSpot, branch tài liệu riêng | Chưa commit/push/PR, chưa xác minh branch protection/Actions |

Bằng chứng: [metadata DB](draft-db-metadata.json), [kết quả source](current-source-checks.json), [inventory/checksum](source-inventory.json). Hai script trong thư mục này là công cụ audit, không phải module ứng dụng. Snapshot metadata **không phải backup phục hồi DB**.

## 3. Bảng dữ liệu: nháp → v3

| Bảng nháp | Khác biệt so với v3 | Đích khi triển khai DB chính thức |
|---|---|---|
| roles | Có PK nhưng không có MEMBER/ADMIN seed | Giữ bảng, seed role |
| users | Tên100/email150; role NOT NULL không default; timestamps nullable/no default | Tên120/email254; MEMBER mặc định; timestamps; giữ UNIQUE email |
| categories | PK/UNIQUE name/active; thiếu CHECK tên | Giữ active; ngừng category không ẩn bài APPROVED cũ |
| districts | District/city và ID số; trái địa giới hai cấp | Không dùng trong DB chính thức; provinces + wards mã chuỗi, ward tùy chọn/FK đúng tỉnh |
| place_statuses | is_public default1, chưa seed | PENDING/APPROVED/REJECTED/HIDDEN; chỉ APPROVED công khai |
| places | District bắt buộc; description/address nullable; thiếu reason và CHECK nội dung/geo | Province/ward, PENDING default, required fields, reason và CHECK geo pair/range |
| place_images | Không UNIQUE(place,sort), không CHECK sort; path255 nhưng procedure nhận500 | UNIQUE + CHECK sort1..3; path500; tối thiểu1ảnh/ảnh thật do transaction PHP |
| review_statuses | is_public nullable/default1; chưa seed | VISIBLE/HIDDEN và flag rõ ràng |
| reviews | Không UNIQUE(user,place); rating không CHECK; content/timestamps nullable | UNIQUE/upsert, rating1..5, comment có nghĩa, timestamps/status defaults |
| favorites | PK(user,place) và FK đúng ý | Giữ; desired boolean, approved-only add/list |
| Chưa có tags | Thiếu nhãn seed/active/slug | Tạo theo v3, TV2 |
| Chưa có place_tags | Thiếu quan hệ M:N | Tạo PK(place,tag), 2FK; max5 do Model, TV3 |

Số bảng mục tiêu: **10 − districts + provinces + wards + tags + place_tags = 13**. Đây là mapping thiết kế, chưa phải migration DB nháp.

Tất cả bảng nháp InnoDB/utf8mb4_general_ci;11FK đều UPDATE/DELETE RESTRICT. Không có CHECK; không UNIQUE review/image như v3. Index chủ yếu PK/FK, thiếu index search/owner/review status dự kiến. SQL_MODE của server và routine không có STRICT_TRANS_TABLES; cần kiểm thử input dài/ép kiểu trên engine nghiệm thu, không dựa vào tự truncate. Timezone SYSTEM, chưa chứng minh UTC thống nhất.

## 4. Đối chiếu đủ 12 procedure

Các prefix KH/TV2/TV3/TV4 là baseline v3; không đổi prefix trong DB nháp. Tên thực tế phải được mapping đồng bộ với PHP/test trước DB chính thức.

| Procedure nháp | Phát hiện qua đọc code | Đích logic/chủ trì v3 |
|---|---|---|
| BTCK_CREATE_USER | INSERT không role_code dù cột bắt buộc/no default; email input255/bảng150; chưa lowercase | CREATE_USER — Khang; MEMBER cố định, độ dài/normalize/UNIQUE đúng |
| BTCK_GET_USER_BY_EMAIL | Trả hash đúng mục đích; email input trực tiếp | GET_USER_BY_EMAIL — Khang; trim/lowercase |
| BTCK_UPDATE_USER_PROFILE | Cập nhật theo ID, không xử lý user không tồn tại/timestamps | Chuyển sang SQL PDO trong User, không giữ SP riêng trong bộ12 |
| CT_LIST_ACTIVE_CATEGORIES | Active/order đúng ý | LIST_ACTIVE_CATEGORIES — TV2 |
| CT_SEARCH_APPROVED_PLACES | Lọc category active làm ẩn bài cũ; district; không page/count/tag/cover/rating | SEARCH_APPROVED_PLACES — TV2; APPROVED-only, category cũ vẫn public, province/ward/tag/page,2rowsets |
| CT_UPDATE_PLACE_STATUS | Check admin/place/status; cho đổi bất kỳ status hợp lệ, thiếu matrix/reason/ảnh/concurrency guard | SET_PLACE_STATUS — **TV4**; không REJECTED→APPROVED trực tiếp; reason bắt buộc; hidden restore |
| NDHT_HIDE_REVIEW | Có admin check; không restore/not-found guard | SET_REVIEW_STATUS — **TV4**; HIDDEN/VISIBLE |
| NDHT_LIST_MY_REVIEWED_PLACES | JOIN theo user có thể tái sử dụng | SQL PDO trong Review — **TV4**, không SP riêng |
| NDHT_SAVE_OR_UPDATE_REVIEW | Rating/approved-only đúng; UPDATE giữ HIDDEN đúng; không validate comment; EXISTS→INSERT không UNIQUE | SAVE_OR_UPDATE_REVIEW — **TV4**; UNIQUE/upsert/comment có nghĩa |
| NPT_ADD_PLACE_IMAGE | Check sort/slot bằng SELECT; thiếu owner/status/path và UNIQUE | ADD_PLACE_IMAGE — TV3; owner/status/constraint DB, file/MIME do PHP |
| NPT_SET_FAVORITE | ADD/REMOVE, approved add và PK ghép tốt; EXISTS→INSERT có thể duplicate error đồng thời | SET_FAVORITE — **Khang**; desired boolean/idempotent |
| NPT_SUBMIT_PLACE | PENDING đúng; description chỉ NOT NULL; thiếu active category/geo/ward và transaction ảnh/tag | SUBMIT_PLACE — TV3; province/ward và transaction PHP |
| Chưa có | Lookup wards theo province | LIST_WARDS_BY_PROVINCE — TV2 |
| Chưa có | Sửa/gửi lại PENDING/REJECTED, xóa reason | RESUBMIT_PLACE — TV3 |

Giữ đúng 12: Khang CREATE_USER/GET_USER_BY_EMAIL/SET_FAVORITE; TV2 LIST_ACTIVE_CATEGORIES/SEARCH_APPROVED_PLACES/LIST_WARDS_BY_PROVINCE; TV3 SUBMIT_PLACE/ADD_PLACE_IMAGE/RESUBMIT_PLACE; TV4 SAVE_OR_UPDATE_REVIEW/SET_REVIEW_STATUS/SET_PLACE_STATUS. Profile/history cũ dùng query thường, không tăng lên14SP.

Procedure nháp đều SECURITY DEFINER, không tự COMMIT/ROLLBACK. Actor phải lấy từ PHP Session, không tin admin_id/user_id client tùy ý. Các race nêu trên là rủi ro suy ra từ code/constraint, **chưa tái hiện concurrent test**. Không ghi CALL pass/fail vì lượt này không CALL.

## 5. Source/sơ đồ và ưu tiên

Sơ đồ PNG còn Place.imageUrl, Category.description, User.isActive; thiếu địa giới/tags/favorite/PlaceImage riêng theo v3. Admin trong hình có category/moderation nhưng source có manageUsers/deleteUser ngoài scope khóa. README nói SQL Server, Review TV3/map tùy chọn — trái v3. Lượt này chỉ ghi sai khác; chưa sửa README thành trạng thái chưa được triển khai.

`database.php` được Git theo dõi nhưng rỗng; `.gitignore` rỗng: phải bảo vệ trước khi điền secret. `ProductService.php` rỗng, ngoài kiến trúc v3, không phát triển service layer này. Schema/seed local rỗng nên DB hiện tại không thể tái tạo từ repo.

**P0:** config/Git an toàn, đưa baseline vào repo đúng cách, schema13 bảng import thực tế. **P1:**12routine assertions/rollback, input scalar/JSON, password72byte, resubmit category inactive, Auth/session/next/quyền. **P2:**timezone/TLS, CI/encoding, docs/sơ đồ, UI/AJAX và deploy.

## 6. Task nhỏ, file, phụ thuộc và điều kiện hoàn thành

Đây là kế hoạch, chưa áp dụng source/SQL. Mỗi task một Issue/branch/PR với ít nhất1reviewer khác; sửa module liên quan, tái sử dụng v3, không copy đè toàn repository. DB nháp giữ nguyên.

| Task/chủ trì | Phụ thuộc | File tạo/sửa/tái sử dụng | Điều kiện hoàn thành |
|---|---|---|---|
| A01 config/Git — Khang | Xác nhận triển khai | database.example.php, .gitignore; bỏ theo dõi database.php nhưng giữ local | Secret ngoài Git; mẫu không password thật; branch/PR riêng |
| A02 schema — TV3 + TV4, Khang tích hợp | A01 | Tái sử dụng/sửa schema v3, test import, database/README | MariaDB 10.4.32 đủ 13 bảng; CHECK reason/FK/geo/UNIQUE thực sự enforce; không bỏ invariant để import |
| A03 seed/12 SP/tests — mỗi người 3 SP | A02 | Seed/4 routine và bản gộp v3; smoke assertions; TV2 kiểm nguồn hành chính | 12 CALL positive/negative/rollback; seed 34 tỉnh/3321 xã/24 bài; SP không commit; bản gộp khớp |
| A04 core MVC — Khang | A01, A03 cho DB | Autoload/bootstrap/core/helpers/routes/front controller/layout v3 | 404/405/CSRF/JSON/exception; array input không warning; SQL chỉ trong Model |
| A05 Auth/Profile — Khang | A03, A04 | User/Auth và views v3; sửa validation/session/next | Login đúng/sai, duplicate 409, tên ≤120 ký tự Unicode, byte guard bcrypt, PRG/next giữ id, logout/quyền |
| A06 lookup/Search — TV2 | A03–A05 | Category/Province/Ward/Tag Model; Place search/index; API location, search.js/wards | Hai rowsets/count đúng; category cũ inactive vẫn tìm được; filter/page/AJAX/mobile/error |
| A07 form/mine — TV3 | A05, lookup A06 | PlaceImage/PlaceTag; form chung; mine/create/edit | Owner từ Session; edit PENDING/REJECTED; validate 1–3 ảnh/0–5 tag/ward |
| A08 submit/upload — TV3 | A03, A07 | Place transaction, upload helper và rollback tests | MIME/size/ext/tên ngẫu nhiên/không thực thi; PENDING đúng; lỗi rollback + dọn file mới |
| A09 resubmit — TV3 | A08 | Cùng Place module/SP, tái sử dụng form | REJECTED→PENDING và xóa reason; giữ category cũ inactive; không sửa APPROVED/HIDDEN; giữ file cũ khi lỗi |
| A10 moderation — **TV4** | A05, fixture Place; A08 cho E2E | AdminController/views/PlaceStatus; gọi SP status | Member 403; matrix/reason/ảnh; admin quyết định cảnh báo trùng; không sửa nội dung người khác |
| A11 Review/history — **TV4** | A05, fixture APPROVED/A10 | Review/ReviewStatus/controller/view/JS; history SQL JOIN | UNIQUE/upsert/comment/rating; edit HIDDEN vẫn HIDDEN; approved-only; đúng user |
| A12 Favorites — Khang | A05, fixture APPROVED | Favorite Model/controller/view/JS | Desired boolean/idempotent; add/list chỉ APPROVED; bỏ favorite bài ẩn; Session/CSRF |
| A13 Admin category/review/dashboard — **TV4** | A10, A11, Category A06 | Admin CRUD active/category, hide/restore, counts/views | Không hard delete category; bài cũ public; restore review; dashboard cơ bản/mobile |
| A14 Map/detail — TV3 | A08, A09; phối hợp A11/A12 | Nominatim/cache/lock/config; Leaflet/map.js/detail/gallery | Toàn site ≤1 request/giây; cache/UA/attribution; không autocomplete; fallback/tọa độ NULL/marker/gallery |
| A15 CI/docs — Khang | Từ A01, hoàn thiện A03–A05 | Workflow/test v3 thêm DB service/assertions/encoding; README/docs/sơ đồ | Windows/Linux pass; không chỉ test 503; PR/reviewer; tài liệu đúng source |
| A16 E2E/concurrency/shared/demo — cả 4 theo module | A06–A15 | Tests/evidence; shared grants/TLS/backup/host probe | Main/shared DB hoạt động; 13 bảng/12 SP; máy khác/mobile; freeze ngày 18; URL hosting hoặc blocker |

Khang điều phối routes/layout/core/routine tổng hợp. TV2 phụ trách search/index, TV3 detail/contribution để giảm conflict Place.php. **TV4 tự làm toàn bộ Admin+Reviews**, kể cả category CRUD; TV2 sở hữu bảng category và hỗ trợ hợp đồng, không làm hộ Admin. Các thành viên khác chỉ test/review phần TV4.

Giữ source chưa thuộc task đang làm; giữ DB nháp; README/sơ đồ cũ là đối chiếu đến khi source thay đổi tương ứng. Đưa v3 vào ổ D theo từng task, không viết lại framework hoặc thêm service layer.

## 7. Kế hoạch 21 ngày

Ngày tính từ kickoff nhóm xác nhận, chưa đặt lịch tuyệt đối.

| Ngày | Công việc | Gate |
|---|---|---|
| 1–2 | Khang A01; TV3/TV4 A02; mỗi người chuẩn bị test 3 SP; TV2 seed | Schema 13 bảng import thật |
| 3–4 | A03; Khang A04/A05; TV2 lookup; TV3 test category resubmit; TV4 test status | 12 CALL/rollback và core/Auth |
| 5–7 | Khang Git/shared/CI; TV2 A06; TV3 A07/A08; TV4 A10 qua PR nhỏ | Login→submit→approve ban đầu trên main/shared |
| 8–10 | Khang A12; TV2 AJAX; TV3 A09; TV4 A11 | Reject→resubmit; review/favorite success/error |
| 11–14 | TV3 A14; TV4 A13; Khang tích hợp; TV2 responsive; concurrent tests | Đủ MVP/API fallback |
| 15–17 | A16 E2E/security/mobile/máy khác/host probe, fix đúng module | Feature complete trước freeze |
| 18–21 | Chỉ bugfix/regression/evidence/backup/report/demo | Không feature mới; main/shared DB nghiệm thu |

Nếu A02/A03 chưa pass, module phụ thuộc DB bị chặn. TV4 có thể dùng approved fixture cô lập để test sớm, nhưng E2E phải qua submit thật. Không giảm test/số bảng/SP hoặc chuyển code TV4 để giữ deadline.

## 8. Bàn giao lượt đối chiếu

1. **Đã thực hiện:** đọc metadata/12 body/parameters; xem PNG; count 10 bảng; lint/HTTP; mapping và 16 task/21 ngày.
2. **File thay đổi:** chỉ docs/audit và bản đính chính; app/public/database/.gitignore chưa đổi.
3. **Kiểm thử:** đọc metadata pass, lint 13/13 pass; trang chủ 200/body 0; chưa CALL hoặc ghi DB nháp.
4. **Còn lại:** source skeleton; DB nháp khác v3; MariaDB là mục tiêu chính thức; trạng thái schema mới xem báo cáo A02; thiếu core/CI/shared setup.
5. **Tiếp theo:** A01 bảo vệ config/Git, A02 database cô lập. Chưa có code chức năng mới áp dụng trong lượt này.
