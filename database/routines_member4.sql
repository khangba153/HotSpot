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
