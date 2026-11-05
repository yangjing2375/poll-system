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
(3, 'carol', 'carol@example.com', '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'female', 20, '2006-05-20'),
(4, 'david', 'david@example.com', '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'male',   23, '2003-11-07'),
(5, 'emma',  'emma@example.com',  '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'female', 19, '2007-02-18'),
(6, 'frank', 'frank@example.com', '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'male',   24, '2002-09-25'),
(7, 'grace', 'grace@example.com', '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'female', 22, '2004-04-03'),
(8, 'henry', 'henry@example.com', '$2y$10$rrNu2HMTBZDZE2l5D9YgQOHROi8oWwcKWXg1Lod/alWZF3ZHNJQRq', 'male',   21, '2005-07-15');

INSERT IGNORE INTO polls (id, title, description, topic, creator_id, is_multiple, max_options, is_active, is_hot, is_anonymous, option_type, end_time) VALUES
(1, '校园午餐满意度调查', '针对食堂新菜单的满意度收集', '校园生活', 1, 0, 1, 1, 0, 0, 'text', NULL),
(2, '最想参加的社团（可多选）', '每人最多选 2 个社团', '社团活动', 1, 1, 2, 1, 1, 0, 'text', NULL),
(3, '已结束的毕业去向调查', '用于验证已结束投票不应再接受提交', '毕业季', 2, 0, 1, 0, 0, 0, 'text', '2026-01-01 00:00:00'),
(4, '选课时你最看重什么', '校园专题调查，帮学弟学妹避坑', '校园专题', 3, 0, 1, 1, 0, 0, 'text', NULL),
(5, '学校周边最值得推荐的小吃', '食堂之外的第二选择', '美食专题', 2, 0, 1, 1, 0, 0, 'text', NULL),
(6, '实习你更看重哪一点', '找实习时最在意的因素', '职场专题', 4, 0, 1, 1, 0, 0, 'text', NULL),
(7, '本月最期待的院线电影', '按预告片投票', '影视专题', 5, 0, 1, 1, 0, 0, 'text', NULL),
(8, '周末最常做的运动（可多选）', '每人最多选 2 项', '运动&出行', 6, 1, 2, 1, 0, 0, 'text', NULL),
(9, '假期出行你更倾向哪种方式', '长途出行偏好调查', '旅游专题', 7, 0, 1, 1, 0, 0, 'text', NULL),
(10, '最常玩的游戏类型（可多选）', '每人最多选 2 项', '游戏专题', 8, 1, 2, 1, 0, 0, 'text', NULL),
(11, '选车时你最看重什么', '购车关注点排序', '汽车专题', 1, 0, 1, 1, 0, 0, 'text', NULL),
(12, '和父母沟通的频率', '匿名投票，仅统计票数', '家庭&情感', 2, 0, 1, 1, 0, 1, 'text', NULL),
(13, '你更喜欢哪种放松方式（可多选）', '每人最多选 2 项', '娱乐专题', 3, 1, 2, 1, 0, 0, 'text', NULL);

INSERT IGNORE INTO poll_options (id, poll_id, option_text, vote_count) VALUES
(1, 1, '非常满意', 3),
(2, 1, '一般', 3),
(3, 1, '不满意', 0),
(4, 2, '摄影社', 2),
(5, 2, '羽毛球社', 4),
(6, 2, '辩论社', 3),
(7, 2, '志愿者协会', 1),
(8, 3, '继续读研', 1),
(9, 3, '直接就业', 1),
(10, 4, '课程难度', 2),
(11, 4, '老师给分', 3),
(12, 4, '上课时间', 1),
(13, 4, '同学口碑', 1),
(14, 5, '麻辣烫', 3),
(15, 5, '煎饼果子', 2),
(16, 5, '手抓饼', 1),
(17, 5, '奶茶', 2),
(18, 6, '薪资待遇', 3),
(19, 6, '成长空间', 4),
(20, 6, '通勤距离', 1),
(21, 6, '团队氛围', 2),
(22, 7, '科幻大片《星际回声》', 4),
(23, 7, '动画电影《夏日航线》', 2),
(24, 7, '悬疑片《第七个证人》', 1),
(25, 8, '跑步', 3),
(26, 8, '篮球', 2),
(27, 8, '羽毛球', 4),
(28, 8, '骑行', 1),
(29, 9, '高铁', 4),
(30, 9, '飞机', 2),
(31, 9, '自驾', 1),
(32, 9, '火车硬卧', 1),
(33, 10, 'MOBA 类', 3),
(34, 10, '沙盒生存类', 2),
(35, 10, '射击类', 2),
(36, 10, '单机剧情类', 3),
(37, 11, '油耗', 2),
(38, 11, '空间', 3),
(39, 11, '安全配置', 4),
(40, 11, '外观', 1),
(41, 12, '每天', 2),
(42, 12, '每周几次', 4),
(43, 12, '每月几次', 2),
(44, 12, '很少', 1),
(45, 13, '刷剧', 3),
(46, 13, '打游戏', 2),
(47, 13, '户外散步', 4),
(48, 13, '和朋友聚餐', 3);

INSERT IGNORE INTO poll_votes (poll_id, option_id, user_id) VALUES
(1, 1, 1), (1, 2, 2), (1, 1, 3), (1, 2, 4), (1, 1, 5), (1, 2, 6),
(2, 4, 1), (2, 5, 2), (2, 6, 2), (2, 5, 3), (2, 6, 4), (2, 7, 4),
(2, 4, 5), (2, 5, 5), (2, 5, 6), (2, 6, 7), (2, 7, 8),
(3, 8, 1), (3, 9, 2),
(4, 10, 1), (4, 11, 2), (4, 11, 3), (4, 12, 4), (4, 13, 5), (4, 11, 6),
(5, 14, 1), (5, 15, 2), (5, 14, 3), (5, 17, 4), (5, 16, 5), (5, 14, 6), (5, 15, 7), (5, 17, 8),
(6, 18, 1), (6, 19, 2), (6, 19, 3), (6, 20, 4), (6, 21, 5), (6, 18, 6), (6, 19, 7), (6, 18, 8),
(7, 22, 1), (7, 23, 2), (7, 22, 3), (7, 24, 4), (7, 22, 5), (7, 23, 6), (7, 22, 7),
(8, 25, 1), (8, 27, 2), (8, 25, 2), (8, 27, 3), (8, 26, 4), (8, 27, 5), (8, 25, 6), (8, 26, 7), (8, 27, 8),
(9, 29, 1), (9, 30, 2), (9, 29, 3), (9, 31, 4), (9, 29, 5), (9, 29, 6), (9, 32, 7), (9, 30, 8),
(10, 33, 1), (10, 36, 2), (10, 33, 3), (10, 34, 4), (10, 35, 5), (10, 36, 6), (10, 33, 7), (10, 34, 8),
(11, 37, 1), (11, 39, 2), (11, 38, 3), (11, 39, 4), (11, 38, 5), (11, 39, 6), (11, 38, 7), (11, 40, 8),
(12, 41, 1), (12, 42, 2), (12, 42, 3), (12, 43, 4), (12, 44, 5), (12, 43, 6), (12, 42, 7), (12, 41, 8),
(13, 45, 1), (13, 47, 2), (13, 45, 3), (13, 48, 4), (13, 47, 5), (13, 45, 6), (13, 47, 7), (13, 48, 8), (13, 46, 5);

-- 保证选项票数与明细一致
UPDATE poll_options po
SET vote_count = (SELECT COUNT(*) FROM poll_votes pv WHERE pv.option_id = po.id);
