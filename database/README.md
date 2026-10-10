# Database A03 — MariaDB 10.4.32 (XAMPP)

13 bảng giữ nguyên từ A02; 12 stored procedure, 3/người. PDO dùng `pdo_mysql`. Không import vào `db_hospot`, không tự sửa shared database.

## Import an toàn

Khuyến nghị chạy `php tests/routines_mariadb.php`: xác minh version, tạo database `hotspot_test_a03_<UTC>_<random>` mới, import `schema.sql` → `seed.sql` → `seed_admin_full.sql` → `routines.sql`. Fixture test rollback; seed đã commit và database được giữ lại để nhóm trưởng kiểm tra. Tài khoản/tên DB lấy từ biến môi trường giống A02; chỉ chấp nhận local/CI, không remote shared host.

Import thủ công chỉ trên database kiểm thử mới/rỗng. Dùng bản gộp `routines.sql` **hoặc** bốn file member, không import cả hai. Mọi routine file đặt strict SQL mode và `SQL SECURITY INVOKER`; không chứa DROP/COMMIT/ROLLBACK. Không hardcode DEFINER hay credential. Routine cần được tạo bởi tài khoản có CREATE ROUTINE; khi gọi INVOKER, tài khoản DB cần EXECUTE và quyền tối thiểu SELECT/INSERT/UPDATE/DELETE tương ứng. Grant cụ thể cho shared/app sẽ làm ở bước thiết lập quyền, chưa được thử với tài khoản hạn chế ở A03.

## Mapping giữ nguyên

| Người | Tên SQL |
|---|---|
| Khang | KH_CREATE_USER, KH_GET_USER_BY_EMAIL, KH_SET_FAVORITE |
| TV2 | TV2_LIST_ACTIVE_CATEGORIES, TV2_SEARCH_APPROVED_PLACES, TV2_LIST_WARDS_BY_PROVINCE |
| TV3 | TV3_SUBMIT_PLACE, TV3_ADD_PLACE_IMAGE, TV3_RESUBMIT_PLACE |
| TV4 | TV4_SAVE_OR_UPDATE_REVIEW, TV4_SET_REVIEW_STATUS, TV4_SET_PLACE_STATUS |

TV2–TV4 vẫn là prefix tạm theo foundation, chưa có tên viết tắt thật; thay đồng bộ SQL/PHP/tests sau khi nhóm cung cấp.

## Hợp đồng transaction/PDO

**8 routine ghi yêu cầu caller mở transaction** (`@@in_transaction=1`); gọi ngoài transaction trả SQLSTATE 45000. Caller quyết định commit/rollback, routine không tự kết thúc transaction. Đây là kiểm tra giao thức kỹ thuật để các khóa FOR UPDATE có hiệu lực xuyên chuỗi submit/images/tags/moderation, không thay đổi nghiệp vụ.

Model A04 trở đi phải bắt mọi lỗi và rollback **toàn bộ** chuỗi, đồng thời dọn file upload; lỗi statement trong MariaDB không tự rollback mọi statement đã thành công trước đó. Thứ tự khóa parent place trước khi thêm ảnh/review/favorite, resubmit và moderation; deadlock/lock timeout cần rollback và phản hồi phù hợp ở PHP. A03 có test lock timeout 1205 giữa hai connection, chưa có stress test trên shared DB.

SEARCH trả hai result set dữ liệu: trang (có thể rỗng) và `total_count`. PDO còn có gói kết thúc CALL; trên PHP hiện tại `columnCount()` có thể giữ metadata cũ, cần kiểm tra `getColumnMeta(0)`, drain bằng `nextRowset()` và `closeCursor()` cả khi lỗi. Helper trong `tests/support/mariadb.php` là bằng chứng test, không phải core MVC mới.

Actor ID phải lấy từ Session phía PHP. Routine kiểm user tồn tại, owner hoặc role ADMIN, nhưng không thể biết người gọi HTTP là ai khi tất cả dùng cùng DB account. Không coi truyền một admin_id là cơ chế xác thực.

## Seed hành chính và demo

Snapshot JSON offline ghim commit nguồn và SHA256 ở `sources/manifest.json`; không tải dữ liệu biến động trong CI. Sinh lại bằng `php tools/generate_admin_seed.php` (cần intl/mbstring). Có 34 tỉnh, 3.321 xã/phường; bảo toàn mã chuỗi, kiểm unique/reference/count, normalize NFC và prefix loại đơn vị. Hoàng Sa mã 20333 là DAC_KHU. Nguồn cộng đồng và [Quyết định 19/2025/QĐ-TTg](https://vanban.chinhphu.vn/?classid=1&docid=214409&orggroupid=3&pageid=27160) được ghi rõ; chưa đối chiếu từng dòng với 143 trang phụ lục scan, không tuyên bố danh mục hiện hành năm 2026 đã xác minh toàn bộ.

Demo giữ 24 địa điểm giả lập, 3 tài khoản bcrypt, 6 danh mục, 8 tags, 24 ảnh, 24 liên kết tag, 18 review, 18 favorites. Không dữ liệu người dùng thật. Ảnh chỉ là path placeholder theo v3; file ảnh chưa có trong repo và A03 không dựng giao diện/asset mới.

## Kiểm thử

```powershell
python -X utf8 tests/admin_seed_generator.py
php tests/routines_mariadb.php
php tests/schema_mariadb.php
```

Xem `docs/reports/A03_MARIADB.md` để biết kết quả thực tế. Báo cáo JSON trong `tests/results/` không commit. Không đánh dấu PR/CI hoàn thành chỉ vì local pass.
