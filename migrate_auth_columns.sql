USE village_traveler;

-- Add role only if missing
SET @has_role := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'role'
);
SET @sql := IF(
    @has_role = 0,
    "ALTER TABLE users ADD COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user' AFTER password_hash",
    "SELECT 'role column already exists' AS msg"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add email only if missing
SET @has_email := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'email'
);
SET @sql := IF(
    @has_email = 0,
    "ALTER TABLE users ADD COLUMN email VARCHAR(190) NULL AFTER username",
    "SELECT 'email column already exists' AS msg"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add reset token hash only if missing
SET @has_reset_hash := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'reset_token_hash'
);
SET @sql := IF(
    @has_reset_hash = 0,
    "ALTER TABLE users ADD COLUMN reset_token_hash CHAR(64) DEFAULT NULL AFTER role",
    "SELECT 'reset_token_hash column already exists' AS msg"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add reset token expiry only if missing
SET @has_reset_exp := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'reset_token_expires_at'
);
SET @sql := IF(
    @has_reset_exp = 0,
    "ALTER TABLE users ADD COLUMN reset_token_expires_at DATETIME DEFAULT NULL AFTER reset_token_hash",
    "SELECT 'reset_token_expires_at column already exists' AS msg"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure admin has role/email
UPDATE users
SET role = 'admin'
WHERE username = 'admin';

UPDATE users
SET email = 'info.itzone.sl@gmail.com'
WHERE username = 'admin'
  AND (email IS NULL OR email = '');

-- Add unique index for email only if missing
SET @has_email_idx := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND INDEX_NAME = 'users_email_unique'
);
SET @sql := IF(
    @has_email_idx = 0,
    "ALTER TABLE users ADD UNIQUE KEY users_email_unique (email)",
    "SELECT 'users_email_unique index already exists' AS msg"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- If some old users still have NULL email, set placeholder then enforce NOT NULL
UPDATE users
SET email = CONCAT('user', id, '@local.invalid')
WHERE email IS NULL OR email = '';

ALTER TABLE users
MODIFY email VARCHAR(190) NOT NULL;
