# AI 交互日志（Prompt Log）

## 概述

本项目在开发过程中大量使用了 AI（Trae）辅助编程，以下是主要的交互记录，包含每条 Prompt、AI 返回的关键代码/建议，以及对应的功能实现和修改文件。

> **说明**：为保持文档可读性，AI 返回的代码仅保留关键片段，完整输出见 Git 提交历史。每条 Prompt 对应解决了项目中的某个具体功能或问题，可通过文档中的"对应文件"快速定位到相关代码。

---

## 1. 用户认证系统

### Prompt 1: 用户注册与登录功能

**Prompt**:
```
请帮我实现用户注册和登录功能，需要：
1. 用户注册：验证用户名和邮箱唯一性，密码加密存储
2. 用户登录：支持普通用户和管理员登录，设置Session
3. 退出登录：销毁Session和Cookie
4. 会话验证：检查登录状态
```

**AI 返回关键代码**:

```php
// api/register.php
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];

if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => '请填写完整信息']);
    exit;
}

$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['status' => 'error', 'message' => '用户名已存在']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->execute([$username, $email, $hashedPassword]);
```

**对应文件**:
- `api/register.php` - 用户注册接口
- `api/login.php` - 用户登录接口
- `api/logout.php` - 退出登录接口
- `api/check_session.php` - 会话验证接口

---

## 2. 投票创建功能

### Prompt 2: 创建投票功能

**Prompt**:
```
请帮我实现创建投票功能，需要：
1. 登录验证
2. 支持文字投票和图片投票两种类型
3. 支持单选和多选模式（多选时设置最大可选数量）
4. 支持匿名投票（匿名时不显示创建者用户名）
5. 支持设置截止时间
6. 图片自动压缩（300x300像素，70%质量）
```

**AI 返回关键代码**:

```php
// api/create_poll.php
$optionType = $data['option_type'] ?? 'text';
$isAnonymous = $data['is_anonymous'] ?? 0;

$stmt = $db->prepare("INSERT INTO polls (title, description, topic, creator_id, is_multiple, max_options, end_time, is_active, is_anonymous, option_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $data['title'],
    $data['description'] ?? '',
    $data['topic'] ?? '',
    $creatorId,
    $data['is_multiple'] ?? 0,
    $data['max_options'] ?? 1,
    $data['end_time'] ?? null,
    1,
    $isAnonymous,
    $optionType
]);
```

```javascript
// static/js/create_poll.js - 图片压缩
function compressImage(file, maxWidth = 300, maxHeight = 300, quality = 0.7) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                if (width > maxWidth) {
                    height = (height * maxWidth) / width;
                    width = maxWidth;
                }
                if (height > maxHeight) {
                    width = (width * maxHeight) / height;
                    height = maxHeight;
                }
                
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                resolve(canvas.toDataURL('image/jpeg', quality));
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}
```

**对应文件**:
- `api/create_poll.php` - 创建投票接口
- `static/js/create_poll.js` - 创建投票前端逻辑
- `views/create_poll.html` - 创建投票页面

---

## 3. 投票参与功能

### Prompt 3: 投票功能实现

**Prompt**:
```
请帮我实现投票功能，需要：
1. 登录验证
2. 检查是否已投票（同一用户同一投票只能投一次）
3. 检查投票是否有效（是否在有效期内）
4. 多选时检查是否超过最大可选数量
5. 使用事务保护投票操作的原子性
6. 插入投票记录到poll_votes表，更新选项的vote_count
```

**AI 返回关键代码**:

```php
// api/vote.php
try {
    $db->beginTransaction();
    
    foreach ($option_ids as $option_id) {
        $stmt = $db->prepare("INSERT INTO poll_votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$poll_id, $option_id, $user_id]);

        $stmt = $db->prepare("UPDATE poll_options SET vote_count = vote_count + 1 WHERE id = ?");
        $stmt->execute([$option_id]);
    }
    
    $db->commit();
    echo json_encode(['status' => 'success', 'message' => '投票成功']);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['status' => 'error', 'message' => '服务器错误']);
}
```

**对应文件**:
- `api/vote.php` - 投票接口

---

## 4. 管理员后台

### Prompt 4: 管理员后台管理功能

**Prompt**:
```
请帮我实现管理员后台功能，需要：
1. 投票管理：查看、删除投票（级联删除选项和投票记录）
2. 用户管理：查看、删除用户（级联删除投票记录）
3. 热门投票管理：切换投票热门状态、批量设置/取消热门投票
4. 投票统计：系统概览、7日数据趋势、投票热度排行、活跃用户排行、专题统计
```

**AI 返回关键代码**:

```php
// api/admin_statistics.php
$stats = [
    'overview' => [
        'total_users' => $totalUsers,
        'total_polls' => $totalPolls,
        'total_votes' => $totalVotes,
        'active_polls' => $activePolls,
        'hot_polls' => $hotPolls,
        'today_registrations' => $todayRegistrations,
        'today_polls' => $todayPolls,
        'today_votes' => $todayVotes
    ],
    'trend' => $trendData,
    'hot_polls' => $hotPollsData,
    'active_users' => $activeUsersData,
    'topics' => $topicsData,
    'recent_votes' => $recentVotesData
];

echo json_encode(['success' => true, 'data' => $stats]);
```

**对应文件**:
- `api/admin_polls.php` - 投票管理接口
- `api/admin_users.php` - 用户管理接口
- `api/admin_hot_polls.php` - 热门投票管理接口
- `api/admin_statistics.php` - 投票统计接口
- `views/admin/index.html` - 管理员后台页面

---

## 5. 主题切换功能

### Prompt 5: 白天/夜晚模式切换

**Prompt**:
```
请帮我实现白天/夜晚模式切换功能，需要：
1. 在首页和管理员后台添加设置按钮（⚙️）
2. 支持☀️白天模式和🌙夜晚模式切换
3. 主题设置保存在localStorage，首页和后台管理共享
4. 所有页面元素适配深色主题
5. 默认情况下为白天模式
```

**AI 返回关键代码**:

```javascript
// views/index.html - 主题切换逻辑
function initTheme() {
    const savedTheme = localStorage.getItem('theme');
    const theme = savedTheme || 'light';
    document.body.className = theme;
}

function setTheme(theme) {
    localStorage.setItem('theme', theme);
    document.body.className = theme;
}
```

```css
/* 夜晚模式样式 */
body.night {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

body.night .card {
    background: #16213e;
    border-color: #2d3436;
}

body.night h1, body.night h2, body.night h3, body.night p {
    color: #ffffff;
}
```

**对应文件**:
- `views/index.html` - 首页主题切换
- `views/admin/index.html` - 管理员后台主题切换

---

## 6. 环境变量与日志功能

### Prompt 6: 环境变量和日志功能

**Prompt**:
```
请帮我实现环境变量和日志功能，需要：
1. 创建.env文件管理数据库连接、应用配置和日志级别
2. 创建Env类支持环境变量加载和获取
3. 创建Logger类支持4种日志级别（debug/info/warning/error）
4. 日志按日期分割存储在logs/目录
5. 支持上下文信息（JSON格式）
6. 在登录和创建投票操作中添加日志记录
```

**AI 返回关键代码**:

```php
// config/env.php
class Env {
    private static $vars = [];
    
    public static function load($path = '.env') {
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    self::$vars[trim($key)] = trim($value);
                }
            }
        }
    }
    
    public static function get($key, $default = null) {
        return isset(self::$vars[$key]) ? self::$vars[$key] : $default;
    }
}
```

```php
// config/logger.php
class Logger {
    public static function debug($message, $context = []) {
        self::log('debug', $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::log('info', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('warning', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('error', $message, $context);
    }
    
    private static function log($level, $message, $context) {
        $logPath = Env::get('LOG_PATH', 'logs/');
        $logLevel = Env::get('LOG_LEVEL', 'debug');
        
        $levels = ['debug', 'info', 'warning', 'error'];
        $currentLevelIndex = array_search($logLevel, $levels);
        $messageLevelIndex = array_search($level, $levels);
        
        if ($messageLevelIndex < $currentLevelIndex) return;
        
        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logLine = "[$time] [$level] $message$contextStr\n";
        
        if (!file_exists($logPath)) mkdir($logPath, 0755, true);
        file_put_contents($logPath . "app_$date.log", $logLine, FILE_APPEND);
    }
}
```

**对应文件**:
- `.env` - 环境变量配置文件
- `config/env.php` - 环境变量加载器
- `config/logger.php` - 日志工具类
- `config/db.php` - 使用环境变量和日志
- `api/login.php` - 登录操作日志
- `api/create_poll.php` - 创建投票日志

---

## 7. 代码审查

### Prompt 7: Code Review

**Prompt**:
```
请帮我对整个项目进行一次Code Review，找出代码中的问题并给出优化建议。重点关注：
1. 安全性（SQL注入、敏感信息泄露）
2. 代码规范（错误处理、代码复用）
3. 性能（N+1查询问题）
4. 可维护性（响应格式、文档）
```

**AI 返回关键建议**:

| 问题 | 建议 | 严重性 |
|------|------|--------|
| admin_users.php错误响应泄露敏感信息 | 移除响应中的session字段 | 高 |
| vote.php投票操作缺少事务保护 | 添加beginTransaction/commit/rollBack | 高 |
| get_polls.php存在N+1查询问题 | 使用JOIN查询或批量查询 | 中 |
| API响应格式不统一 | 统一响应格式为{status, message, data} | 低 |
| logger.php缺少日志轮转机制 | 添加定期清理过期日志的方法 | 低 |

**对应文件**:
- `docs/code_review_report.md` - Code Review报告

---

## 8. 文档生成

### Prompt 8: 项目文档生成

**Prompt**:
```
请帮我完善项目文档，需要：
1. 更新README.md，包含项目介绍、技术栈、安装步骤、使用说明、API文档链接
2. 更新API文档（docs/api.md），包含所有18个接口的详细说明、请求参数、响应示例
3. 创建Prompt日志（docs/prompt_log.md），记录开发过程中使用的主要Prompt和AI回复
```

**AI 返回**:
生成了完整的项目文档，包括：
- README.md - 项目说明文档
- docs/api.md - API接口文档（18个接口完整说明）
- docs/prompt_log.md - AI交互日志

**对应文件**:
- `README.md` - 项目说明文档
- `docs/api.md` - API接口文档
- `docs/prompt_log.md` - AI交互日志

---

## 9. Bug修复

### Prompt 9: 创建投票失败问题

**Prompt**:
```
创建投票时显示创建失败，错误信息是"Data too long for column 'option_image'"，请帮我修复这个问题。
```

**AI 返回**:
```sql
-- 修改option_image字段类型从TEXT改为MEDIUMTEXT
ALTER TABLE poll_options MODIFY COLUMN option_image MEDIUMTEXT;
```

```javascript
// 前端添加图片压缩逻辑
function compressImage(file) {
    return new Promise((resolve) => {
        // 压缩到300x300，质量70%
        // ...
    });
}
```

**对应文件**:
- `api/create_poll.php` - 修复选项验证逻辑
- `static/js/create_poll.js` - 添加图片压缩
- 数据库迁移脚本

---

## 10. 性能优化

### Prompt 10: 活跃用户排行逻辑优化

**Prompt**:
```
后台管理的投票统计里面的活跃用户排行有点不准确，不应该参与投票越多代表越活跃吗？请帮我看看逻辑。
```

**AI 返回**:
```php
// 修改前 - 按投票次数排序
ORDER BY vote_count DESC

// 修改后 - 按参与投票数量排序，票数作为次要排序
ORDER BY poll_count DESC, vote_count DESC
```

**对应文件**:
- `api/admin_statistics.php` - 活跃用户排行逻辑优化

---

## 11. 线上部署与文档更新

### Prompt 11: 线上部署问题排查

**Prompt**:
```
为什么在线打开我的项目创建投票显示创建失败？
```

**AI 返回关键建议**:

1. **数据库字段缺失问题**：线上数据库缺少以下字段，需要执行迁移脚本：
   - `polls.topic` - 投票专题
   - `polls.is_hot` - 是否热门
   - `polls.is_anonymous` - 是否匿名
   - `polls.option_type` - 选项类型
   - `poll_options.option_image` - 选项图片

2. **外键约束问题**：`polls` 表的外键约束只允许 `users` 表的用户创建投票，需要移除或修改。

3. **图片字段类型问题**：`option_image` 字段类型为 `VARCHAR(500)`，Base64 图片数据会被截断，需要改为 `MEDIUMTEXT`。

**对应文件**:
- 数据库迁移脚本执行
- `api/create_poll.php` - 已支持所有字段

---

### Prompt 12: 图片投票显示问题

**Prompt**:
```
怎么我在线上打开的系统创建的图片投票，然后去投票投票选项里面看不到图片？
```

**AI 返回关键建议**:

1. **数据库字段类型问题**：`option_image` 字段类型太小，修改为 `MEDIUMTEXT`。
2. **专题页面缺少图片显示逻辑**：`views/topic_polls.html` 的投票模态框没有处理图片选项。

**修复代码**:
```javascript
// views/topic_polls.html - 添加图片显示逻辑
const optionType = data.data.poll.option_type || 'text';
if (optionType === 'image' && option.option_image) {
    optionContent = `<img src="${option.option_image}" ...>`;
}
```

**对应文件**:
- `views/topic_polls.html` - 添加图片选项显示逻辑
- 数据库 `poll_options.option_image` 字段类型修改

---

### Prompt 13: 文档更新

**Prompt**:
```
我觉得我现在得根据我的项目重新更改README.md、prompt_log.md、API文档这三个文档的内容，我现在已经把项目部署到线上了，然后管理员的账号现在是admin密码是password,我觉得我还可以提供一个普通用户的账号为嘻嘻,密码为123456，然后在文档里面要做一个提示就是我测试了线上部署访问URL打开里面的系统所有功能都是正常可用的但是可能系统太大了，有些功能加载很缓慢，要等很久才显示，希望能多等待一会，不能等待请看演示视频。
```

**AI 返回**:
更新了三个文档：
- `README.md` - 添加线上部署地址、测试账号和加载提示
- `docs/api.md` - 更新API地址为线上地址
- `docs/prompt_log.md` - 添加线上部署和文档更新的交互记录

**对应文件**:
- `README.md` - 项目说明文档
- `docs/api.md` - API接口文档
- `docs/prompt_log.md` - AI交互日志

---

## 总结

本项目通过AI辅助编程实现了以下核心功能：

| 功能模块 | AI贡献 | 涉及文件数 |
|----------|--------|------------|
| 用户认证系统 | 注册、登录、退出、会话验证 | 4个API + 2个页面 |
| 投票创建功能 | 文字/图片投票、单选/多选、匿名投票 | 1个API + 1个前端页面 + 1个JS文件 |
| 投票参与功能 | 投票验证、事务保护 | 1个API |
| 管理员后台 | 投票管理、用户管理、热门管理、统计 | 4个API + 1个页面 |
| 主题切换 | 白天/夜晚模式、localStorage记忆 | 2个页面 |
| 环境变量与日志 | .env配置、日志系统 | 3个配置文件 + 2个API |
| 文档生成 | README、API文档、Prompt日志 | 3个文档 |
| Bug修复 | 图片压缩、字段长度、逻辑修复 | 多个文件 |
| 代码审查 | 安全性、性能、规范建议 | 1个报告 |

AI辅助编程大大提高了开发效率，特别是在：
1. 快速生成基础代码框架
2. 提供最佳实践和安全建议
3. 解决复杂的逻辑问题
4. 生成完整的项目文档

同时，开发者在整个过程中保持了对代码的掌控，进行了必要的审查和优化，确保项目质量符合要求。