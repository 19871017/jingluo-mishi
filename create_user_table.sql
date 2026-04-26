CREATE TABLE IF NOT EXISTS `user` (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    openid VARCHAR(64) NOT NULL UNIQUE,
    nickname VARCHAR(64) DEFAULT '',
    avatar VARCHAR(255) DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_openid (openid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_interaction` (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    target_type ENUM('script', 'brand') NOT NULL,
    target_id INT UNSIGNED NOT NULL,
    action_type ENUM('like', 'collect', 'follow') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_user_target_action (user_id, target_type, target_id, action_type),
    INDEX idx_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;