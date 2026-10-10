# Kiến trúc chính thức và kế hoạch thực thi

Browser → public/index.php → Router → Controller → Model → PDO (`pdo_mysql`) → **MariaDB 10.4.32 (XAMPP)**. View dùng Bootstrap; JS/AJAX nhận JSON thống nhất. Nominatim qua ứng dụng có cache/rate limit, bản đồ Leaflet/OpenStreetMap. PHP thuần OOP/MVC, không thêm framework/service layer. Source hiện vẫn là skeleton; mũi tên mô tả kiến trúc phải triển khai ở A04.

## Gate và task

| Task | Phụ thuộc | File tạo/sửa | Điều kiện hoàn thành |
|---|---|---|---|
| A01 config/Git | Quyết định nhóm | .gitignore, database.example.php, README/docs; untrack database.php | Config cá nhân giữ local, ngoài Git; không secret; feature branch/PR review |
| A02 schema/CI | A01 | database/schema.sql, tests/schema_mariadb.php, .github/workflows/mariadb.yml | MariaDB 10.4.32 import 13 bảng; PK/FK/UNIQUE/CHECK thực thi, negative/boundary/rollback pass; draft không đổi |
| A03 seed/12 SP | A02 báo cáo/review | database/seed.sql, routines từng người/bản gộp, tests routine/seed, tools seed hành chính | 12 routine đúng 3/người; CALL positive/negative/quyền/rollback; PDO đóng cursor; seed 34 tỉnh/3321 xã/24 Place được kiểm chứng; không COMMIT trong SP |
| A04 core | A01, A03 để nối DB | public/index.php, app/core, bootstrap/helpers/layout/routes/config | Router 404/405, PDO strict/UTC, Session/CSRF, exception/JSON; input array không warning |
| A05 Auth | A03/A04 | User, AuthController, auth/profile views | Auth/role/session regeneration, CSRF, validation, duplicate, redirect an toàn |

Giữ nguyên Controller/Model/View hiện có trong A01–A03. A03 đã bổ sung seed/12 routine và đạt 158/158 local, generator 17/17; xem báo cáo A03. Gate A04 chờ nhóm trưởng đánh giá. Các task A06–A15 và owner/điều kiện test xem `audit/COMPARISON_AND_PLAN.md`. CI A02 chưa thay thế CI/CALL A03 hoặc E2E.

## 21 ngày

- Ngày 1–2: A01/A02, bảo vệ config và kiểm thử schema thật; chuẩn bị hợp đồng và test cases 3 routine/người.
- Ngày 3–4: A03 (12 routine/seed), A04 core và A05 Auth sau gate DB; TV2 kiểm nguồn địa giới; TV3/TV4 kiểm quyền/trạng thái.
- Ngày 5–7: lookup/search, detail, submit/upload theo phân công; PR nhỏ, test độc lập.
- Ngày 8–10: submit → moderation → public, resubmit, rollback ảnh, hidden review invariant.
- Ngày 11–14: map/Nominatim, AJAX, Favorites, toàn bộ Admin/Review do TV4; tích hợp luồng thật.
- Ngày 15–17: E2E/security/responsive, seed demo thật, shared DB/hosting readiness.
- Ngày 18–21: freeze tính năng, sửa lỗi, regression/demo/báo cáo. Không giảm ràng buộc hoặc đổi owner để bù thời gian.

Shared DB vẫn là MariaDB dùng chung; test ghi dùng database mới. MySQL 8 chỉ là lịch sử audit, không là gate nghiệm thu. Các ngày là lịch tương đối từ ngày nhóm bắt đầu, không tự suy diễn deadline lịch.

## Cập nhật sau chấp thuận A03

A04+A05 core/Auth đơn giản đã chạy trên XAMPP PHP 8.0.30; không triển khai Profile trong task Auth hiện tại. Nền tảng và checklist thủ công ở 11_MVC_AUTH_HANDOFF.md. Ưu tiên mới của nhóm trưởng thay các yêu cầu mở rộng tests/CI/tuning trong kế hoạch cũ: chỉ kiểm luồng quan trọng và lỗi thực tế; không xây thêm tầng kiến trúc. Module tiếp theo giao theo owner, 21 ngày giữ nguyên.
