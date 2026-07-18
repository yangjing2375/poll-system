# API 接口文档

## 基础信息

- **API 地址**: `http://poll-system.freepage.cc/api/`
- **本地地址**: `http://localhost:8080/api/`
- **请求格式**: JSON
- **认证方式**: Session + Cookie
- **跨域**: 支持（允许本地访问）

---

## 接口列表

| 序号 | 接口 | 方法 | 功能 | 认证要求 |
|------|------|------|------|----------|
| 1 | `/api/login.php` | POST | 用户登录 | 无需 |
| 2 | `/api/register.php` | POST | 用户注册 | 无需 |
| 3 | `/api/logout.php` | POST | 退出登录 | 需登录 |
| 4 | `/api/check_session.php` | GET | 检查登录状态 | 否 |
| 5 | `/api/create_poll.php` | POST | 创建投票 | 需登录 |
| 6 | `/api/get_polls.php` | GET | 获取投票列表 | 无需 |
| 7 | `/api/vote.php` | POST | 参与投票 | 需登录 |
| 8 | `/api/get_poll_results.php` | GET | 获取投票结果 | 无需 |
| 9 | `/api/get_user_votes.php` | GET | 获取用户投票记录 | 需登录 |
| 10 | `/api/upload_avatar.php` | POST | 上传头像 | 需登录 |
| 11 | `/api/get_avatar.php` | GET | 获取头像 | 无需 |
| 12 | `/api/get_profile.php` | GET | 获取用户资料 | 需登录 |
| 13 | `/api/update_profile.php` | POST | 更新用户资料 | 需登录 |
| 14 | `/api/admin_login.php` | POST | 管理员登录 | 无需 |
| 15 | `/api/admin_polls.php` | GET/POST | 投票管理 | 需管理员 |
| 16 | `/api/admin_users.php` | GET/POST | 用户管理 | 需管理员 |
| 17 | `/api/admin_hot_polls.php` | GET/POST | 热门投票管理 | 需管理员 |
| 18 | `/api/admin_statistics.php` | GET | 投票统计 | 需管理员 |
| 19 | `/api/get_topics_stats.php` | GET | 获取专题统计 | 无需 |

---

## 1. 用户注册

**请求地址**: `POST /api/register.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名，长度1-50字符 |
| email | string | 是 | 邮箱地址 |
| password | string | 是 | 密码，至少6位 |

**请求示例**:

```json
{
    "username": "testuser",
    "email": "test@example.com",
    "password": "123456"
}
```

**成功响应**:

```json
{
    "status": "success",
    "message": "注册成功",
    "data": {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com"
    }
}
```

**失败响应**:

```json
{
    "status": "error",
    "message": "用户名已存在"
}
```

---

## 2. 用户登录

**请求地址**: `POST /api/login.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名 |
| password | string | 是 | 密码 |

**请求示例**:

```json
{
    "username": "testuser",
    "password": "123456"
}
```

**成功响应**:

```json
{
    "status": "success",
    "message": "登录成功",
    "data": {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "avatar": null,
        "is_admin": false
    }
}
```

**失败响应**:

```json
{
    "status": "error",
    "message": "用户名或密码错误"
}
```

---

## 3. 退出登录

**请求地址**: `POST /api/logout.php`

**请求参数**: 无

**成功响应**:

```json
{
    "status": "success",
    "message": "退出成功"
}
```

---

## 4. 检查登录状态

**请求地址**: `GET /api/check_session.php`

**请求参数**: 无

**成功响应**:

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "avatar": null,
        "is_admin": false
    }
}
```

**失败响应**:

```json
{
    "status": "error",
    "message": "未登录"
}
```

---

## 5. 创建投票

**请求地址**: `POST /api/create_poll.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| title | string | 是 | 投票标题 |
| description | string | 否 | 投票描述 |
| topic | string | 否 | 投票专题 |
| options | array | 是 | 投票选项数组，至少2个 |
| option_type | string | 否 | 选项类型：text（文字）或image（图片），默认text |
| is_multiple | int | 否 | 是否多选，0=单选，1=多选，默认0 |
| max_options | int | 否 | 多选时最多可选数量，默认1 |
| is_anonymous | int | 否 | 是否匿名投票，0=显示创建者，1=隐藏创建者，默认0 |
| end_time | string | 否 | 截止时间，格式YYYY-MM-DD HH:MM:SS |

**文字投票请求示例**:

```json
{
    "title": "你最喜欢的编程语言",
    "description": "请选择你最喜欢的编程语言",
    "topic": "技术专题",
    "options": ["Java", "Python", "JavaScript", "Go"],
    "option_type": "text",
    "is_multiple": 0,
    "max_options": 1,
    "is_anonymous": 0
}
```

**图片投票请求示例**:

```json
{
    "title": "最美风景投票",
    "description": "请选择你心中最美的风景",
    "topic": "旅游专题",
    "options": [
        {"text": "万里长城", "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."},
        {"text": "四川九寨沟", "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."}
    ],
    "option_type": "image",
    "is_multiple": 1,
    "max_options": 2,
    "is_anonymous": 1
}
```

**成功响应**:

```json
{
    "success": true,
    "message": "投票创建成功",
    "poll_id": 1
}
```

**失败响应**:

```json
{
    "success": false,
    "message": "请先登录"
}
```

---

## 6. 获取投票列表

**请求地址**: `GET /api/get_polls.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| topic | string | 否 | 按专题筛选 |

**成功响应**:

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "你最喜欢的编程语言",
            "description": "请选择你最喜欢的编程语言",
            "topic": "技术专题",
            "option_type": "text",
            "is_multiple": 0,
            "max_options": 1,
            "is_active": 1,
            "is_hot": 1,
            "is_anonymous": 0,
            "start_time": "2026-07-14 10:00:00",
            "end_time": null,
            "created_at": "2026-07-14 10:00:00",
            "creator_name": "testuser",
            "options": [
                {
                    "id": 1,
                    "option_text": "Java",
                    "option_image": null,
                    "vote_count": 5,
                    "percentage": 33.3
                },
                {
                    "id": 2,
                    "option_text": "Python",
                    "option_image": null,
                    "vote_count": 10,
                    "percentage": 66.7
                }
            ],
            "total_votes": 15,
            "has_voted": false
        }
    ]
}
```

---

## 7. 参与投票

**请求地址**: `POST /api/vote.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| poll_id | int | 是 | 投票ID |
| option_ids | array | 是 | 选项ID数组，单选传1个，多选传多个 |

**请求示例**:

```json
{
    "poll_id": 1,
    "option_ids": [1]
}
```

**成功响应**:

```json
{
    "status": "success",
    "message": "投票成功"
}
```

**失败响应**:

```json
{
    "status": "error",
    "message": "您已参与过此投票"
}
```

---

## 8. 获取投票结果

**请求地址**: `GET /api/get_poll_results.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| poll_id | int | 是 | 投票ID |

**成功响应**:

```json
{
    "success": true,
    "data": {
        "poll": {
            "id": 1,
            "title": "你最喜欢的编程语言",
            "description": "请选择你最喜欢的编程语言",
            "topic": "技术专题",
            "option_type": "text",
            "is_multiple": 0,
            "max_options": 1,
            "is_active": 1,
            "is_hot": 1,
            "is_anonymous": 0,
            "creator_name": "testuser",
            "total_votes": 15,
            "has_voted": true,
            "created_at": "2026-07-14 10:00:00"
        },
        "options": [
            {
                "id": 1,
                "option_text": "Java",
                "option_image": null,
                "vote_count": 5,
                "percentage": 33.3
            },
            {
                "id": 2,
                "option_text": "Python",
                "option_image": null,
                "vote_count": 10,
                "percentage": 66.7
            }
        ]
    }
}
```

---

## 9. 获取用户投票记录

**请求地址**: `GET /api/get_user_votes.php`

**请求参数**: 无

**成功响应**:

```json
{
    "success": true,
    "data": [
        {
            "vote_id": 1,
            "poll_id": 1,
            "poll_title": "你最喜欢的编程语言",
            "poll_description": "请选择你最喜欢的编程语言",
            "options": [
                {
                    "id": 1,
                    "option_text": "Java",
                    "option_image": null
                }
            ],
            "voted_at": "2026-07-14 12:00:00"
        }
    ]
}
```

---

## 10. 上传头像

**请求地址**: `POST /api/upload_avatar.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| avatar | string | 是 | 图片base64数据 |

**请求示例**:

```json
{
    "avatar": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
}
```

**成功响应**:

```json
{
    "success": true,
    "message": "头像上传成功"
}
```

---

## 11. 获取头像

**请求地址**: `GET /api/get_avatar.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| user_id | int | 是 | 用户ID |

**成功响应**:

```json
{
    "success": true,
    "data": {
        "avatar": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
    }
}
```

---

## 12. 获取用户资料

**请求地址**: `GET /api/get_profile.php`

**请求参数**: 无

**成功响应**:

```json
{
    "success": true,
    "data": {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "gender": null,
        "birthday": null,
        "age": null,
        "avatar": null,
        "created_at": "2026-07-14 10:00:00"
    }
}
```

---

## 13. 更新用户资料

**请求地址**: `POST /api/update_profile.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 否 | 用户名 |
| email | string | 否 | 邮箱 |
| gender | string | 否 | 性别（男/女/其他） |
| age | int | 否 | 年龄 |
| birthday | string | 否 | 生日，格式YYYY-MM-DD |

**请求示例**:

```json
{
    "username": "newname",
    "email": "newemail@example.com",
    "gender": "男",
    "age": 25,
    "birthday": "1999-01-15"
}
```

**成功响应**:

```json
{
    "success": true,
    "message": "资料更新成功"
}
```

---

## 14. 管理员登录

**请求地址**: `POST /api/admin_login.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 管理员用户名 |
| password | string | 是 | 管理员密码 |

**请求示例**:

```json
{
    "username": "admin",
    "password": "password"
}
```

**成功响应**:

```json
{
    "status": "success",
    "message": "登录成功",
    "data": {
        "id": 1,
        "username": "admin",
        "email": "admin@example.com",
        "avatar": null
    }
}
```

---

## 15. 投票管理

**请求地址**: `GET/POST /api/admin_polls.php`

### 获取投票列表（GET）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码，默认1 |
| limit | int | 否 | 每页数量，默认10 |
| search | string | 否 | 搜索关键词（标题/描述） |
| status | int | 否 | 状态筛选：-1=全部，0=已禁用，1=进行中 |

**成功响应**:

```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "title": "你最喜欢的编程语言",
            "description": "请选择你最喜欢的编程语言",
            "topic": "技术专题",
            "is_multiple": 0,
            "max_options": 1,
            "is_active": 1,
            "is_hot": 1,
            "is_anonymous": 0,
            "creator_name": "testuser",
            "total_votes": 15,
            "created_at": "2026-07-14 10:00:00",
            "status_text": "进行中",
            "options": [
                {"id": 1, "option_text": "Java", "vote_count": 5},
                {"id": 2, "option_text": "Python", "vote_count": 10}
            ]
        }
    ],
    "total": 100,
    "page": 1,
    "limit": 10
}
```

### 创建投票（POST）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| action | string | 是 | 值为"create" |
| title | string | 是 | 投票标题 |
| description | string | 否 | 投票描述 |
| topic | string | 否 | 投票专题 |
| options | array | 是 | 投票选项数组，至少2个 |
| is_multiple | int | 否 | 是否多选，默认0 |
| max_options | int | 否 | 多选时最多可选数量，默认1 |
| is_active | int | 否 | 是否启用，默认1 |
| is_anonymous | int | 否 | 是否匿名，默认0 |
| end_time | string | 否 | 截止时间 |

**请求示例**:

```json
{
    "action": "create",
    "title": "管理员创建的投票",
    "description": "描述内容",
    "topic": "校园专题",
    "options": ["选项1", "选项2", "选项3"],
    "is_multiple": 0,
    "max_options": 1,
    "is_active": 1,
    "is_anonymous": 0
}
```

**成功响应**:

```json
{
    "status": "success",
    "message": "投票创建成功",
    "poll_id": 1
}
```

### 编辑投票（POST）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| action | string | 是 | 值为"edit" |
| id | int | 是 | 投票ID |
| title | string | 是 | 投票标题 |
| description | string | 否 | 投票描述 |
| topic | string | 否 | 投票专题 |
| options | array | 是 | 投票选项数组，至少2个 |
| is_multiple | int | 否 | 是否多选 |
| max_options | int | 否 | 多选时最多可选数量 |
| is_active | int | 否 | 是否启用 |
| is_anonymous | int | 否 | 是否匿名 |
| end_time | string | 否 | 截止时间 |

**请求示例**:

```json
{
    "action": "edit",
    "id": 1,
    "title": "修改后的标题",
    "options": ["修改后的选项1", "修改后的选项2"]
}
```

**成功响应**:

```json
{
    "status": "success",
    "message": "投票更新成功"
}
```

### 删除投票（POST）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| action | string | 是 | 值为"delete" |
| id | int | 是 | 投票ID |

**请求示例**:

```json
{
    "action": "delete",
    "id": 1
}
```

**成功响应**:

```json
{
    "status": "success",
    "message": "投票删除成功"
}
```

### 切换投票状态（POST）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| action | string | 是 | 值为"toggle_active" |
| id | int | 是 | 投票ID |

**请求示例**:

```json
{
    "action": "toggle_active",
    "id": 1
}
```

**成功响应**:

```json
{
    "status": "success",
    "message": "投票已启用"
}
```

---

## 16. 用户管理

**请求地址**: `GET/POST /api/admin_users.php`

### 获取用户列表（GET）

**请求参数**: 无

**成功响应**:

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "username": "testuser",
            "email": "test@example.com",
            "gender": "男",
            "age": 25,
            "created_at": "2026-07-14 10:00:00"
        }
    ]
}
```

### 删除用户（POST）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| action | string | 是 | 值为"delete" |
| user_id | int | 是 | 用户ID |

**请求示例**:

```json
{
    "action": "delete",
    "user_id": 1
}
```

**成功响应**:

```json
{
    "success": true,
    "message": "用户删除成功"
}
```

---

## 17. 热门投票管理

**请求地址**: `GET/POST /api/admin_hot_polls.php`

### 获取热门投票列表（GET）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| filter | string | 否 | 筛选：hot（热门）、normal（普通） |

**成功响应**:

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "你最喜欢的编程语言",
            "topic": "技术专题",
            "is_hot": 1,
            "total_votes": 15,
            "created_at": "2026-07-14 10:00:00"
        }
    ]
}
```

### 切换热门状态（POST）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| action | string | 是 | 值为"toggle" |
| poll_id | int | 是 | 投票ID |

**请求示例**:

```json
{
    "action": "toggle",
    "poll_id": 1
}
```

**成功响应**:

```json
{
    "success": true,
    "message": "热门状态切换成功"
}
```

### 批量设置热门（POST）

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| action | string | 是 | 值为"batch_set"或"batch_cancel" |
| poll_ids | array | 是 | 投票ID数组 |

**请求示例**:

```json
{
    "action": "batch_set",
    "poll_ids": [1, 2, 3]
}
```

---

## 18. 投票统计

**请求地址**: `GET /api/admin_statistics.php`

**请求参数**: 无

**成功响应**:

```json
{
    "success": true,
    "data": {
        "overview": {
            "total_users": 100,
            "total_polls": 50,
            "total_votes": 500,
            "active_polls": 30,
            "hot_polls": 10,
            "today_registrations": 5,
            "today_polls": 3,
            "today_votes": 20
        },
        "trend": {
            "dates": ["07-12", "07-13", "07-14", "07-15", "07-16", "07-17", "07-18"],
            "votes": [50, 60, 45, 70, 80, 65, 75],
            "polls": [5, 3, 8, 6, 4, 7, 5],
            "registrations": [3, 5, 2, 4, 6, 3, 5]
        },
        "hot_polls": [
            {"id": 1, "title": "热门投票1", "vote_count": 100},
            {"id": 2, "title": "热门投票2", "vote_count": 80},
            {"id": 3, "title": "热门投票3", "vote_count": 60},
            {"id": 4, "title": "热门投票4", "vote_count": 45},
            {"id": 5, "title": "热门投票5", "vote_count": 30}
        ],
        "active_users": [
            {"id": 1, "username": "user1", "poll_count": 15, "vote_count": 20},
            {"id": 2, "username": "user2", "poll_count": 12, "vote_count": 18},
            {"id": 3, "username": "user3", "poll_count": 10, "vote_count": 15},
            {"id": 4, "username": "user4", "poll_count": 8, "vote_count": 12},
            {"id": 5, "username": "user5", "poll_count": 5, "vote_count": 8}
        ],
        "topics": [
            {"name": "技术专题", "count": 20},
            {"name": "校园专题", "count": 15},
            {"name": "美食专题", "count": 10},
            {"name": "生活专题", "count": 5}
        ],
        "recent_votes": [
            {"poll_title": "投票标题", "username": "user1", "voted_at": "2026-07-18 10:00:00"}
        ]
    }
}
```

---

## 19. 获取专题统计

**请求地址**: `GET /api/get_topics_stats.php`

**请求参数**: 无

**成功响应**:

```json
{
    "success": true,
    "data": [
        {"topic": "家庭&情感", "participant_count": 50, "vote_count": 120},
        {"topic": "美食专题", "participant_count": 30, "vote_count": 80},
        {"topic": "校园专题", "participant_count": 45, "vote_count": 95},
        {"topic": "职场专题", "participant_count": 25, "vote_count": 60},
        {"topic": "影视专题", "participant_count": 35, "vote_count": 75},
        {"topic": "运动&出行", "participant_count": 20, "vote_count": 45},
        {"topic": "娱乐专题", "participant_count": 40, "vote_count": 85},
        {"topic": "旅游专题", "participant_count": 55, "vote_count": 130},
        {"topic": "汽车专题", "participant_count": 15, "vote_count": 35},
        {"topic": "游戏专题", "participant_count": 60, "vote_count": 150}
    ]
}
```

---

## 数据库表结构

### users（用户表）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键，自增 |
| username | varchar(50) | 用户名 |
| email | varchar(100) | 邮箱 |
| password | varchar(255) | 密码（加密） |
| gender | varchar(10) | 性别 |
| birthday | date | 生日 |
| age | int | 年龄 |
| avatar | text | 头像（base64） |
| created_at | datetime | 创建时间 |

### admins（管理员表）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键，自增 |
| username | varchar(50) | 用户名 |
| email | varchar(100) | 邮箱 |
| password | varchar(255) | 密码（加密） |
| gender | varchar(10) | 性别 |
| birthday | date | 生日 |
| age | int | 年龄 |
| avatar | text | 头像（base64） |
| created_at | datetime | 创建时间 |

### polls（投票表）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键，自增 |
| title | varchar(200) | 投票标题 |
| description | text | 投票描述 |
| topic | varchar(50) | 投票专题 |
| creator_id | int | 创建者ID |
| is_multiple | tinyint | 是否多选（0/1） |
| max_options | int | 多选时最多可选数量 |
| is_active | tinyint | 是否活跃（0/1） |
| is_hot | tinyint | 是否热门（0/1） |
| is_anonymous | tinyint | 是否匿名（0/1） |
| option_type | varchar(20) | 选项类型（text/image） |
| start_time | datetime | 开始时间 |
| end_time | datetime | 截止时间 |
| created_at | datetime | 创建时间 |

### poll_options（投票选项表）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键，自增 |
| poll_id | int | 投票ID（外键） |
| option_text | varchar(100) | 选项文字 |
| option_image | mediumtext | 选项图片（base64） |
| vote_count | int | 投票数 |

### poll_votes（投票记录表）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键，自增 |
| poll_id | int | 投票ID（外键） |
| option_id | int | 选项ID（外键） |
| user_id | int | 用户ID |
| voted_at | datetime | 投票时间 |

---

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 400 | 请求参数错误 |
| 401 | 未登录或无权限 |
| 500 | 服务器内部错误 |

---

## 注意事项

1. 所有POST请求的Content-Type必须为`application/json`
2. 登录后Session会自动保存，后续请求需携带Cookie
3. 管理员接口需要管理员权限，普通用户无法访问
4. 图片数据需使用base64编码，最大不超过2MB
5. 投票选项至少2个，最多50个
6. 多选投票的max_options不能超过选项数量
7. 管理员创建投票时，creator_id为管理员ID