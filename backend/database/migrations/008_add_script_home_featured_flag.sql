-- Add home carousel featured flag to script table

ALTER TABLE `script`
    ADD COLUMN `is_home_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `purchase_count`;
