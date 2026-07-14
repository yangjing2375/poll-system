# API 接口文档

## 基础信息

- **API 地址**: `http://localhost:8080/api/`
- **请求格式**: JSON
- **认证方式**: Session + Cookie

---

## 已实现接口

### 1. 用户注册

**请求地址**: `POST /api/register.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名，长度1-50字符 |
| email | string | 是 | 邮箱地址 |
| password | string | 是 | 密码，至少6位 |

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

### 2. 用户登录

**请求地址**: `POST /api/login.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名 |
| password | string | 是 | 密码 |

**成功响应**:

```json
{
    "status": "success",
    "message": "登录成功",
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
    "message": "用户名或密码错误"
}
```

---

### 3. 检查登录状态

**请求地址**: `GET /api/check_session.php`

**请求参数**: 无

**成功响应**:

```json
{
    "status": "success",
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
    "message": "未登录"
}
```

---

### 4. 创建投票

**请求地址**: `POST /api/create_poll.php`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| title | string | 是 | 投票标题 |
| description | string | 否 | 投票描述 |
| options | array | 是 | 投票选项数组，至少2个 |
| is_multiple | int | 否 | 是否多选，0=单选，1=多选，默认0 |
| max_options | int | 否 | 多选时最多可选数量，默认1 |
| end_time | string | 否 | 截止时间，格式YYYY-MM-DD HH:MM:SS |

**请求示例**:

```json
{
    "title": "你最喜欢的编程语言",
    "description": "请选择你最喜欢的编程语言",
    "options": ["Java", "Python", "JavaScript", "Go"],
    "is_multiple": 0,
    "max_options": 1
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

### 5. 获取投票列表

**请求地址**: `GET /api/get_polls.php`

**请求参数**: 无

**成功响应**:

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "你最喜欢的编程语言",
            "description": "请选择你最喜欢的编程语言",
            "is_multiple": 0,
            "max_options": 1,
            "is_active": 1,
            "start_time": "2026-07-14 10:00:00",
            "end_time": null,
            "created_at": "2026-07-14 10:00:00",
            "creator_name": "testuser",
            "options": [
                {
                    "id": 1,
                    "option_text": "Java",
                    "vote_count": 5,
                    "percentage": 33.3
                }
            ],
            "total_votes": 15,
            "has_voted": false
        }
    ]
}
```

**失败响应**:

```json
{
    "success": false,
    "message": "获取投票列表失败"
}
```

---

### 6. 参与投票

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
    "option_ids": [1, 3]
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

## 待实现接口

- [ ] 查看投票结果 (`GET /api/get_results.php`)
