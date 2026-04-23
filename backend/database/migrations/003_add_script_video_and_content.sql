-- Add video_url and detail_content columns to script table
-- These fields are expected by the mini-program frontend

ALTER TABLE `script`
    ADD COLUMN `video_url` TEXT DEFAULT NULL COMMENT 'Video URLs (comma-separated)' AFTER `thumbnail`,
    ADD COLUMN `detail_content` TEXT DEFAULT NULL COMMENT 'Detailed introduction text' AFTER `video_url`;
