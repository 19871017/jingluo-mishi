-- Add collect_count and purchase_count to script table

ALTER TABLE `script`
    ADD COLUMN `collect_count` INT NOT NULL DEFAULT 0 AFTER `like_count`,
    ADD COLUMN `purchase_count` INT NOT NULL DEFAULT 0 AFTER `collect_count`;
