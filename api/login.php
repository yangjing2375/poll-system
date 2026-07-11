<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '只支持POST请求']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username'], $data['password'])) {
    echo json_encode(['status' => 'error', 'message' => '缺少必要参数']);
    exit;
}

$username = trim($data['username']);
$password = trim($data['password']);

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => '用户名和密码不能为空']);
    exit;
}

$db = getDB();

try {
    $stmt = $db->prepare("SELECT id, username, email, password FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['status' => 'error', 'message' => '用户名或密码错误']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    echo json_encode([
        'status' => 'success',
        'message' => '登录成功',
        'data' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '登录失败: ' . $e->getMessage()]);
}
?>