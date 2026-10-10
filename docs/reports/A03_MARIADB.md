# A03 — Seed và 12 stored procedure trên MariaDB

Ngày 10/10/2026, repo `D:\UEH\Dự án\HotSpot\HotSpot`. Nhóm trưởng đã chấp thuận A01/A02 local. Branch mới `feature/mariadb-a03-seed-routines` dựa trên A01/A02 `4070033`. Không thay đổi 13 bảng/schema, không triển khai MVC/Auth/Admin/UI và không thao tác ghi vào db_hospot.

## 1. Kết quả import thực tế

PHP CLI 8.4 + PDO pdo_mysql; MariaDB **10.4.32-MariaDB**, XAMPP 127.0.0.1:3306. Database mới cuối cùng: **`hotspot_test_a03_20261010_124156_3ea6f8a6`**, được giữ lại để xem qua phpMyAdmin. Thứ tự import: schema → seed demo → seed hành chính → bản gộp 12 routine.

| Bảng | Số dòng seed |
|---|---:|
| roles | 2 |
| users | 3 |
| categories | 6 |
| provinces | 34 |
| wards | 3.321 |
| place_statuses | 4 |
| places | 24 |
| place_images | 24 |
| tags | 8 |
| place_tags | 24 |
| review_statuses | 2 |
| reviews | 18 |
| favorites | 18 |
| **Tổng** | **3.488** |

Có 18 địa điểm APPROVED, 3 PENDING, 2 REJECTED, 1 HIDDEN. 3.321 cấp xã gồm **2.621 XA / 687 PHUONG / 13 DAC_KHU**, khớp số liệu tổng hợp trong [thông tin Chính phủ về sắp xếp 2025](https://baochinhphu.vn/quyet-dinh-nhieu-noi-dung-he-trong-de-trien-khai-to-chuc-chinh-quyen-dia-phuong-2-cap-1022506271457596.htm).

Snapshot nguồn cộng đồng ghim commit `0d36a6ef24cb59a3b4caec5b2f709dcb439a4827`, SHA256 `7315a8ecc579d5d706e7c4b8d6f59f6603932fd1fe8775114be091ca3d35b7b8`. Nguồn, ngày lấy, đối chiếu pháp lý và giới hạn ở `database/sources/manifest.json`. Mã chuỗi/0 đầu giữ nguyên, kiểm đủ counts/code set/unique/parent/type/name length. Normalize NFC và prefix giải quyết 4 tên Unicode tổ hợp cùng 1 prefix xã viết thường, đặc biệt Hoàng Sa 20333 phải là DAC_KHU. [Quyết định 19/2025/QĐ-TTg](https://vanban.chinhphu.vn/?classid=1&docid=214409&orggroupid=3&pageid=27160) là nguồn đối chiếu pháp lý; chưa đối chiếu từng dòng với 143 trang phụ lục scan, không tuyên bố danh mục hiện hành 2026 đã được xác minh toàn bộ.

Demo là dữ liệu giả lập; tài khoản chỉ chứa hash bcrypt, không thêm mật khẩu hoặc thông tin kết nối thật vào Git. Mỗi Place có 1 ảnh và 1 tag active trong seed; review/favorite trỏ APPROVED, FK đầy đủ, không trùng author/place, REJECTED có lý do. Ảnh còn là path placeholder của v3, chưa có file asset tương ứng; A03 không tạo giao diện mới.

## 2. 12 procedure và kết quả từng routine

Số PASS là số scenario; một scenario có thể chứa nhiều assertion/CALL. Mỗi routine có ca hợp lệ/không hợp lệ/boundary hoặc ranh giới quyền/trạng thái. TV2–TV4 giữ prefix tạm, không tự đặt tên thành viên.

| Người phụ trách | Procedure SQL | PASS | FAIL |
|---|---|---:|---:|
| Khang | `KH_CREATE_USER` | 11 | 0 |
| Khang | `KH_GET_USER_BY_EMAIL` | 6 | 0 |
| Khang | `KH_SET_FAVORITE` | 11 | 0 |
| TV2 | `TV2_LIST_ACTIVE_CATEGORIES` | 3 | 0 |
| TV2 | `TV2_SEARCH_APPROVED_PLACES` | 11 | 0 |
| TV2 | `TV2_LIST_WARDS_BY_PROVINCE` | 5 | 0 |
| TV3 | `TV3_SUBMIT_PLACE` | 14 | 0 |
| TV3 | `TV3_ADD_PLACE_IMAGE` | 12 | 0 |
| TV3 | `TV3_RESUBMIT_PLACE` | 11 | 0 |
| TV4 | `TV4_SAVE_OR_UPDATE_REVIEW` | 16 | 0 |
| TV4 | `TV4_SET_REVIEW_STATUS` | 9 | 0 |
| TV4 | `TV4_SET_PLACE_STATUS` | 16 | 0 |

Tổng **125 scenario routine pass**, cùng 33 kiểm tra import/seed/transaction/concurrency/safety = **158/158 PASS**, exit 0. Không có routine ngoài 12 đã chốt. Bản gộp khớp bốn file cá nhân sau chuẩn hóa newline Windows/Linux.

## 3. Kiểm thử đã chạy

| Lệnh | Kết quả thực tế |
|---|---|
| `php tests/routines_mariadb.php` lượt đầu | 147/150 pass; 3 fail ở parity newline và đếm result set PDO |
| `php tests/routines_mariadb.php` sau sửa + bổ sung boundary/status/count | **158/158 pass** trên DB mới nêu trên |
| `python -X utf8 tests/admin_seed_generator.py` | **17/17 pass**: hash, offline reproducibility/NFC/Hoàng Sa, negative count/duplicate/code/reference/type/length; lỗi không ghi đè SQL tốt |
| `php tests/schema_mariadb.php` regression | **64/64 pass**, DB `hotspot_test_a02_20261010_123702_d4dece6e` |
| PHP lint app/public/tests/tools | **17/17 pass** |
| Parse YAML workflow + `git diff --check` | Pass |
| GitHub credential/API identity + push dry-run | Tài khoản khangba153 hợp lệ, dry-run có quyền tạo feature branch; không push thật |

JSON chi tiết local: `tests/results/hotspot_test_a03_20261010_124156_3ea6f8a6.json`; báo cáo generator cùng thư mục. Tất cả JSON runtime bị ignore. Các database test trước được giữ lại, không DROP. Seed commit để đối chiếu; mọi scenario ghi được rollback. Digest **toàn bộ nội dung 13 bảng seed** trước/sau bằng nhau; fingerprint metadata/routine/count db_hospot trước/sau bằng nhau. Chưa chứng minh checksum từng bản ghi draft, nhưng draft đang trống theo audit và runner không có statement ghi vào đó.

Các ca chính: email UNIQUE/case/Unicode length; favorite desired 0/1/idempotent/non-public; search lọc/category inactive/count/empty page/clamp/INT boundary/injection text; ward inactive/mismatch; submit PENDING/geo/province/owner; ảnh slots 1–3/4th/duplicate/owner/state; resubmit REJECTED→PENDING/clear reason/giữ category cũ inactive; review 1/5/0/6/content 65535/65536 bytes/upsert/HIDDEN giữ nguyên; admin role/id/status/transition/reason; caller transaction/no COMMIT, duplicate error + rollback cả chuỗi.

Test hai connection xác nhận khóa parent Place ngăn moderation cạnh tranh với resubmit và trả lock timeout 1205 khi caller còn giữ transaction. Đây là kiểm chứng một đường cạnh tranh cụ thể, không là stress test toàn bộ shared DB.

PDO native prepared statements chạy tất cả 12 CALL. SEARCH có 2 result set dữ liệu và gói kết thúc CALL; trên driver máy này columnCount giữ metadata cũ ở gói cuối. Helper kiểm thêm getColumnMeta(0), drain nextRowset, closeCursor trong finally. Trang rỗng vẫn giữ result set count và gọi CALL/query tiếp theo không lỗi commands out of sync.

## 4. Sửa lỗi v3 và hợp đồng kỹ thuật

- Favorite chỉ nhận 0/1, không coi 2 là hành động bỏ yêu thích.
- Resubmit cho giữ nguyên category cũ đã inactive, vẫn chặn đổi sang category inactive khác; kiểm quyền/khóa parent trước update.
- Các trường bắt buộc ở routine không chấp nhận chỉ whitespace; status NULL/không hợp lệ bị reject rõ; status hợp lệ được uppercase/trim về mã canonical.
- Pagination tính offset ở BIGINT để tránh tràn INT; clamp giữ nguyên 1..30 và default 12.
- Parent row lock phối hợp image/review/favorite/resubmit/moderation. 8 routine ghi bắt buộc caller transaction; 4 routine đọc không yêu cầu. Không có COMMIT/ROLLBACK/START TRANSACTION bên trong routine.
- SECURITY INVOKER, không hardcode DEFINER; strict mode được capture lúc tạo cả 12 routine. Không sửa schema hoặc thêm routine/table/trigger để né nghiệp vụ.

## 5. Database bảo vệ gì, PHP còn phải bảo vệ gì

| Lớp | Quy tắc bảo vệ |
|---|---|
| Schema A02 | PK/FK/UNIQUE/CHECK, đúng province/ward, tọa độ ghép/range, rating 1..5, nội dung không chỉ whitespace, lý do REJECTED, tối đa 3 ảnh nhờ slot+UNIQUE; FK RESTRICT |
| 12 routine A03 | Actor tồn tại/owner/admin theo ID được truyền, category/location active, giữ category cũ inactive khi resubmit, PENDING mặc định, state transitions, approve/restore cần 1–3 ảnh, review upsert và hidden invariant, favorite public/idempotent, strict params và caller transaction |
| PHP bắt buộc từ A04 trở đi | Session xác định actor (không tin ID từ request), login/password_hash/verify và email format; CSRF/role tại endpoint; normalize/validate input; PDO transaction rollback toàn bộ chuỗi; tối thiểu 1 ảnh khi submit/resubmit hoàn tất; tối đa 5 tag active từ seed và đồng bộ ảnh/tag; upload MIME/size/ext/file safety/dọn file; lọc favorite/history/detail theo public/session; cảnh báo trùng; escape output; geocode/cache/rate limit |

Routine SQL không xác thực người dùng HTTP và không ngăn một tài khoản có quyền DML trực tiếp bỏ qua routine. App DB account phải quyền tối thiểu; quyền đó và core/HTTP chưa được dựng hoặc test trong A03. KH_CREATE_USER chỉ kiểm hash không rỗng, PHP chịu trách nhiệm tạo hash thực sự; không coi trường VARCHAR password_hash là chứng minh an toàn mật khẩu.

## 6. File thay đổi và trạng thái Git

- `database/seed.sql`: tái sử dụng demo v3, chỉ hash fixture; `seed_admin_full.sql` và `sources/{administrative-units-2025.json,manifest.json}`: seed offline có provenance.
- `database/routines_member1_khang.sql`, `routines_member2.sql`, `routines_member3.sql`, `routines_member4.sql`, `routines.sql`: 12 routine sửa nghiệp vụ và MariaDB.
- `tools/generate_admin_seed.php`: kiểm/normalize/sinh SQL; `tests/admin_seed_generator.py`, `tests/routines_mariadb.php`, `tests/support/mariadb.php`: generator/PDO runtime tests.
- `.gitattributes`: newline reproducible; `.github/workflows/mariadb.yml`: A02+A03/generator, MariaDB 10.4.32, intl/mbstring, artifact results.
- README, database/README, docs/setup/02/08/10, báo cáo này: trạng thái/hợp đồng/bằng chứng.
- Giữ nguyên `database/schema.sql`, source MVC/UI/Auth/Admin, config local và db_hospot.

Git Credential Manager đã có credential tài khoản khangba153; kiểm API identity thành công, đặt username trong **config Git local**, dry-run push thành công. Không token trong chat/file repo, không sửa URL remote để nhúng credential. Chưa push thật theo chỉ dẫn trước giữ local; chưa tạo Issue/PR, chưa có Actions remote/reviewer/merge. Không tuyên bố PR hoàn thành.

**A01–A02 đang chung branch** `feature/mariadb-foundation-a01-a02` với hai commit `7677c15` và `4070033`. Đề xuất review một PR nền tảng, xem riêng từng commit: config/secret+quyết định (A01), schema/constraints+tests (A02); reviewer khác xác nhận cả hai trước merge. A03 ở branch riêng dựa trên A01/A02: khi yêu cầu push có thể tạo PR stacked với base foundation branch để diff chỉ chứa A03, sau PR nền tảng merge thì retarget base main và kiểm CI lại. Hoặc chờ nền tảng merge rồi đưa commit A03 sang branch từ main. Không merge trực tiếp main và không gom A04 vào PR này.

## 7. Còn lại trước A04

- Nhóm trưởng đánh giá A03; chưa bắt đầu A04.
- GitHub push/PR/review/Actions chưa thực hiện; local pass không thay CI remote.
- Kiểm quyền DB tối thiểu và shared MariaDB từ máy 4 thành viên chưa làm; stress/deadlock retry chưa kiểm chứng.
- Prefix tên thật TV2–TV4 và đối chiếu pháp lý từng dòng hành chính còn chờ; asset demo placeholder chưa có.
- Core/Session/CSRF/Auth/HTTP và business transaction upload/tag tối thiểu ảnh vẫn cần A04+module sau; không tuyên bố site chạy từ DB pass.
