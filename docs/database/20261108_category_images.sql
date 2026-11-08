-- MariaDB local / alwaysdata. Chạy trên database của Hot Spot đã chọn.
-- Không xóa danh mục, địa điểm hoặc mô tả đã được người dùng chỉnh sửa.
SET NAMES utf8mb4;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) NULL AFTER description;
UPDATE categories SET image_path = 'assets/images/categories/food.jpg', description = COALESCE(NULLIF(TRIM(description), ''), 'Món ngon địa phương') WHERE name = 'Ăn uống' AND (image_path IS NULL OR image_path = '');
UPDATE categories SET image_path = 'assets/images/categories/coffee.jpg', description = COALESCE(NULLIF(TRIM(description), ''), 'Quán cà phê đáng thử') WHERE name = 'Cà phê' AND (image_path IS NULL OR image_path = '');
UPDATE categories SET image_path = 'assets/images/categories/travel.jpg', description = COALESCE(NULLIF(TRIM(description), ''), 'Điểm đến nổi bật') WHERE name = 'Du lịch' AND (image_path IS NULL OR image_path = '');
UPDATE categories SET image_path = 'assets/images/categories/nature.jpg', description = COALESCE(NULLIF(TRIM(description), ''), 'Gần gũi thiên nhiên') WHERE name = 'Thiên nhiên' AND (image_path IS NULL OR image_path = '');
UPDATE categories SET image_path = 'assets/images/categories/culture.jpg', description = COALESCE(NULLIF(TRIM(description), ''), 'Văn hóa và di sản') WHERE name = 'Văn hóa' AND (image_path IS NULL OR image_path = '');
UPDATE categories SET image_path = 'assets/images/categories/entertainment.jpg', description = COALESCE(NULLIF(TRIM(description), ''), 'Đi chơi cuối tuần') WHERE name = 'Vui chơi' AND (image_path IS NULL OR image_path = '');

-- Giữ nguyên tên, tham số và nghiệp vụ routine; bổ sung hai cột hiển thị.
DROP PROCEDURE IF EXISTS PTH_LIST_ACTIVE_CATEGORIES;
DELIMITER $$
CREATE PROCEDURE PTH_LIST_ACTIVE_CATEGORIES()
SQL SECURITY INVOKER
BEGIN
    SELECT category_id, name, description, image_path
    FROM categories WHERE is_active = 1 ORDER BY name, category_id;
END$$
DELIMITER ;
