<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '只支持POST请求']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['action']) && $data['action'] === 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => '退出成功']);
    exit;
}

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

    $is_admin = false;
    $admin_id = null;

    if (!$user) {
        $adminStmt = $db->prepare("SELECT id, username, password, email FROM admins WHERE username = :username");
        $adminStmt->execute([':username' => $username]);
        $admin = $adminStmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $is_admin = true;
            $admin_id = $admin['id'];
            
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['is_admin'] = true;
            
            echo json_encode([
                'status' => 'success',
                'message' => '登录成功',
                'data' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'email' => $admin['email']
                ],
                'is_admin' => true
            ]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => '用户名或密码错误']);
            exit;
        }
    }

    if (!password_verify($password, $user['password'])) {
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
        ],
        'is_admin' => false
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '登录失败: ' . $e->getMessage()]);
}
?>
