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
