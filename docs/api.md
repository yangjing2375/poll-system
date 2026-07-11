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

## 待实现接口

- [ ] 创建投票 (`POST /api/create_poll.php`)
- [ ] 获取投票列表 (`GET /api/get_polls.php`)
- [ ] 参与投票 (`POST /api/vote.php`)
- [ ] 查看投票结果 (`GET /api/get_results.php`)
