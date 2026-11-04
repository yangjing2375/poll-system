-- 在线投票系统数据库结构（MySQL 5.7 / 8.0）
-- 用法：mysql -uroot -proot < sql/schema.sql

CREATE DATABASE IF NOT EXISTS poll_system
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE poll_system;

-- ================= 用户与管理员 =================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar TEXT DEFAULT NULL,
    avatar_data LONGBLOB DEFAULT NULL,
    gender ENUM('male', 'female') DEFAULT NULL,
    age INT DEFAULT NULL,
    birthday DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_username (username),
    UNIQUE KEY uk_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    avatar TEXT DEFAULT NULL,
    avatar_data LONGBLOB DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================= 投票 =================
CREATE TABLE IF NOT EXISTS polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    topic VARCHAR(50) DEFAULT NULL,
    creator_id INT NOT NULL,
    is_multiple TINYINT(1) DEFAULT 0,
    max_options INT DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    is_hot TINYINT(1) DEFAULT 0,
    is_anonymous TINYINT(1) DEFAULT 0,
    option_type VARCHAR(20) DEFAULT 'text',
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_creator_id (creator_id),
    INDEX idx_is_active (is_active),
    INDEX idx_is_hot (is_hot),
    INDEX idx_topic (topic),
    CONSTRAINT fk_poll_creator FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    option_image VARCHAR(500) DEFAULT NULL,
    vote_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_poll_id (poll_id),
    CONSTRAINT fk_option_poll FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS poll_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_id INT NOT NULL,
    user_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_poll_id (poll_id),
    INDEX idx_user_id (user_id),
    INDEX idx_option_id (option_id),
    UNIQUE KEY uk_poll_user_option (poll_id, user_id, option_id),
    CONSTRAINT fk_vote_poll FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_option FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================= 测试数据 =================
INSERT IGNORE INTO admins (id, username, password, email) VALUES
(1, 'admin', '$2y$10$B2paT1IHCIjZjrMXeERerehgXy3N/DlTguzPKtFUGSwsekjawvLQm', 'admin@poll-system.local');

INSERT IGNORE INTO users (id, username, email, password, gender, age, birthday) VALUES
(1, 'alice', 'alice@example.com', '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'female', 21, '2005-03-12'),
(2, 'bob',   'bob@example.com',   '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'male',   22, '2004-08-01'),
(3, 'carol', 'carol@example.com', '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'female', 20, '2006-05-20');

INSERT IGNORE INTO polls (id, title, description, topic, creator_id, is_multiple, max_options, is_active, is_hot, is_anonymous, option_type, end_time) VALUES
(1, '校园午餐满意度调查', '针对食堂新菜单的满意度收集', '校园生活', 1, 0, 1, 1, 0, 0, 'text', NULL),
(2, '最想参加的社团（可多选）', '每人最多选 2 个社团', '社团活动', 1, 1, 2, 1, 1, 0, 'text', NULL),
(3, '已结束的毕业去向调查', '用于验证已结束投票不应再接受提交', '毕业季', 2, 0, 1, 0, 0, 0, 'text', '2026-01-01 00:00:00');

INSERT IGNORE INTO poll_options (id, poll_id, option_text, vote_count) VALUES
(1, 1, '非常满意', 1),
(2, 1, '一般', 1),
(3, 1, '不满意', 0),
(4, 2, '摄影社', 1),
(5, 2, '羽毛球社', 0),
(6, 2, '辩论社', 0),
(7, 2, '志愿者协会', 0),
(8, 3, '继续读研', 0),
(9, 3, '直接就业', 0);

INSERT IGNORE INTO poll_votes (poll_id, option_id, user_id) VALUES
(1, 1, 1),
(1, 2, 2),
(2, 4, 1);
