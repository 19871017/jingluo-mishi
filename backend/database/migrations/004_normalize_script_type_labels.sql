-- Normalize legacy script type labels to Chinese

UPDATE `script`
SET `script_type` = '角色扮演'
WHERE TRIM(`script_type`) IN ('RPG', 'rpg');

UPDATE `script`
SET `script_type` = '沉浸演绎'
WHERE TRIM(`script_type`) = '沉浸';
