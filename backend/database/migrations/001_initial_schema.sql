-- Escape Room Script Platform Database Schema
-- Version: 1.0.0

CREATE DATABASE IF NOT EXISTS think DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE think;

-- User Table
CREATE TABLE IF NOT EXISTS `user` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `openid` VARCHAR(64) NOT NULL UNIQUE COMMENT 'WeChat OpenID',
    `nickname` VARCHAR(64) DEFAULT '' COMMENT 'Nickname',
    `avatar` VARCHAR(255) DEFAULT '' COMMENT 'Avatar URL',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_openid` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User Table';

-- Admin Table
CREATE TABLE IF NOT EXISTS `admin` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Username',
    `password` VARCHAR(255) NOT NULL COMMENT 'Password (hashed)',
    `role` ENUM('super', 'normal') NOT NULL DEFAULT 'normal' COMMENT 'Role: super or normal',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin Table';

-- Category Table
CREATE TABLE IF NOT EXISTS `category` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL COMMENT 'Category Name',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Sort Order',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Category Table';

-- Brand Table
CREATE TABLE IF NOT EXISTS `brand` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Brand Name',
    `logo` VARCHAR(255) DEFAULT '' COMMENT 'Logo URL',
    `description` TEXT COMMENT 'Brand Description',
    `total_authorizations` INT NOT NULL DEFAULT 0 COMMENT 'Total Authorizations',
    `total_views` INT NOT NULL DEFAULT 0 COMMENT 'Total Views',
    `total_likes` INT NOT NULL DEFAULT 0 COMMENT 'Total Likes',
    `follower_count` INT NOT NULL DEFAULT 0 COMMENT 'Follower Count',
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' COMMENT 'Status',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Brand Table';

-- Script Table
CREATE TABLE IF NOT EXISTS `script` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `brand_id` INT UNSIGNED NOT NULL COMMENT 'Brand ID',
    `category_id` INT UNSIGNED NOT NULL COMMENT 'Category ID',
    `name` VARCHAR(100) NOT NULL COMMENT 'Script Name',
    `alias` VARCHAR(100) DEFAULT '' COMMENT 'Alias',
    `create_date` DATE DEFAULT NULL COMMENT 'Creation Date',
    `min_players` INT NOT NULL DEFAULT 1 COMMENT 'Minimum Players',
    `max_players` INT NOT NULL DEFAULT 10 COMMENT 'Maximum Players',
    `duration` INT NOT NULL DEFAULT 60 COMMENT 'Duration in Minutes',
    `type` VARCHAR(50) DEFAULT '' COMMENT 'Script Type',
    `authorizer` VARCHAR(100) DEFAULT '' COMMENT 'Authorizer',
    `description` TEXT COMMENT 'Description',
    `thumbnail` VARCHAR(255) DEFAULT '' COMMENT 'Thumbnail URL',
    `video_url` TEXT DEFAULT NULL COMMENT 'Video URLs (comma-separated)',
    `detail_content` TEXT DEFAULT NULL COMMENT 'Detailed introduction text',
    `theme_attrs` JSON DEFAULT NULL COMMENT 'Theme Attributes',
    `detail_attrs` JSON DEFAULT NULL COMMENT 'Detail Attributes',
    `auth_info` JSON DEFAULT NULL COMMENT 'Authorization Info',
    `status` ENUM('pending', 'approved', 'rejected', 'draft') NOT NULL DEFAULT 'pending' COMMENT 'Status',
    `view_count` INT NOT NULL DEFAULT 0 COMMENT 'View Count',
    `like_count` INT NOT NULL DEFAULT 0 COMMENT 'Like Count',
    `collect_count` INT NOT NULL DEFAULT 0 COMMENT 'Collect Count',
    `purchase_count` INT NOT NULL DEFAULT 0 COMMENT 'Purchase Count',
    `is_home_featured` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Show in home carousel first',
    `home_featured_sort` INT NOT NULL DEFAULT 0 COMMENT 'Home carousel order',
    `is_script_featured` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Show in script page carousel first',
    `script_featured_sort` INT NOT NULL DEFAULT 0 COMMENT 'Script page carousel order',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_brand_id` (`brand_id`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`brand_id`) REFERENCES `brand`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `category`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Script Table';

-- Script Image Table
CREATE TABLE IF NOT EXISTS `script_image` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `script_id` INT UNSIGNED NOT NULL COMMENT 'Script ID',
    `url` VARCHAR(255) NOT NULL COMMENT 'Image URL',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Sort Order',
    INDEX `idx_script_id` (`script_id`),
    FOREIGN KEY (`script_id`) REFERENCES `script`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Script Image Table';

-- Market Listing Table
CREATE TABLE IF NOT EXISTS `market_listing` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'User ID',
    `type` ENUM('buy', 'sell') NOT NULL COMMENT 'Listing Type: buy or sell',
    `title` VARCHAR(100) NOT NULL COMMENT 'Title',
    `description` TEXT COMMENT 'Description',
    `price` DECIMAL(10, 2) DEFAULT NULL COMMENT 'Price',
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' COMMENT 'Status',
    `is_featured` TINYINT NOT NULL DEFAULT 0 COMMENT 'Is Featured',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`type`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Market Listing Table';

-- Market Image Table
CREATE TABLE IF NOT EXISTS `market_image` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `listing_id` INT UNSIGNED NOT NULL COMMENT 'Listing ID',
    `url` VARCHAR(255) NOT NULL COMMENT 'Image URL',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Sort Order',
    INDEX `idx_listing_id` (`listing_id`),
    FOREIGN KEY (`listing_id`) REFERENCES `market_listing`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Market Image Table';

-- User Interaction Table
CREATE TABLE IF NOT EXISTS `user_interaction` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'User ID',
    `target_type` ENUM('script', 'brand') NOT NULL COMMENT 'Target Type: script or brand',
    `target_id` INT UNSIGNED NOT NULL COMMENT 'Target ID',
    `action_type` ENUM('like', 'collect', 'follow') NOT NULL COMMENT 'Action Type: like, collect, follow',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX `idx_user_target_action` (`user_id`, `target_type`, `target_id`, `action_type`),
    INDEX `idx_target` (`target_type`, `target_id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User Interaction Table';

-- Home Banner Table
CREATE TABLE IF NOT EXISTS `home_banner` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `image` VARCHAR(255) NOT NULL COMMENT 'Banner Image URL',
    `link` VARCHAR(255) DEFAULT '' COMMENT 'Link URL',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Sort Order',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Home Banner Table';

-- Home Ad Table
CREATE TABLE IF NOT EXISTS `home_ad` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `image` VARCHAR(255) NOT NULL COMMENT 'Ad Image URL',
    `link` VARCHAR(255) DEFAULT '' COMMENT 'Link URL',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Sort Order',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Home Ad Table';

-- Insert default admin (password: admin123)
INSERT INTO `admin` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super');

-- Insert default categories
INSERT INTO `category` (`name`, `sort_order`) VALUES
('恐怖', 1),
('悬疑', 2),
('推理', 3),
('实景推理', 4),
('沉浸演绎', 5),
('解谜逃脱', 6),
('角色扮演', 7),
('机关密室', 8),
('情感', 9),
('科幻', 10),
('古风', 11),
('欢乐', 12),
('儿童密室', 13);
