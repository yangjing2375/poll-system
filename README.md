# 在线投票系统 (Online Poll System)

一个基于 PHP + MySQL 的在线投票系统，支持用户注册登录、创建投票、参与投票和查看结果。

## 技术栈

- **前端**: HTML5 + CSS3 + JavaScript
- **后端**: PHP 7.4+
- **数据库**: MySQL 5.7+
- **服务器**: phpstudy_pro

## 项目结构

```
poll-system/
├── api/              # 后端API接口
│   ├── login.php     # 用户登录
│   ├── register.php  # 用户注册
│   └── check_session.php  # 会话验证
├── config/           # 配置文件
│   └── db.php        # 数据库连接配置
├── sql/              # SQL脚本
│   └── create_users_table.sql  # 用户表创建脚本
├── static/           # 静态资源
│   ├── css/          # CSS样式
│   └── js/           # JavaScript文件
├── views/            # 前端页面
│   ├── index.html    # 首页/仪表盘
│   ├── login.html    # 登录页面
│   └── register.html # 注册页面
├── index.php         # 入口文件
├── .gitignore        # Git忽略文件
└── README.md         # 项目说明
```

## 安装步骤

1. **配置 phpstudy_pro**
   - 启动 phpstudy_pro
   - 创建网站，设置网站目录为 `poll-system`
   - 设置域名（如：`http://localhost:80`）

2. **创建数据库**
   - 打开 phpMyAdmin
   - 创建数据库 `poll_system`
   - 执行 `sql/create_users_table.sql` 脚本

3. **配置数据库连接**
   - 编辑 `config/db.php`
   - 修改数据库连接信息（如果需要）

4. **访问网站**
   - 在浏览器中访问配置的域名

## 使用说明

1. **用户注册**
   - 访问注册页面，填写用户名、邮箱和密码
   - 用户名和邮箱必须唯一

2. **用户登录**
   - 使用注册的用户名和密码登录
   - 登录成功后跳转到首页

3. **功能模块**
   - 创建投票：创建新的投票主题和选项
   - 参与投票：浏览并参与投票
   - 查看结果：查看投票统计结果

## 功能列表

- [x] 用户注册与登录
- [ ] 创建投票
- [ ] 参与投票
- [ ] 查看投票结果
- [ ] 投票管理

## 开发规范

- 每个功能实现完成后进行一次 Git 提交
- 提交信息格式：`feat: 添加功能名称`
- 后端代码使用 PDO 进行数据库操作
- 前端代码使用原生 JavaScript

## 注意事项

- 确保 PHP 版本 >= 7.4
- 确保 MySQL 版本 >= 5.7
- 密码使用 `password_hash` 加密存储
- 前端请求使用 `credentials: 'include'` 保持会话
