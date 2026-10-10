-- HOT SPOT - Schema 13 tables | MariaDB 10.4.32 (XAMPP) | UTF8MB4
-- ONLY import into a NEW EMPTY database. Does NOT DROP existing tables.
-- Official engine: MariaDB 10.4.32; db_hospot is a read-only comparison draft.
SET NAMES utf8mb4;
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
SET time_zone = '+00:00';

CREATE TABLE roles (
  role_code VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  role_name VARCHAR(70) NOT NULL,
  PRIMARY KEY (role_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  user_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_code VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'MEMBER',
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(254) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(user_id), UNIQUE KEY uq_users_email (email),
  KEY idx_users_role(role_code),
  CONSTRAINT fk_users_role FOREIGN KEY(role_code) REFERENCES roles(role_code) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT ck_users_name CHECK (full_name REGEXP '[^[:space:]]'),
  CONSTRAINT ck_users_email CHECK (email REGEXP '[^[:space:]]')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  category_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(category_id), UNIQUE KEY uq_categories_name(name),
  CONSTRAINT ck_categories_name CHECK (name REGEXP '[^[:space:]]')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provinces (
  province_code VARCHAR(10) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  province_name VARCHAR(120) NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY(province_code), UNIQUE KEY uq_provinces_name(province_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wards (
  ward_code VARCHAR(10) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  province_code VARCHAR(10) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  ward_name VARCHAR(150) NOT NULL,
  ward_type VARCHAR(30) NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY(ward_code),
  UNIQUE KEY uq_wards_province_ward(province_code, ward_code),
  KEY idx_wards_province_name(province_code, ward_name),
  CONSTRAINT fk_wards_province FOREIGN KEY(province_code) REFERENCES provinces(province_code)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT ck_wards_type CHECK (ward_type IN ('PHUONG','XA','DAC_KHU'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE place_statuses (
  status_code VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  display_name VARCHAR(70) NOT NULL,
  is_public BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY(status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE places (
  place_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_id BIGINT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  province_code VARCHAR(10) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  ward_code VARCHAR(10) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  status_code VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'PENDING',
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  address VARCHAR(300) NOT NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  rejection_reason TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(place_id),
  KEY idx_places_public_filter(status_code, province_code, category_id, created_at),
  KEY idx_places_owner_status(owner_id,status_code),
  KEY idx_places_category(category_id),
  KEY idx_places_ward_pair(province_code,ward_code),
  CONSTRAINT fk_places_owner FOREIGN KEY(owner_id) REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_places_category FOREIGN KEY(category_id) REFERENCES categories(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_places_province FOREIGN KEY(province_code) REFERENCES provinces(province_code) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_places_ward_pair FOREIGN KEY(province_code,ward_code) REFERENCES wards(province_code,ward_code) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_places_status FOREIGN KEY(status_code) REFERENCES place_statuses(status_code) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT ck_places_title CHECK (title REGEXP '[^[:space:]]'),
  CONSTRAINT ck_places_description CHECK (description REGEXP '[^[:space:]]'),
  CONSTRAINT ck_places_address CHECK (address REGEXP '[^[:space:]]'),
  CONSTRAINT ck_places_geo_pair CHECK ((latitude IS NULL AND longitude IS NULL) OR (latitude IS NOT NULL AND longitude IS NOT NULL)),
  CONSTRAINT ck_places_lat CHECK (latitude IS NULL OR (latitude BETWEEN -90 AND 90)),
  CONSTRAINT ck_places_lon CHECK (longitude IS NULL OR (longitude BETWEEN -180 AND 180)),
  CONSTRAINT ck_places_rejected CHECK (status_code <> 'REJECTED' OR COALESCE(rejection_reason,'') REGEXP '[^[:space:]]')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE place_images (
  image_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  place_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(500) NOT NULL,
  sort_order TINYINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(image_id),
  UNIQUE KEY uq_place_images_sort(place_id,sort_order),
  CONSTRAINT fk_place_images_place FOREIGN KEY(place_id) REFERENCES places(place_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT ck_images_sort CHECK (sort_order BETWEEN 1 AND 3),
  CONSTRAINT ck_images_path CHECK (image_path REGEXP '[^[:space:]]')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
  tag_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(90) NOT NULL,
  slug VARCHAR(100) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY(tag_id), UNIQUE KEY uq_tags_slug(slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE place_tags (
  place_id BIGINT UNSIGNED NOT NULL,
  tag_id INT UNSIGNED NOT NULL,
  PRIMARY KEY(place_id,tag_id),
  KEY idx_place_tags_tag(tag_id),
  CONSTRAINT fk_place_tags_place FOREIGN KEY(place_id) REFERENCES places(place_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_place_tags_tag FOREIGN KEY(tag_id) REFERENCES tags(tag_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_statuses (
  status_code VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  display_name VARCHAR(70) NOT NULL,
  is_public BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY(status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  review_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  place_id BIGINT UNSIGNED NOT NULL,
  status_code VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'VISIBLE',
  rating TINYINT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(review_id), UNIQUE KEY uq_reviews_author_place(user_id,place_id),
  KEY idx_reviews_place_status(place_id,status_code),
  CONSTRAINT fk_reviews_user FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_reviews_place FOREIGN KEY(place_id) REFERENCES places(place_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_reviews_status FOREIGN KEY(status_code) REFERENCES review_statuses(status_code) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT ck_reviews_rating CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT ck_reviews_content CHECK (content REGEXP '[^[:space:]]')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE favorites (
  user_id BIGINT UNSIGNED NOT NULL,
  place_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(user_id,place_id), KEY idx_favorites_place(place_id),
  CONSTRAINT fk_favorites_user FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_favorites_place FOREIGN KEY(place_id) REFERENCES places(place_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
