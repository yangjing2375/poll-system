# 在线投票系统 Poll System

一个支持多专题、多选项与结果统计的在线投票平台：普通用户可以注册登录、浏览与参与投票、查看结果、管理个人信息；管理员可以创建/编辑/下架投票、管理用户、查看数据统计。

---

## 一、项目简介

| 项 | 说明 |
| --- | --- |
| 定位 | 校园/社区场景的在线投票与问卷工具 |
| 角色 | 游客、普通用户、管理员 |
| 核心流程 | 管理员创建投票 → 用户浏览并投票 → 实时统计结果 → 管理员查看报表 |
| 仓库 | `github.com/yangjing2375/poll-system` |

主要功能：

- 认证：注册、登录、退出、会话检测（普通用户与管理员两套入口）
- 投票：列表（关键词 / 专题 / 热门筛选）、详情、单选与多选提交、结果统计、我的投票记录
- 管理：投票增删改查、热门标记、用户列表与删除、数据看板（概览 / 专题 / 排行 / 近期投票 / 趋势）
- 个人中心：资料查看与修改、头像上传

---

## 二、技术栈

| 层 | 技术 |
| --- | --- |
| 前端 | HTML5 + CSS3 + 原生 JavaScript（`fetch` 调用接口） |
| 后端 | PHP 8.x（PDO 预处理、Session 会话、事务） |
| 数据库 | MySQL 5.7 / 8.0（InnoDB、外键、唯一索引） |
| 依赖管理 | Composer（仅测试依赖） |
| 测试 | Postman（接口）、PHPUnit（单元/集成）、GitHub Actions（CI） |
| 运行环境 | Apache（phpStudy）或 PHP 内置服务器 |

---

## 三、运行方式

### 1. 准备

- PHP 8.0 及以上（需 `pdo_mysql`、`gd` 扩展）
- MySQL 5.7 / 8.0

### 2. 建库并导入结构与演示数据

```bash
mysql -uroot -proot < sql/schema.sql
```

脚本会创建 `poll_system` 库、5 张表与演示数据（3 个投票、3 个测试用户、1 个管理员）。

### 3. 配置数据库连接

复制 `.env.example` 为 `.env`，按本机情况修改主机、库名、账号密码；不创建 `.env` 时使用默认值 `127.0.0.1 / poll_system / root / root`。

### 4. 启动

方式一（Apache，phpStudy）：把项目目录放到站点根目录下，访问

```
http://localhost:8080/poll-system/
```

方式二（PHP 内置服务器）：

```bash
php -S 127.0.0.1:8080 -t .
```

### 5. 测试账号

| 角色 | 账号 | 密码 |
| --- | --- | --- |
| 管理员 | admin | Admin@123 |
| 普通用户 | alice | 123456 |
| 普通用户 | bob | 123456 |
| 普通用户 | carol | 123456 |

> 演示数据中的密码使用 `password_hash()` 生成的 bcrypt 哈希，不是明文存储。

### 6. 运行测试

```bash
# 接口自动化（站点需已启动，集合内 baseUrl 默认 http://localhost:8080/poll-system）
newman run postman/PollSystem.postman_collection.json -e postman/PollSystem.postman_environment.json

# PHPUnit（站点运行在 127.0.0.1:8083 时）
composer install
vendor/bin/phpunit --testdox
```

---

## 四、我负责的部分

个人独立完成全部开发与测试工作：

- 数据库设计：用户/管理员/投票/选项/投票记录 5 张表，含外键与唯一约束
- 后端接口：19 个 PHP 接口，统一 JSON 返回（`status` / `success` 两种结构，见接口一览），PDO 预处理防注入，Session 鉴权
- 前端页面：投票列表、详情、创建、个人中心、管理后台
- 测试：84 条用例设计、13 个缺陷定位与修复（11 个已关闭）、Postman 集合与 PHPUnit 自动化、CI 接入

---

## 五、目录结构

```
poll-system
├── api/                 19 个接口
├── config/              数据库连接、环境变量、日志
├── static/              样式与前端脚本
├── views/               页面（用户端、管理端）
├── docs/                测试用例、缺陷清单、测试报告、接口说明
├── postman/             Postman 集合与环境
├── sql/                 建表脚本、迁移脚本、初始化数据
├── tests/               PHPUnit 用例
├── screenshots/         界面与执行结果截图
├── index.php            入口
└── .github/workflows/   CI 配置
```

---

## 六、测试资料

| 文件 | 内容 |
| --- | --- |
| `docs/测试用例.xlsx` | 84 条用例：编号、模块、标题、前置条件、步骤、预期、实际、结果 |
| `docs/缺陷清单.xlsx` | 13 个缺陷：复现步骤、严重程度、优先级、状态、修复说明 |
| `docs/测试报告.md` | 测试范围、用例统计（81 通过 / 3 失败）、缺陷分布、遗留风险、结论 |
| `docs/测试数据.md` | 基线数据、边界值与异常数据设计 |
| `docs/截图清单.md` | 截图目录与对应材料 |
| `postman/PollSystem.postman_collection.json` | 32 个请求、64 条断言，按业务链路执行 |
| `tests/` | PHPUnit 44 个用例、107 条断言，CI 自动执行 |
| `docs/GitHub-Issues缺陷单.md` | 13 条缺陷 Issue 的标题、标签与正文 |

---

## 七、接口一览

返回结构：认证、投票、管理、个人中心类接口使用 `{"status":"success|error","message":"...","data":...}`；列表/结果/创建类接口使用 `{"success":true|false,...}`，两类结构在 `docs/api.md` 中有完整示例。

| 模块 | 方法 | 地址 | 说明 |
| --- | --- | --- | --- |
| 认证 | POST | `/api/register.php` | 注册 |
| 认证 | POST | `/api/login.php` | 登录（普通用户/管理员共用） |
| 认证 | POST | `/api/logout.php` | 退出 |
| 认证 | GET | `/api/check_session.php` | 会话检测 |
| 认证 | POST | `/api/admin_login.php` | 管理端登录 |
| 投票 | GET | `/api/get_polls.php?keyword=&topic=&is_hot=` | 投票列表、筛选与是否已投 |
| 投票 | GET | `/api/get_poll_results.php?poll_id=` | 选项票数与投票明细 |
| 投票 | POST | `/api/vote.php` | 提交投票（单选/多选） |
| 投票 | GET | `/api/get_user_votes.php` | 我的投票记录 |
| 投票 | POST | `/api/create_poll.php` | 用户端创建投票 |
| 管理 | GET | `/api/admin_polls.php?page=&limit=&search=&status=` | 管理端投票列表 |
| 管理 | POST | `/api/admin_polls.php` | `action=create/edit/delete/toggle_active` |
| 管理 | GET | `/api/admin_users.php?page=&limit=&search=` | 管理端用户列表 |
| 管理 | POST | `/api/admin_users.php` | `action=add/delete` |
| 管理 | POST | `/api/admin_hot_polls.php` | 设置/取消热门 |
| 统计 | GET | `/api/admin_statistics.php?type=` | 概览、专题、排行、近期投票、趋势 |
| 统计 | GET | `/api/get_topics_stats.php` | 专题参与统计 |
| 个人 | GET | `/api/get_profile.php` | 资料查看 |
| 个人 | POST | `/api/update_profile.php` | 资料修改 |
| 个人 | POST | `/api/upload_avatar.php` | 头像上传 |
| 个人 | GET | `/api/get_avatar.php` | 头像输出 |

---

## 八、数据库表

| 表 | 说明 |
| --- | --- |
| `users` | 普通用户，含资料与头像 |
| `admins` | 管理员 |
| `polls` | 投票主题，含专题、是否多选、最大可选数、上下线、匿名、结束时间 |
| `poll_options` | 投票选项与票数 |
| `poll_votes` | 投票记录，唯一约束 `(poll_id, user_id, option_id)` 保证同一选项不重复计票，接口层再校验「一票一投」 |

---

## 九、常见问题

1. 接口返回「请先登录」：Session 未建立，先调用 `login.php` 并保持同一个 Cookie。
2. 中文乱码：数据库与连接均使用 `utf8mb4`，导入时加 `--default-character-set=utf8mb4`。
3. 页面样式丢失：确认通过站点根路径访问（带 `/poll-system/` 前缀），而不是直接打开 `index.html`。
4. 上传头像失败：头像以二进制形式存入 `users.avatar_data` / `admins.avatar_data`，确认 `php.ini` 的 `upload_max_filesize`、`post_max_size` 不小于 2MB。
