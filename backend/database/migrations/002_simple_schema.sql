-- Drop tables if they exist
DROP TABLE IF EXISTS `user_interaction`;
DROP TABLE IF EXISTS `script_image`;
DROP TABLE IF EXISTS `market_image`;
DROP TABLE IF EXISTS `home_ad`;
DROP TABLE IF EXISTS `home_banner`;
DROP TABLE IF EXISTS `market_listing`;
DROP TABLE IF EXISTS `script`;
DROP TABLE IF EXISTS `brand`;
DROP TABLE IF EXISTS `category`;
DROP TABLE IF EXISTS `admin`;
DROP TABLE IF EXISTS `user`;

CREATE TABLE IF NOT EXISTS `user` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `openid` VARCHAR(64) NOT NULL UNIQUE,
    `nickname` VARCHAR(64) DEFAULT '',
    `avatar` VARCHAR(255) DEFAULT '',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_openid` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('super', 'normal') NOT NULL DEFAULT 'normal',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `category` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `brand` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `logo` VARCHAR(255) DEFAULT '',
    `follower_count` INT NOT NULL DEFAULT 0,
    `total_authorizations` INT NOT NULL DEFAULT 0,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `script` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `brand_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `alias` VARCHAR(100) DEFAULT '',
    `create_date` DATE DEFAULT NULL,
    `min_players` INT NOT NULL DEFAULT 2,
    `max_players` INT NOT NULL DEFAULT 8,
    `duration` INT NOT NULL DEFAULT 120,
    `type` VARCHAR(50) DEFAULT '',
    `authorizer` VARCHAR(100) DEFAULT '',
    `cover_image` VARCHAR(255) DEFAULT '',
    `thumbnail` VARCHAR(255) DEFAULT '',
    `video_url` TEXT DEFAULT NULL,
    `detail_content` TEXT DEFAULT NULL,
    `description` TEXT,
    `theme_attrs` JSON DEFAULT NULL,
    `detail_attrs` JSON DEFAULT NULL,
    `auth_info` JSON DEFAULT NULL,
    `status` ENUM('draft', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'draft',
    `view_count` INT NOT NULL DEFAULT 0,
    `like_count` INT NOT NULL DEFAULT 0,
    `collect_count` INT NOT NULL DEFAULT 0,
    `purchase_count` INT NOT NULL DEFAULT 0,
    `is_home_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `home_featured_sort` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_brand_id` (`brand_id`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `script_image` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `script_id` INT UNSIGNED NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    INDEX `idx_script_id` (`script_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_interaction` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `target_type` ENUM('script', 'brand') NOT NULL,
    `target_id` INT UNSIGNED NOT NULL,
    `action_type` ENUM('like', 'collect', 'follow') NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX `idx_user_target_action` (`user_id`, `target_type`, `target_id`, `action_type`),
    INDEX `idx_target` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `market_listing` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(100) NOT NULL,
    `type` ENUM('buy', 'sell') NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_is_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `home_banner` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `image` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `home_ad` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `image` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super');

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

INSERT INTO `brand` (`name`, `status`) VALUES
('Brand A', 'approved'),
('Brand B', 'approved'),
('Brand C', 'pending');

INSERT INTO `script` (`name`, `brand_id`, `category_id`, `min_players`, `max_players`, `duration`, `status`) VALUES
('Horror Script 1', 1, 1, 2, 4, 120, 'approved'),
('Mystery Script 1', 1, 2, 3, 6, 150, 'approved'),
('Detective Script 1', 2, 3, 4, 8, 180, 'pending'),
('Drama Script 1', 2, 4, 2, 6, 120, 'draft'),
('Comedy Script 1', 3, 5, 4, 10, 90, 'pending');

INSERT INTO `market_listing` (`title`, `type`, `price`, `status`) VALUES
('Sell Horror Script', 'sell', 500.00, 'approved'),
('Buy Mystery Script', 'buy', 300.00, 'pending'),
('Sell Detective Script', 'sell', 800.00, 'approved'),
('Buy Comedy Script', 'buy', 200.00, 'pending');
