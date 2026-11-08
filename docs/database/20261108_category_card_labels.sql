-- Chạy trên database Hot Spot đã chọn. Có thể chạy lại, không ghi đè ảnh đã chọn.
SET NAMES utf8mb4;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS card_label_image_path VARCHAR(500) NULL AFTER image_path;
UPDATE categories SET card_label_image_path = 'assets/images/categories/labels/food.jpg', updated_at = updated_at WHERE name = 'Ăn uống' AND (card_label_image_path IS NULL OR card_label_image_path = '');
UPDATE categories SET card_label_image_path = 'assets/images/categories/labels/coffee.jpg', updated_at = updated_at WHERE name = 'Cà phê' AND (card_label_image_path IS NULL OR card_label_image_path = '');
UPDATE categories SET card_label_image_path = 'assets/images/categories/labels/travel.jpg', updated_at = updated_at WHERE name = 'Du lịch' AND (card_label_image_path IS NULL OR card_label_image_path = '');
UPDATE categories SET card_label_image_path = 'assets/images/categories/labels/nature.jpg', updated_at = updated_at WHERE name = 'Thiên nhiên' AND (card_label_image_path IS NULL OR card_label_image_path = '');
UPDATE categories SET card_label_image_path = 'assets/images/categories/labels/culture.jpg', updated_at = updated_at WHERE name = 'Văn hóa' AND (card_label_image_path IS NULL OR card_label_image_path = '');
UPDATE categories SET card_label_image_path = 'assets/images/categories/labels/entertainment.jpg', updated_at = updated_at WHERE name = 'Vui chơi' AND (card_label_image_path IS NULL OR card_label_image_path = '');
