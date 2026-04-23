-- Purchase intent records for scripts

CREATE TABLE IF NOT EXISTS `script_purchase_intent` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `script_id` INT UNSIGNED NOT NULL,
    `brand_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `city` VARCHAR(100) NOT NULL DEFAULT '',
    `contact_name` VARCHAR(100) NOT NULL DEFAULT '',
    `contact_phone` VARCHAR(50) NOT NULL DEFAULT '',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_script_id` (`script_id`),
    INDEX `idx_brand_id` (`brand_id`)
);
