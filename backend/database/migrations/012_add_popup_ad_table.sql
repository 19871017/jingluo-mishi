-- Add Popup Ad Table
CREATE TABLE IF NOT EXISTS `popup_ad` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `image` VARCHAR(255) NOT NULL COMMENT 'Popup Ad Image URL',
    `script_id` INT UNSIGNED NOT NULL COMMENT 'Target Script ID',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Is Active',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Sort Order',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_is_active` (`is_active`),
    FOREIGN KEY (`script_id`) REFERENCES `script`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Popup Ad Table';

-- Insert sample popup ad
INSERT INTO `popup_ad` (`image`, `script_id`, `is_active`, `sort_order`) VALUES
('https://via.placeholder.com/600x800/2563eb/ffffff?text=Popup+Ad', 1, 1, 1);