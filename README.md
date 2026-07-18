# 在线投票系统 (Online Poll System)

一个基于 PHP + MySQL 的在线投票系统，支持用户注册登录、创建投票（文字/图片）、参与投票、查看结果，以及完整的管理员后台管理功能。

## 功能特性

### 用户功能
- ✅ 用户注册与登录
- ✅ 创建投票（支持文字投票和图片投票）
- ✅ 参与投票（支持单选和多选）
- ✅ 查看投票结果
- ✅ 匿名投票（隐藏创建者信息）
- ✅ 个人资料管理（头像上传、信息修改）
- ✅ 投票记录查询
- ✅ 主题切换（白天模式/夜晚模式）
- ✅ 专题分类浏览（10个专题）

### 管理员功能(管理员也作为用户拥有用户功能)
- ✅ 投票管理（查看、删除投票）
- ✅ 用户管理（查看、删除用户）
- ✅ 热门投票管理（设置/取消热门）
- ✅ 投票统计（系统概览、数据趋势、热度排行、活跃用户排行）

### 工程化特性
- ✅ 环境变量配置（.env文件）
- ✅ 日志系统（多级别日志、按日期分割）
- ✅ SQL注入防护（PDO预处理）
- ✅ 密码加密（password_hash）

## 技术栈

| 层级 | 技术 | 版本要求 |
|------|------|----------|
| 前端 | HTML5 + CSS3 + JavaScript | - |
| 后端 | PHP | 7.4+ |
| 数据库 | MySQL | 5.7+ |
| 服务器 | phpstudy_pro / InfinityFree | - |
| 图表 | Chart.js | 3.x |

## 环境要求

### 必需扩展
- `pdo_mysql` - 数据库连接
- `gd` - 图片处理（用于图片压缩）
- `fileinfo` - 文件类型检测

### 服务器配置
- PHP 7.4+（推荐PHP 8.0+）
- MySQL 5.7+（支持utf8mb4字符集）
- Apache/Nginx Web服务器
- 开启URL重写（mod_rewrite）

## 项目结构

```
poll-system/
├── api/                  # 后端API接口（19个）
│   ├── login.php         # 用户登录
│   ├── register.php      # 用户注册
│   ├── logout.php        # 退出登录
│   ├── check_session.php # 会话验证
│   ├── create_poll.php   # 创建投票
│   ├── get_polls.php     # 获取投票列表
│   ├── vote.php          # 参与投票
│   ├── get_poll_results.php # 获取投票结果
│   ├── get_user_votes.php   # 获取用户投票记录
│   ├── upload_avatar.php    # 上传头像
│   ├── get_avatar.php       # 获取头像
│   ├── get_profile.php      # 获取用户资料
│   ├── update_profile.php   # 更新用户资料
│   ├── admin_login.php      # 管理员登录
│   ├── admin_polls.php      # 投票管理
│   ├── admin_users.php      # 用户管理
│   ├── admin_hot_polls.php  # 热门投票管理
│   ├── admin_statistics.php # 投票统计
│   └── get_topics_stats.php # 获取专题统计
├── config/               # 配置文件
│   ├── db.php            # 数据库连接配置
│   ├── env.php           # 环境变量加载器
│   └── logger.php        # 日志工具类
├── docs/                 # 项目文档
│   ├── api.md            # API接口文档
│   └── prompt_log.md     # AI交互日志
├── logs/                 # 日志文件目录
├── sql/                  # SQL脚本
│   └── create_tables.sql # 完整建表脚本
├── static/               # 静态资源
│   ├── css/
│   │   └── style.css     # 全局样式
│   └── js/
│       ├── create_poll.js # 创建投票逻辑
│       ├── user_header.js # 用户头部逻辑
│       └── admin.js       # 管理员后台逻辑
├── views/                # 前端页面（9个）
│   ├── index.html        # 首页
│   ├── login.html        # 登录页面
│   ├── register.html     # 注册页面
│   ├── create_poll.html  # 创建投票页面
│   ├── vote.html         # 投票页面
│   ├── results.html      # 投票结果页面
│   ├── my_votes.html     # 我的投票记录
│   ├── topic_polls.html  # 专题投票页面
│   └── admin/
│       └── index.html    # 管理员后台
├── .env                  # 环境变量配置文件
├── .gitignore            # Git忽略文件
├── index.php             # 入口文件
└── README.md             # 项目说明
```

## 安装步骤

### 1. 获取项目源码

```bash
git clone https://github.com/yangjing3375/poll-system.git
cd poll-system
```

### 2. 配置服务器

- 启动 phpstudy_pro（或其他PHP开发环境）
- 创建网站，设置网站目录为 `poll-system`
- 设置端口（如：8080）
- 确保开启 `mod_rewrite` 模块

### 3. 创建数据库

```sql
CREATE DATABASE poll_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. 配置环境变量

编辑 `.env` 文件，配置数据库连接信息：

```
APP_ENV=development
APP_NAME=在线投票系统
APP_URL=http://localhost:8080

DB_HOST=localhost
DB_NAME=poll_system
DB_USER=root
DB_PASS=root

LOG_LEVEL=debug
LOG_PATH=logs/
```

### 5. 执行数据库脚本

在 phpMyAdmin 中执行 `sql/create_tables.sql` 创建所有数据表。

### 6. 初始化管理员账号

默认管理员账号已在 `sql/create_tables.sql` 中创建：
- 用户名：admin
- 密码：password

如需创建新的管理员账号，执行以下SQL：

```sql
INSERT INTO admins (username, email, password) VALUES 
('admin', 'admin@example.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye.IjzqAKL9xL5jvMFVdNJHvGCgTq/VEq');
```

密码为 `password` 的 bcrypt 哈希值。

### 7. 访问网站

#### 本地访问

在浏览器中访问：`http://localhost:8080`

#### 线上Demo

项目已部署到线上环境，访问地址：**http://poll-system.freepage.cc**

## 使用说明

### 用户注册与登录

1. 访问登录页面，点击"注册"
2. 填写用户名、邮箱和密码（至少6位）
3. 注册成功后自动登录

### 创建投票

1. 点击"创建投票"进入创建页面
2. 选择选项类型：
   - **文字投票**：只输入文字选项（至少2个）
   - **图片投票**：上传图片（自动压缩到300x300，70%质量）并输入文字描述
3. 设置投票选项
4. 可选设置：
   - 允许多选（设置最大可选数量）
   - 匿名投票（隐藏创建者信息）
   - 截止时间
5. 点击"创建投票"完成

### 参与投票

1. 在首页浏览投票列表或按专题筛选
2. 点击"去投票"按钮
3. 选择选项（单选或多选）
4. 点击"提交投票"完成

### 管理员后台

1. 使用管理员账号登录（账号：admin / 密码：password）
2. 在首页点击"后台管理"
3. 管理投票、用户、热门投票和查看统计数据

## API文档

详细的API接口文档请查看：[docs/api.md](docs/api.md)

## 开发规范

- **Git提交**: 使用 Conventional Commits 格式（如 `feat: 添加功能名称`）
- **数据库**: 使用PDO预处理语句，防止SQL注入
- **前端**: 使用原生JavaScript，不依赖框架
- **安全**: 密码使用password_hash加密，日志不记录敏感信息

## 注意事项

- 确保 PHP 版本 >= 7.4
- 确保 MySQL 版本 >= 5.7
- 确保 PHP 扩展 `pdo_mysql`、`gd`、`fileinfo` 已启用
- 图片投票会自动压缩（300x300像素，70%质量）
- 日志文件保存在 `logs/` 目录，按日期分割
- 项目使用Session + Cookie进行身份认证

## 项目演示

### 线上部署地址

**http://poll-system.freepage.cc**

### 测试账号

| 账号类型 | 用户名 | 密码 |
|----------|--------|------|
| 管理员 | admin | password |
| 普通用户 | 嘻嘻 | 123456 |

### ⚠️ 重要提示

线上部署的系统所有功能均已测试通过，可正常使用。但由于服务器资源限制，部分功能加载可能会比较缓慢，需要等待一段时间才能显示。如果无法等待，请查看演示视频。

## License

MIT License