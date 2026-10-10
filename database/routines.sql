-- MariaDB 10.4.32: combined copy; import this OR the four member files, never both.
-- KH (Khang): 3 stored procedures. MariaDB 10.4.32
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
DELIMITER $$
CREATE PROCEDURE KH_CREATE_USER(
  IN p_full_name VARCHAR(120),
  IN p_email VARCHAR(254),
  IN p_password_hash VARCHAR(255)
)
SQL SECURITY INVOKER
BEGIN
  IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caller transaction required'; END IF;
  IF p_full_name IS NULL OR p_full_name NOT REGEXP '[^[:space:]]'
     OR p_email IS NULL OR p_email NOT REGEXP '[^[:space:]]'
     OR p_password_hash IS NULL OR p_password_hash NOT REGEXP '[^[:space:]]' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Missing registration fields';
  END IF;
  -- Validate email format in PHP; the UNIQUE index prevents duplicates even under concurrency.
  INSERT INTO users(role_code,full_name,email,password_hash)
    VALUES('MEMBER',TRIM(p_full_name),LOWER(TRIM(p_email)),p_password_hash);
  SELECT LAST_INSERT_ID() AS user_id;
END$$

CREATE PROCEDURE KH_GET_USER_BY_EMAIL(IN p_email VARCHAR(254))
SQL SECURITY INVOKER
BEGIN
  IF p_email IS NULL OR p_email NOT REGEXP '[^[:space:]]' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Email is required';
  END IF;
  SELECT user_id,role_code,full_name,email,password_hash,created_at
    FROM users WHERE email=LOWER(TRIM(p_email)) LIMIT 1;
END$$

CREATE PROCEDURE KH_SET_FAVORITE(
  IN p_user_id BIGINT UNSIGNED,
  IN p_place_id BIGINT UNSIGNED,
  IN p_is_favorite BOOLEAN
)
SQL SECURITY INVOKER
BEGIN
  DECLARE v_user_count INT DEFAULT 0;
  DECLARE v_place_status VARCHAR(20) DEFAULT NULL;
  IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caller transaction required'; END IF;
  IF p_is_favorite IS NULL OR p_is_favorite NOT IN (0,1) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Favorite flag must be 0 or 1';
  END IF;
  SELECT COUNT(*) INTO v_user_count FROM users WHERE user_id=p_user_id;
  IF v_user_count=0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='User not found';
  END IF;
  IF p_is_favorite=1 THEN
    SELECT status_code INTO v_place_status FROM places WHERE place_id=p_place_id FOR UPDATE;
    IF v_place_status IS NULL OR v_place_status<>'APPROVED' THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place is not public';
    END IF;
    INSERT INTO favorites(user_id,place_id) VALUES(p_user_id,p_place_id)
      ON DUPLICATE KEY UPDATE user_id=VALUES(user_id);
  ELSE
    -- Removing old favorite is permitted even if the place is now hidden.
    DELETE FROM favorites WHERE user_id=p_user_id AND place_id=p_place_id;
  END IF;
  SELECT p_place_id AS place_id, IF(p_is_favorite=1,1,0) AS is_favorite;
END$$
DELIMITER ;

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

-- TV3: 3 stored procedures. App controls PDO transaction across place/images/tags.
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
DELIMITER $$
CREATE PROCEDURE TV3_SUBMIT_PLACE(
  IN p_owner_id BIGINT UNSIGNED,
  IN p_category_id INT UNSIGNED,
  IN p_province_code VARCHAR(10),
  IN p_ward_code VARCHAR(10),
  IN p_title VARCHAR(180),
  IN p_description TEXT,
  IN p_address VARCHAR(300),
  IN p_latitude DECIMAL(10,7),
  IN p_longitude DECIMAL(10,7)
)
SQL SECURITY INVOKER
BEGIN
  DECLARE v_count INT DEFAULT 0;
  IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caller transaction required'; END IF;
  IF p_title IS NULL OR p_title NOT REGEXP '[^[:space:]]' OR p_description IS NULL
     OR p_description NOT REGEXP '[^[:space:]]' OR p_address IS NULL
     OR p_address NOT REGEXP '[^[:space:]]' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place fields are required';
  END IF;
  IF (p_latitude IS NULL) <> (p_longitude IS NULL) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Latitude and longitude must both be set or null';
  END IF;
  SELECT COUNT(*) INTO v_count FROM categories WHERE category_id=p_category_id AND is_active=1;
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Category unavailable'; END IF;
  SELECT COUNT(*) INTO v_count FROM provinces WHERE province_code=p_province_code AND is_active=1;
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Province unavailable'; END IF;
  IF p_ward_code IS NOT NULL AND TRIM(p_ward_code)<>'' THEN
    SELECT COUNT(*) INTO v_count FROM wards WHERE ward_code=p_ward_code
      AND province_code=p_province_code AND is_active=1;
    IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Ward does not belong to province'; END IF;
  END IF;
  INSERT INTO places(owner_id,category_id,province_code,ward_code,status_code,title,description,address,latitude,longitude)
  VALUES(p_owner_id,p_category_id,p_province_code,NULLIF(TRIM(p_ward_code),''),'PENDING',
         TRIM(p_title),TRIM(p_description),TRIM(p_address),p_latitude,p_longitude);
  SELECT LAST_INSERT_ID() AS place_id;
END$$

CREATE PROCEDURE TV3_ADD_PLACE_IMAGE(
  IN p_owner_id BIGINT UNSIGNED,
  IN p_place_id BIGINT UNSIGNED,
  IN p_image_path VARCHAR(500),
  IN p_sort_order TINYINT UNSIGNED
)
SQL SECURITY INVOKER
BEGIN
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_locked_id BIGINT UNSIGNED DEFAULT NULL;
  IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caller transaction required'; END IF;
  IF p_image_path IS NULL OR p_image_path NOT REGEXP '[^[:space:]]'
      OR p_sort_order IS NULL OR p_sort_order NOT BETWEEN 1 AND 3 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid image path/order';
  END IF;
  SELECT place_id INTO v_locked_id FROM places
    WHERE place_id=p_place_id AND owner_id=p_owner_id AND status_code IN ('PENDING','REJECTED') FOR UPDATE;
  IF v_locked_id IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place cannot be edited by user'; END IF;
  INSERT INTO place_images(place_id,image_path,sort_order) VALUES(p_place_id,TRIM(p_image_path),p_sort_order);
  SELECT LAST_INSERT_ID() AS image_id;
END$$

CREATE PROCEDURE TV3_RESUBMIT_PLACE(
  IN p_owner_id BIGINT UNSIGNED,
  IN p_place_id BIGINT UNSIGNED,
  IN p_category_id INT UNSIGNED,
  IN p_province_code VARCHAR(10),
  IN p_ward_code VARCHAR(10),
  IN p_title VARCHAR(180),
  IN p_description TEXT,
  IN p_address VARCHAR(300),
  IN p_latitude DECIMAL(10,7),
  IN p_longitude DECIMAL(10,7)
)
SQL SECURITY INVOKER
BEGIN
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_old_category INT UNSIGNED DEFAULT NULL;
  IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caller transaction required'; END IF;
  SELECT category_id INTO v_old_category FROM places
    WHERE place_id=p_place_id AND owner_id=p_owner_id AND status_code IN ('PENDING','REJECTED') FOR UPDATE;
  IF v_old_category IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place cannot be resubmitted'; END IF;
  IF p_title IS NULL OR p_title NOT REGEXP '[^[:space:]]' OR p_description IS NULL
     OR p_description NOT REGEXP '[^[:space:]]' OR p_address IS NULL
     OR p_address NOT REGEXP '[^[:space:]]' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place fields are required';
  END IF;
  IF (p_latitude IS NULL) <> (p_longitude IS NULL) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Latitude and longitude must both be set or null';
  END IF;
  SELECT COUNT(*) INTO v_count FROM categories WHERE category_id=p_category_id AND (is_active=1 OR category_id=v_old_category);
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Category unavailable'; END IF;
  SELECT COUNT(*) INTO v_count FROM provinces WHERE province_code=p_province_code AND is_active=1;
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Province unavailable'; END IF;
  IF p_ward_code IS NOT NULL AND TRIM(p_ward_code)<>'' THEN
    SELECT COUNT(*) INTO v_count FROM wards WHERE ward_code=p_ward_code
      AND province_code=p_province_code AND is_active=1;
    IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Ward does not belong to province'; END IF;
  END IF;
  UPDATE places SET category_id=p_category_id,province_code=p_province_code,
    ward_code=NULLIF(TRIM(p_ward_code),''),title=TRIM(p_title),description=TRIM(p_description),
    address=TRIM(p_address),latitude=p_latitude,longitude=p_longitude,
    status_code='PENDING',rejection_reason=NULL
  WHERE place_id=p_place_id AND owner_id=p_owner_id AND status_code IN ('PENDING','REJECTED');
  IF ROW_COUNT()=0 THEN
    -- An unchanged PENDING update also returns 0: explicitly check existence/permission.
    SELECT COUNT(*) INTO v_count FROM places WHERE place_id=p_place_id AND owner_id=p_owner_id AND status_code='PENDING';
    IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place cannot be resubmitted'; END IF;
  END IF;
  SELECT p_place_id AS place_id,'PENDING' AS status_code;
END$$
DELIMITER ;

-- TV4: 3 stored procedures. Admin authentication uses users.role_code in DB.
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
DELIMITER $$
CREATE PROCEDURE TV4_SAVE_OR_UPDATE_REVIEW(
  IN p_user_id BIGINT UNSIGNED,
  IN p_place_id BIGINT UNSIGNED,
  IN p_rating TINYINT UNSIGNED,
  IN p_content TEXT
)
SQL SECURITY INVOKER
BEGIN
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_place_status VARCHAR(20) DEFAULT NULL;
  IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caller transaction required'; END IF;
  IF p_rating IS NULL OR p_rating NOT BETWEEN 1 AND 5 OR
     p_content IS NULL OR p_content NOT REGEXP '[^[:space:]]' THEN
     SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid review';
  END IF;
  SELECT status_code INTO v_place_status FROM places WHERE place_id=p_place_id FOR UPDATE;
  IF v_place_status IS NULL OR v_place_status<>'APPROVED' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place not public'; END IF;
  -- DO NOT include status_code in duplicate update: hidden reviews must stay hidden.
  INSERT INTO reviews(user_id,place_id,status_code,rating,content)
    VALUES(p_user_id,p_place_id,'VISIBLE',p_rating,TRIM(p_content))
    ON DUPLICATE KEY UPDATE rating=VALUES(rating),content=VALUES(content);
  SELECT review_id,status_code,rating FROM reviews WHERE user_id=p_user_id AND place_id=p_place_id;
END$$

CREATE PROCEDURE TV4_SET_REVIEW_STATUS(
  IN p_admin_id BIGINT UNSIGNED,
  IN p_review_id BIGINT UNSIGNED,
  IN p_status VARCHAR(20)
)
SQL SECURITY INVOKER
BEGIN
  DECLARE v_count INT DEFAULT 0;
  IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caller transaction required'; END IF;
  SET p_status=UPPER(TRIM(p_status));
  IF p_status IS NULL OR p_status NOT IN ('VISIBLE','HIDDEN') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid review status';
  END IF;
  SELECT COUNT(*) INTO v_count FROM users WHERE user_id=p_admin_id AND role_code='ADMIN';
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Admin permission required'; END IF;
  SELECT COUNT(*) INTO v_count FROM reviews WHERE review_id=p_review_id;
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Review not found'; END IF;
  UPDATE reviews SET status_code=p_status WHERE review_id=p_review_id;
  SELECT p_review_id AS review_id,p_status AS status_code;
END$$

CREATE PROCEDURE TV4_SET_PLACE_STATUS(
  IN p_admin_id BIGINT UNSIGNED,
  IN p_place_id BIGINT UNSIGNED,
  IN p_new_status VARCHAR(20),
  IN p_rejection_reason TEXT
)
SQL SECURITY INVOKER
BEGIN
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_prev_status VARCHAR(20) DEFAULT NULL;
  DECLARE v_image_count INT DEFAULT 0;
  IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Caller transaction required'; END IF;
  SET p_new_status=UPPER(TRIM(p_new_status));
  SELECT COUNT(*) INTO v_count FROM users WHERE user_id=p_admin_id AND role_code='ADMIN';
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Admin permission required'; END IF;
  SELECT status_code INTO v_prev_status FROM places WHERE place_id=p_place_id FOR UPDATE;
  IF v_prev_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place not found'; END IF;
  IF p_new_status IS NULL OR p_new_status NOT IN ('APPROVED','REJECTED','HIDDEN') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid place status';
  END IF;
  IF NOT ((v_prev_status='PENDING' AND p_new_status IN ('APPROVED','REJECTED'))
       OR (v_prev_status='APPROVED' AND p_new_status='HIDDEN')
       OR (v_prev_status='HIDDEN' AND p_new_status='APPROVED')) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid place state transition';
  END IF;
  IF p_new_status='REJECTED' AND (p_rejection_reason IS NULL OR p_rejection_reason NOT REGEXP '[^[:space:]]') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Rejection reason required';
  END IF;
  IF p_new_status='APPROVED' THEN
    SELECT COUNT(*) INTO v_image_count FROM place_images WHERE place_id=p_place_id;
    IF v_image_count NOT BETWEEN 1 AND 3 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Place must have 1-3 images';
    END IF;
  END IF;
  UPDATE places SET status_code=p_new_status,
    rejection_reason=CASE WHEN p_new_status='REJECTED' THEN TRIM(p_rejection_reason) ELSE NULL END
    WHERE place_id=p_place_id AND status_code=v_prev_status;
  IF ROW_COUNT()=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Concurrent place status change'; END IF;
  SELECT p_place_id AS place_id,p_new_status AS status_code;
END$$
DELIMITER ;
