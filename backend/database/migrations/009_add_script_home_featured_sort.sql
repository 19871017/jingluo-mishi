-- Add home carousel sort field to script table

ALTER TABLE `script`
    ADD COLUMN `home_featured_sort` INT NOT NULL DEFAULT 0 AFTER `is_home_featured`;
