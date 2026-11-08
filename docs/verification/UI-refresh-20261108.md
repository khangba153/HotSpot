# UI/UX Hot Spot — chỉnh sửa và kiểm thử 08/11/2026

## Các thay đổi đã hoàn thành

- Menu dùng route hiện tại: Đăng địa điểm/tạo/sửa/địa điểm của tôi và Yêu thích có màu, gạch chân và aria-current đúng. Biểu tượng tìm kiếm ngoài Home dẫn về Home đúng anchor.
- Danh mục bỏ hiệu ứng grayscale/opacity; card danh mục là link lọc thật. Bỏ các ký hiệu trang trí không cần thiết.
- Home tối đa 12 card/trang: desktop 4 cột × 3 hàng; responsive 3/2/1 cột khi màn hình nhỏ hơn. Ảnh, chữ và vùng bấm lớn hơn; hàng nút Chi tiết được căn cùng đáy card. Bỏ trái tim giả trên card; yêu thích thật vẫn ở Detail/Favorites.
- Bỏ nút sắp xếp tĩnh, Xem tất cả tĩnh, đăng ký nhận tin và các mục footer chưa triển khai. Bỏ bộ lọc chỉ có một option và phân trang giả trên danh sách bài chờ duyệt; giữ tìm kiếm thực và bổ sung empty state/số lượng theo bộ lọc.
- Dropdown dùng select gốc để submit và validate, bổ sung danh sách có tìm kiếm, chuột/bàn phím, Enter/Esc, focus, trạng thái disabled và đồng bộ AJAX phường/xã. Không thêm framework/dependency.
- Admin dùng nền tối, chữ dễ đọc hơn, menu chung ở `_navigation.php`, vẫn có điều hướng trên mobile; bảng cuộn trong vùng riêng có hướng dẫn. Phân trang thật gọn thay vì tạo hàng chục nút. Khung Admin tối đa 1440 px; card đo ở 1440/1920/2560 px lần lượt 277/276.5/276.5 px.
- Admin được tạo/sửa bài của chính mình qua cùng form. Bài mới vẫn PENDING, owner từ session, kiểm CSRF/ảnh/nhãn/địa giới và quyền chủ bài được giữ nguyên. Không thay schema, routine, 32 task hoặc phân công.
- Mobile Login/Register/Forgot đưa form lên trước nội dung quảng bá; ô email xuất hiện trong khung đầu. Profile thu gọn sidebar, đưa thông tin cá nhân trước thống kê trên mobile.
- Radio số sao có tên 1–5 sao cho trình đọc màn hình.
- AI nhận JSON thuần hoặc một khối Markdown JSON bao toàn bộ phản hồi, sau đó vẫn kiểm schema/kiểu/catalog. Đã sửa lỗi 500 tái hiện ở lượt E2E trước.
- Thêm `run-local.ps1` và `public/.user.ini`: 5 MB/ảnh, 20 MB/POST. PHP CLI dùng tham số -d trong script; request quá lớn trả 413 rõ nghĩa, không báo nhầm CSRF. Hosting cần kiểm giới hạn thực tế khi deploy.

## Kiểm thử đã thực hiện

- **94 ca HTTP/database: 93 đạt; 1 bị chặn bởi DNS Nominatim**. 59 ca hồi quy luồng Auth/Profile/Place/Moderation/Favorite/Review; 25 ca category/validation/quyền/tìm kiếm/geocode; 10 ca quyền Admin/upload/413/chuẩn hóa JSON. 7 ca SMTP/reset của lượt E2E trước không chạy lại trong lượt này vì không sửa module đó; không gửi email ra ngoài.
- **64 lượt trang/kích thước, không tràn ngang**, HTTP 200 và không có JS page error: Member 6 route × 4 kích thước, Admin 6 route × 4 kích thước, Guest 4 route × 4 kích thước; 375/768/1440/2560 px. Kiểm thêm 3 kích thước desktop cho độ ổn định card Admin.
- 13 kiểm tra dropdown: mở, tìm, Enter, đổi tỉnh/ward, reset, clear search, disabled, empty search, Esc/focus, popup vừa mobile.
- 7 kiểm tra Admin bằng browser: lọc review thật, next-page, pagination gọn, lưu modal danh mục, menu active, đăng bằng form thật và điều hướng mobile.
- 13 kiểm tra UI công khai: card danh mục lọc thật, màu danh mục, empty state, count=0, reset 12 card/4 cột, không còn nút giả, favorite phản hồi thật, màu/gạch chân/aria-current và focus khi thiếu select.
- Browser kiểm AI summary, AI recommendations, modal Esc/focus; AI description không tự ghi đè và chỉ áp dụng khi bấm Dùng bản nháp. Cả **3 chức năng gọi Claude thật trả 200**, gọi lặp lấy cache và yêu cầu mới trong 60 giây trả 429.
- Upload 3 PNG hợp lệ, mỗi ảnh khoảng 3 MB: Admin gửi được, DB lưu 3 ảnh/PENDING/owner Admin. Guest vẫn 401, CSRF sai vẫn 403. Request 21 MB nhận 413 đúng nội dung.
- 7 mẫu phản hồi JSON được kiểm: JSON thuần/fenced hợp lệ; loại phần văn bản thừa, sai/mất trường và list thay object. Validation catalog vẫn được thực hiện sau chuẩn hóa.
- 12 ảnh card ở vùng đã cuộn vào đều tải thật; ảnh dưới khung dùng lazy loading. Không coi ảnh skeleton chụp ngay sau cuộn là ảnh hỏng.
- Kiểm cú pháp PHP/JS và diff whitespace trước bàn giao.

## Lỗi tương tự tìm thêm trong quá trình kiểm tra và đã sửa

1. Xóa tìm kiếm đặt native select về rỗng nhưng chữ trên dropdown tùy biến chưa đổi → thêm tín hiệu đồng bộ riêng, không gây thêm 3 request AJAX.
2. Focus vào select bắt buộc bị đưa về phần tử native đã ẩn → chuyển focus đến nút chọn đang nhìn thấy.
3. Menu dropdown có thể ra ngoài mép dưới màn hình → đo khoảng trống và mở lên trên khi cần.
4. Admin pagination tạo quá nhiều số trang, dễ giãn bố cục → chỉ hiện đầu/cuối/các trang gần hiện tại, có Trước/Sau.
5. Admin chờ duyệt tìm không thấy làm bảng trống không giải thích → thêm empty state và cập nhật số lượng, reset tìm kiếm khôi phục hàng.
6. Nút Chi tiết ở card mô tả ngắn/có địa chỉ một dòng lệch đáy → body card flex, nút nằm cùng đáy.
7. Sidebar Profile đã thu gọn nhưng thống kê vẫn đẩy form xuống sâu → thông tin cá nhân đứng trước thống kê trên mobile.

## Giới hạn còn lại

- Nominatim/tile OpenStreetMap vẫn lỗi DNS trong môi trường này; fallback và lưu không tọa độ vẫn chạy. Chưa đổi sang Google Maps vì chưa cấu hình Maps API và đây không phải thay đổi provider được nghiệm thu.
- Google Login ở port QA 8023 không có origin whitelist; không đánh giá lỗi này là hồi quy OAuth của port 8000/domain public. Chưa thực hiện đăng nhập Google thật trong lượt này.
- Chưa deploy. Website public cũ không tự nhận code/database/config local.
- Các ca thử dùng database/snapshot riêng; dữ liệu nhóm giữ nguyên. Không tự commit/push hoặc cập nhật webtask trong lượt sửa UI này.

## Vị trí mã

CSS tiếp tục chia theo module: `app.css`, `admin.css`, `auth.css`, `profile.css`; điều khiển dùng chung ở `interface.css`. Dropdown ở `select-control.js`, phân trang Admin ở `admin-pagination.js`. README và run-local.ps1 ghi cách chạy đủ giới hạn upload.

Minh chứng JSON và ảnh nằm tại `D:/UEH/LTWEB/hotspot-mission-control/.sites-local/ui-refresh-20261108/`: final-summary.json, responsive-ui.js.results.json, admin-layout.js.results.json, guest-responsive.js.results.json, select-interaction.js.results.json, admin-interaction.js.results.json, public-interaction.js.results.json, ai-results.json, changed-behavior-results.json, pending-empty.js.results.json. Ảnh: home-1440.png, cards-final-desktop.png, admin-1440.png, admin-reviews-375.png, login-mobile.png, profile-final-mobile.png.

## Chỉnh bổ sung thanh tìm kiếm và cooldown AI

Đã sửa nút Tìm kiếm bị vượt khung: thanh cao 56 px, nút cao 44 px, có đủ khoảng đệm. Đã đo ở 320/375/575/768/1024/1440/1920/2560 px; nút nằm trong thanh, nút AI nằm trong viewport và không tràn ngang.

Khoảng chờ yêu cầu AI cần gọi Claude giảm từ 60 xuống 10 giây cho cả ba chức năng; cache vẫn trả ngay. Kiểm 12 ca qua Controller thật với clock/dependency fixture, không gọi API ngoài: sau 9 giây nhận 429 kèm thông báo 10 giây; sau 10 và 11 giây nhận 200; có cache nhận 200 cả khi chưa đủ 10 giây. Các kết quả 60 giây ở phần nghiệm thu trước là cấu hình tại thời điểm kiểm tra ban đầu. Timeout HTTP tới Claude không thay đổi.

Minh chứng bổ sung: `D:/UEH/LTWEB/hotspot-mission-control/.sites-local/search-fix-20261108/layout-results.json` và `cooldown-results.json`.
