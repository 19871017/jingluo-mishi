-- Expand category taxonomy to match the 12 Chinese script types used by frontend filters

-- Normalize legacy category names first
UPDATE `category` SET `name` = '恐怖' WHERE TRIM(`name`) IN ('Horror', '恐怖');
UPDATE `category` SET `name` = '悬疑' WHERE TRIM(`name`) IN ('Mystery', '悬疑');
UPDATE `category` SET `name` = '推理' WHERE TRIM(`name`) IN ('Detective', '推理');
UPDATE `category` SET `name` = '情感' WHERE TRIM(`name`) IN ('Drama', '剧情', '情感');
UPDATE `category` SET `name` = '欢乐' WHERE TRIM(`name`) IN ('Comedy', '欢乐');
UPDATE `category` SET `name` = '科幻' WHERE TRIM(`name`) IN ('Sci-Fi', '科幻');

-- Ensure the full category set exists
INSERT INTO `category` (`name`, `sort_order`)
SELECT '实景推理', 4 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `name` = '实景推理');
INSERT INTO `category` (`name`, `sort_order`)
SELECT '沉浸演绎', 5 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `name` = '沉浸演绎');
INSERT INTO `category` (`name`, `sort_order`)
SELECT '解谜逃脱', 6 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `name` = '解谜逃脱');
INSERT INTO `category` (`name`, `sort_order`)
SELECT '角色扮演', 7 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `name` = '角色扮演');
INSERT INTO `category` (`name`, `sort_order`)
SELECT '机关密室', 8 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `name` = '机关密室');
INSERT INTO `category` (`name`, `sort_order`)
SELECT '古风', 11 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `name` = '古风');
INSERT INTO `category` (`name`, `sort_order`)
SELECT '儿童密室', 13 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `name` = '儿童密室');

-- Keep sort order aligned with frontend type order
UPDATE `category` SET `sort_order` = 1 WHERE `name` = '儿童密室';
UPDATE `category` SET `sort_order` = 2 WHERE `name` = '恐怖';
UPDATE `category` SET `sort_order` = 3 WHERE `name` = '悬疑';
UPDATE `category` SET `sort_order` = 4 WHERE `name` = '实景推理';
UPDATE `category` SET `sort_order` = 5 WHERE `name` = '沉浸演绎';
UPDATE `category` SET `sort_order` = 6 WHERE `name` = '解谜逃脱';
UPDATE `category` SET `sort_order` = 7 WHERE `name` = '角色扮演';
UPDATE `category` SET `sort_order` = 8 WHERE `name` = '机关密室';
UPDATE `category` SET `sort_order` = 9 WHERE `name` = '情感';
UPDATE `category` SET `sort_order` = 10 WHERE `name` = '科幻';
UPDATE `category` SET `sort_order` = 11 WHERE `name` = '古风';
UPDATE `category` SET `sort_order` = 12 WHERE `name` = '欢乐';

-- Move existing scripts into the closest matching new taxonomy
UPDATE `script` s
JOIN `category` c ON c.`id` = s.`category_id`
JOIN `category` target ON target.`name` = '情感'
SET s.`category_id` = target.`id`
WHERE c.`name` IN ('剧情');
