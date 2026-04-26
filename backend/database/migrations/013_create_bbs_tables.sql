CREATE TABLE IF NOT EXISTS `bbs_post` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `user_nickname` VARCHAR(64) DEFAULT '',
    `user_avatar` VARCHAR(255) DEFAULT '',
    `title` VARCHAR(200) NOT NULL DEFAULT '',
    `content` TEXT NOT NULL,
    `images` JSON DEFAULT NULL,
    `video` VARCHAR(500) DEFAULT '',
    `video_cover` VARCHAR(500) DEFAULT '',
    `like_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `comment_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_is_featured` (`is_featured`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `bbs_comment` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `user_nickname` VARCHAR(64) DEFAULT '',
    `user_avatar` VARCHAR(255) DEFAULT '',
    `content` TEXT NOT NULL,
    `like_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_post_id` (`post_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;