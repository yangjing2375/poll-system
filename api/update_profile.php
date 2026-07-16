<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

if (!isset($_SESSION['user_id']) && !$is_admin) {
    echo json_encode(['status' => 'error', 'message' => '请先登录']);
    exit;
}

$user_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['user_id'];
$table_name = $is_admin ? 'admins' : 'users';
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => '无效的数据']);
    exit;
}

$db = getDB();
$updateFields = [];
$params = [];

try {
    if (isset($data['username']) && !empty($data['username'])) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table_name WHERE username = ? AND id != ?");
        $stmt->execute([$data['username'], $user_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => '用户名已存在']);
            exit;
        }
        $updateFields[] = 'username = ?';
        $params[] = $data['username'];
        if ($is_admin) {
            $_SESSION['admin_username'] = $data['username'];
        } else {
            $_SESSION['username'] = $data['username'];
        }
    }

    if (isset($data['email']) && !empty($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => '无效的邮箱地址']);
            exit;
        }
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table_name WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $user_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => '邮箱已被使用']);
            exit;
        }
        $updateFields[] = 'email = ?';
        $params[] = $data['email'];
    }

    if (isset($data['password']) && !empty($data['password'])) {
        if (!isset($data['old_password']) || empty($data['old_password'])) {
            echo json_encode(['status' => 'error', 'message' => '请输入原密码']);
            exit;
        }
        
        $stmt = $db->prepare("SELECT password FROM $table_name WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($data['old_password'], $user['password'])) {
            echo json_encode(['status' => 'error', 'message' => '原密码错误']);
            exit;
        }
        
        if (strlen($data['password']) < 6) {
            echo json_encode(['status' => 'error', 'message' => '密码长度至少6位']);
            exit;
        }
        $updateFields[] = 'password = ?';
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (isset($data['avatar'])) {
        $updateFields[] = 'avatar = ?';
        $params[] = $data['avatar'];
    }

    if (isset($data['gender'])) {
        if (!in_array($data['gender'], ['male', 'female'])) {
            echo json_encode(['status' => 'error', 'message' => '无效的性别值']);
            exit;
        }
        $updateFields[] = 'gender = ?';
        $params[] = $data['gender'];
    }

    if (isset($data['birthday'])) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birthday'])) {
            echo json_encode(['status' => 'error', 'message' => '无效的出生日期格式']);
            exit;
        }
        $updateFields[] = 'birthday = ?';
        $params[] = $data['birthday'];
    }

    if (isset($data['age'])) {
        $age = (int)$data['age'];
        if ($age < 0 || $age > 150) {
            echo json_encode(['status' => 'error', 'message' => '无效的年龄值']);
            exit;
        }
        $updateFields[] = 'age = ?';
        $params[] = $age;
    }

    if (empty($updateFields)) {
        echo json_encode(['status' => 'error', 'message' => '没有需要更新的字段']);
        exit;
    }

    $params[] = $user_id;
    $sql = "UPDATE $table_name SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    $selectFields = 'id, username, email, avatar, gender, age, birthday';
    $stmt = $db->prepare("SELECT $selectFields FROM $table_name WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'message' => '资料更新成功',
        'data' => $user
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '更新失败: ' . $e->getMessage()]);
}
?>
