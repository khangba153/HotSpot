# Ảnh và mô tả danh mục

1. Import `20261108_category_images.sql` vào database Hot Spot đang dùng (MariaDB local/alwaysdata). Migration chạy lại được; không xóa dữ liệu địa điểm và giữ mô tả đã có.
2. Đồng bộ mã nguồn và thư mục `public/assets/images/categories`. Khi deploy, cần import SQL và upload ảnh cùng phiên bản trước khi mở bản code mới.
3. Quản trị → Quản lý danh mục → Sửa: nhập mô tả hiển thị Home, chọn đường dẫn ảnh hoặc tải ảnh PNG/JPG/WebP tối đa 2 MB; xem trước rồi Lưu thay đổi.
4. Để trống ảnh dùng poster Du lịch mặc định. Để trống mô tả thì Home không hiện mô tả. Khi sửa mà không chọn file mới, đường dẫn hiện tại được giữ.

Database lưu `categories.image_path` (đường dẫn/URL), `categories.description` (tối đa 300 ký tự). File tải lên nằm trong `public/uploads/categories`, không commit Git. Khi đổi hosting cần sao chép cả thư mục upload; không xóa thư mục này khi triển khai mã nguồn mới.

Routine `PTH_LIST_ACTIVE_CATEGORIES()` vẫn cùng tên/tham số, trả thêm description và image_path. Tổng số routine không đổi. Không thay đổi phân công hoặc 32 task.

Bộ poster được tạo bằng built-in image_gen: minh họa poster ngang 3:2 cùng nền kem, cam san hô và nét xanh đậm; chủ thể lần lượt phở, cà phê phin, vali/bản đồ, núi/sông, kiến trúc/hoa sen, vòng quay giải trí. Prompt đầy đủ: public/assets/images/categories/generation-prompts.json. Bản PNG gốc lưu trên máy tại C:/Users/Admin/Downloads/HotSpot_Category_Posters_20261108; bản JPG tối ưu dùng trên web trong thư mục public/assets/images/categories.

Ảnh thật của địa điểm không thay đổi; poster chỉ dùng cho danh mục.
