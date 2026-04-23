-- Normalize legacy category labels to Chinese

UPDATE `category` SET `name` = '恐怖' WHERE TRIM(`name`) = 'Horror';
UPDATE `category` SET `name` = '悬疑' WHERE TRIM(`name`) = 'Mystery';
UPDATE `category` SET `name` = '推理' WHERE TRIM(`name`) = 'Detective';
UPDATE `category` SET `name` = '剧情' WHERE TRIM(`name`) = 'Drama';
UPDATE `category` SET `name` = '欢乐' WHERE TRIM(`name`) = 'Comedy';
UPDATE `category` SET `name` = '科幻' WHERE TRIM(`name`) = 'Sci-Fi';
