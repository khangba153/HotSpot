-- TV2: 3 stored procedures. Rename TV2 prefix to member initials before production integration.
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
DELIMITER $$
CREATE PROCEDURE TV2_LIST_ACTIVE_CATEGORIES()
SQL SECURITY INVOKER
BEGIN
  SELECT category_id,name FROM categories WHERE is_active=1 ORDER BY name,category_id;
END$$

CREATE PROCEDURE TV2_SEARCH_APPROVED_PLACES(
  IN p_keyword VARCHAR(150),
  IN p_category_id INT UNSIGNED,
  IN p_province_code VARCHAR(10),
  IN p_ward_code VARCHAR(10),
  IN p_tag_slug VARCHAR(100),
  IN p_page INT,
  IN p_page_size INT
)
SQL SECURITY INVOKER
BEGIN
  DECLARE v_page INT DEFAULT 1;
  DECLARE v_page_size INT DEFAULT 12;
  DECLARE v_offset BIGINT UNSIGNED DEFAULT 0;
  SET v_page=GREATEST(COALESCE(p_page,1),1);
  SET v_page_size=LEAST(GREATEST(COALESCE(p_page_size,12),1),30);
  SET v_offset=(CAST(v_page AS UNSIGNED)-1)*v_page_size;
  -- Result set 1: page data. Old posts remain visible even if category is inactive.
  SELECT p.place_id,p.title,p.description,p.address,p.category_id,c.name AS category_name,
         p.province_code,pr.province_name,p.ward_code,p.latitude,p.longitude,p.created_at,
         (SELECT pi.image_path FROM place_images pi WHERE pi.place_id=p.place_id ORDER BY pi.sort_order LIMIT 1) AS cover_image,
         (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.place_id=p.place_id AND r.status_code='VISIBLE') AS average_rating,
         (SELECT COUNT(*) FROM reviews r WHERE r.place_id=p.place_id AND r.status_code='VISIBLE') AS review_count
  FROM places p
  JOIN categories c ON c.category_id=p.category_id
  JOIN provinces pr ON pr.province_code=p.province_code
  WHERE p.status_code='APPROVED'
    AND (NULLIF(TRIM(p_keyword),'') IS NULL OR p.title LIKE CONCAT('%',TRIM(p_keyword),'%') OR p.address LIKE CONCAT('%',TRIM(p_keyword),'%'))
    AND (p_category_id IS NULL OR p.category_id=p_category_id)
    AND (NULLIF(TRIM(p_province_code),'') IS NULL OR p.province_code=p_province_code)
    AND (NULLIF(TRIM(p_ward_code),'') IS NULL OR p.ward_code=p_ward_code)
    AND (NULLIF(TRIM(p_tag_slug),'') IS NULL OR EXISTS (
      SELECT 1 FROM place_tags pt JOIN tags t ON t.tag_id=pt.tag_id
      WHERE pt.place_id=p.place_id AND t.slug=p_tag_slug))
  ORDER BY p.created_at DESC,p.place_id DESC LIMIT v_page_size OFFSET v_offset;
  -- Result set 2: total rows so an empty page still has a pagination count.
  SELECT COUNT(*) AS total_count FROM places p
  WHERE p.status_code='APPROVED'
    AND (NULLIF(TRIM(p_keyword),'') IS NULL OR p.title LIKE CONCAT('%',TRIM(p_keyword),'%') OR p.address LIKE CONCAT('%',TRIM(p_keyword),'%'))
    AND (p_category_id IS NULL OR p.category_id=p_category_id)
    AND (NULLIF(TRIM(p_province_code),'') IS NULL OR p.province_code=p_province_code)
    AND (NULLIF(TRIM(p_ward_code),'') IS NULL OR p.ward_code=p_ward_code)
    AND (NULLIF(TRIM(p_tag_slug),'') IS NULL OR EXISTS (
      SELECT 1 FROM place_tags pt JOIN tags t ON t.tag_id=pt.tag_id
      WHERE pt.place_id=p.place_id AND t.slug=p_tag_slug));
END$$

CREATE PROCEDURE TV2_LIST_WARDS_BY_PROVINCE(IN p_province_code VARCHAR(10))
SQL SECURITY INVOKER
BEGIN
  IF p_province_code IS NULL OR p_province_code NOT REGEXP '[^[:space:]]' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Province is required';
  END IF;
  SELECT ward_code,province_code,ward_name,ward_type
    FROM wards WHERE province_code=p_province_code AND is_active=1
    ORDER BY ward_name,ward_code;
END$$
DELIMITER ;
