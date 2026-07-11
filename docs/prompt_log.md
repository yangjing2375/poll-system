# Prompt 日志

本文档记录项目开发过程中使用 AI 工具的 Prompt 及返回结果。

---

## 已完成功能

### 1. 项目初始化与用户注册登录功能

#### 1.1 创建项目目录结构

**Prompt**:
```
请帮我创建一个在线投票系统的项目目录结构，使用 PHP + MySQL 技术栈。
```

**AI 返回**:
创建了以下目录结构：
- `api/` - 后端API接口
- `config/` - 配置文件
- `sql/` - 数据库脚本
- `static/` - 静态资源（CSS、JS）
- `views/` - 前端页面

**对应文件**:
- 创建了所有目录

---

#### 1.2 创建数据库配置文件

**Prompt**:
```
请创建数据库连接配置文件，使用PDO连接MySQL。
```

**AI 返回**:
创建了 `config/db.php`，包含数据库连接信息和 `getDB()` 函数。

**对应文件**:
- `config/db.php`

---

#### 1.3 创建用户表SQL脚本

**Prompt**:
```
请创建用户表的SQL脚本，包含id、username、email、password字段。
```

**AI 返回**:
创建了 `sql/create_users_table.sql`。

**对应文件**:
- `sql/create_users_table.sql`

---

#### 1.4 创建用户注册接口

**Prompt**:
```
请创建用户注册的后端API，使用PHP PDO连接MySQL数据库。
要求：
1. 接收用户名、邮箱、密码
2. 验证用户名和邮箱是否唯一
3. 密码使用password_hash加密存储
4. 返回JSON格式响应
```

**AI 返回**:
创建了 `api/register.php`，包含完整的注册逻辑和错误处理。

**对应文件**:
- `api/register.php`

---

#### 1.5 创建用户登录接口

**Prompt**:
```
请创建用户登录的后端API，使用PHP Session保持登录状态。
要求：
1. 验证用户名和密码
2. 使用password_verify验证密码
3. 设置Session存储用户信息
4. 返回JSON格式响应
```

**AI 返回**:
创建了 `api/login.php`，实现登录验证和Session管理。

**对应文件**:
- `api/login.php`

---

#### 1.6 创建会话验证接口

**Prompt**:
```
请创建检查登录状态的API，用于前端验证用户是否已登录。
```

**AI 返回**:
创建了 `api/check_session.php`。

**对应文件**:
- `api/check_session.php`

---

#### 1.7 创建前端注册页面

**Prompt**:
```
请创建用户注册的前端页面，包含美观的表单设计。
要求：
1. 用户名输入框
2. 邮箱输入框
3. 密码输入框
4. 使用fetch发送POST请求
5. 显示成功或失败提示
```

**AI 返回**:
创建了 `views/register.html` 和 `static/js/register.js`。

**对应文件**:
- `views/register.html`
- `static/js/register.js`

---

#### 1.8 创建前端登录页面

**Prompt**:
```
请创建用户登录的前端页面，包含美观的表单设计。
要求：
1. 用户名输入框
2. 密码输入框
3. 使用fetch发送POST请求
4. 显示成功或失败提示
5. 登录成功后跳转到首页
```

**AI 返回**:
创建了 `views/login.html` 和 `static/js/login.js`。

**对应文件**:
- `views/login.html`
- `static/js/login.js`

---

#### 1.9 创建首页（仪表盘）

**Prompt**:
```
请创建登录成功后的首页，显示欢迎信息和功能入口。
```

**AI 返回**:
创建了 `views/index.html`。

**对应文件**:
- `views/index.html`

---

#### 1.10 创建样式文件

**Prompt**:
```
请创建注册和登录页面的CSS样式，使用渐变背景和现代设计风格。
```

**AI 返回**:
创建了 `static/css/style.css`。

**对应文件**:
- `static/css/style.css`

---

#### 1.11 修复CORS和Session问题

**Prompt**:
```
登录功能存在问题，Session无法保持。请修复CORS设置和前端fetch请求。
要求：
1. 设置Access-Control-Allow-Credentials为true
2. 设置具体的Access-Control-Allow-Origin
3. 前端fetch添加credentials: 'include'
```

**AI 返回**:
修改了 `config/db.php`、`api/register.php`、`api/login.php` 和前端JS文件。

**对应文件**:
- `config/db.php`
- `api/register.php`
- `api/login.php`
- `static/js/login.js`
- `static/js/register.js`

---

#### 1.12 修复PHP扩展问题

**Prompt**:
```
数据库连接失败，提示could not find driver。请检查并修复PHP的pdo_mysql扩展配置。
```

**AI 返回**:
创建了完整的 `php.ini` 配置文件，启用了 `pdo_mysql` 扩展。

**对应文件**:
- 配置文件在 phpstudy_pro 目录中

---

## 待实现功能

### 2. 创建投票功能（待开发）

### 3. 参与投票功能（待开发）

### 4. 查看投票结果功能（待开发）
