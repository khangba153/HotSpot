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
