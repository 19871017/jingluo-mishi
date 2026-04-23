-- Add independent script page carousel featured fields

ALTER TABLE `script`
    ADD COLUMN `is_script_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `home_featured_sort`,
    ADD COLUMN `script_featured_sort` INT NOT NULL DEFAULT 0 AFTER `is_script_featured`;
