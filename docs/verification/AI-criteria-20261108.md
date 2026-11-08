# Kiểm thử tiêu chí AI mở rộng — 08/11/2026

## Đã thêm

- Xếp hạng điểm trung bình cao nhất; nhiều review công khai nhất; nhiều lượt lưu nhất.
- Ngày đăng mới nhất/cũ nhất và ngày cập nhật gần nhất.
- Mức sao, số review, số lượt lưu tối thiểu.
- Nhiều nhãn AND, kết hợp danh mục/tỉnh/từ khóa tên–mô tả–địa chỉ.
- Bài đăng trong N ngày, có tọa độ, top 1–12.
- Card và mô tả bộ lọc hiển thị điểm/count/sort để kiểm tra được kết quả.

Không thay schema, seed nhóm hoặc routine; Model riêng của KH giữ SQL và allowlist ORDER BY. Mọi giá trị dữ liệu bind PDO. Chỉ lấy APPROVED; chỉ tính VISIBLE vào điểm/count; aggregate review/favorite riêng để không nhân bản số lượng. Có tie-break ổn định. Total count được tính trước LIMIT. Tìm kiếm không có kết quả không tự bỏ điều kiện.

## Kiểm tra

Dùng snapshot hiện tại và database tạm riêng. Các fixture 1–3 có điểm/lượt review/favorite/ngày/nhãn/tọa độ biết trước; fixture điểm cao nhưng PENDING để thử không lộ. Không thay dữ liệu local của nhóm.

- 49 ca SQL/validation đạt: thứ tự 7 chế độ, điểm 4.5/5, hidden review không tính, review/favorite tối thiểu, ngày gần đây, tọa độ, AND hai nhãn, top1 vẫn giữ full count, nhiều điều kiện, empty state, wildcard/SQL literal, unsupported-only clarification, ranking-only, schema/type/range/catalog sai và fenced JSON.
- 4 ca cache đạt: round trip, tách người dùng, bỏ cache có catalog cũ, không đọc schema v1.
- 8 ca HTTP đạt: GET 405, Guest 401, CSRF 403, array input 422, cached 200, filter/limit giữ đúng, điểm/count và thứ tự card đúng.
- Browser desktop/mobile: API cached, sort label, 3 card đúng top3, có metadata rating/count, không báo unsupported cho thứ tự đã hỗ trợ, không tràn và dialog vừa màn hình.
- 9 cách hỏi khác nhau đã gọi Claude thật và đạt. Kiểm thêm lại 3 câu xếp hạng sau khi làm rõ prompt không tự bịa mức tối thiểu. Vẫn dùng giới hạn đầu ra 250 token; không tăng timeout/cooldown.

## Các câu đã thử với Claude thật

1. quán ăn đánh giá cao nhất đà nẵng
2. Top 3 quán cà phê được nhiều người lưu nhất ở Hà Nội
3. Địa điểm ở Đà Nẵng từ 4,5 sao, ít nhất 3 đánh giá
4. Cà phê yên tĩnh, đi nhóm tại Đà Nẵng
5. 12 địa điểm mới đăng trong 7 ngày qua, có tọa độ bản đồ
6. chỗ nhiều đánh giá nhất ở Huế
7. quán ăn gần tôi, dưới 100 nghìn, đang mở cửa — nhận diện Ăn uống, báo 3 phần chưa hỗ trợ, không bịa dữ liệu giá/giờ/khoảng cách
8. 5 địa điểm đăng lâu nhất tại Lâm Đồng
9. Top 5 địa điểm vừa cập nhật ở Đà Nẵng

Các kết quả này là mẫu kiểm tra, không khẳng định AI sẽ luôn hiểu đúng mọi cách diễn đạt. UI luôn hiển thị bộ lọc và phần unsupported để người dùng đối chiếu.

## Giới hạn

Chỉ dữ liệu Hot Spot đã được duyệt; không phải xếp hạng Google Maps hoặc tìm quán trên Internet. Seed review hiện là đánh giá giả lập đã ghi nhãn phục vụ đồ án; điểm thống kê không phải đánh giá thực tế bên ngoài. Không xử lý giá/giờ mở cửa/vị trí người dùng bằng dữ liệu giả. Nhiều nhãn là AND, chưa hỗ trợ OR.

Chưa commit/push/deploy. Minh chứng local: D:/UEH/LTWEB/hotspot-mission-control/.sites-local/ai-criteria-20261108/criteria-results.json, cache-results.json, http-results.json, live-criteria-results.json, live-updated-results.json, ranking-prompt-results.json, ui-results.json và criteria-desktop.png/criteria-mobile.png.
