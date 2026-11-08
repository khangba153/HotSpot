-- Chọn database Hot Spot trước khi chạy.
SELECT COUNT(*) AS seed_places,
       SUM(latitude IS NOT NULL AND longitude IS NOT NULL) AS with_coordinates
FROM places JOIN users ON users.user_id=places.owner_id
WHERE users.email LIKE 'demo-20261107-member%@hotspot.test';

SELECT places.title,COUNT(place_images.image_id) AS image_count
FROM places JOIN users ON users.user_id=places.owner_id
LEFT JOIN place_images ON place_images.place_id=places.place_id
WHERE users.email LIKE 'demo-20261107-member%@hotspot.test'
GROUP BY places.place_id HAVING image_count <> 3;

SELECT places.title FROM places JOIN users ON users.user_id=places.owner_id
WHERE users.email LIKE 'demo-20261107-member%@hotspot.test'
AND (latitude IS NULL OR longitude IS NULL);

SELECT COUNT(*) AS old_fixture_labels FROM places WHERE title LIKE '[TEST] Hồ sơ%';
SELECT COUNT(*) AS old_review_labels FROM reviews WHERE content LIKE '[ĐÁNH GIÁ GIẢ LẬP DEMO]%';
