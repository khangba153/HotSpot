# Dải nhãn danh mục trên card địa điểm

`categories.card_label_image_path VARCHAR(500) NULL` lưu đường dẫn ảnh trang trí dưới ảnh địa điểm trên Home. Mỗi danh mục lưu một ảnh, tất cả địa điểm cùng danh mục sử dụng chung ảnh đó. Tên hiển thị lấy từ `categories.name`, không thêm bảng nhãn hoặc tên trùng.

- `image_path`: poster ở phần Danh mục phổ biến.
- `card_label_image_path`: ảnh dải nhãn trên card địa điểm.
- `description`: mô tả ngắn dưới poster danh mục.

## Cập nhật database và source

1. Sao lưu database. Chọn đúng database Hot Spot trong phpMyAdmin.
2. Nếu chưa có `image_path`, chạy `20261108_category_images.sql` trước.
3. Chạy `20261108_category_card_labels.sql` cùng thư mục này. File có thể chạy lại; chỉ điền ảnh mặc định khi trường nhãn trống, giữ dữ liệu các cột cũ, bao gồm `updated_at`.
4. Cập nhật source PHP/CSS/JS và thư mục `public/assets/images/categories/labels/` cùng lúc. Gói seed cũ vẫn cần chạy thêm SQL ở bước 3.
5. Trong Quản lý danh mục → Sửa: xem hai ảnh riêng, nhập đường dẫn hoặc tải file PNG/JPG/WebP tối đa 2 MB. File tải lên được ưu tiên hơn đường dẫn trong cùng lần lưu. Để xóa dải ảnh, xóa đường dẫn và không chọn file mới; tên danh mục vẫn hiển thị.

Không thêm routine mới, không đổi tham số hoặc nghiệp vụ 12 routine hiện có. Model Category đọc thêm một danh sách ảnh cho mỗi lần tải Home/tìm AJAX, không truy vấn riêng từng card. Danh mục ngừng nhận bài mới vẫn giữ dải nhãn trên địa điểm công khai hiện có.

Ảnh mới được tạo bằng công cụ imagegen tích hợp, đồng bộ phong cách poster cũ. Bộ prompt nằm ở `public/assets/images/categories/labels/generation-prompts.json`. Sáu JPG nằm cùng thư mục. Ảnh chụp địa điểm thực tế giữ nguyên.
