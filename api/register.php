<?php
require_once '../config/db.php';
setCORSHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '只支持POST请求']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username'], $data['email'], $data['password'])) {
    echo json_encode(['status' => 'error', 'message' => '缺少必要参数']);
    exit;
}

$username = trim($data['username']);
$email = trim($data['email']);
$password = trim($data['password']);

if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => '用户名、邮箱和密码不能为空']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => '邮箱格式不正确']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['status' => 'error', 'message' => '密码长度至少6位']);
    exit;
}

$db = getDB();

try {
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => '用户名已存在']);
        exit;
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => '邮箱已被注册']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);

    $userId = $db->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => '注册成功',
        'data' => [
            'id' => $userId,
            'username' => $username,
            'email' => $email
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '注册失败: ' . $e->getMessage()]);
}
?>