<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($input['action']) && $input['action'] === 'login') {
        $username = $input['username'];
        $password = $input['password'];

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['is_admin'] = true;
            
            echo json_encode([
                'status' => 'success',
                'message' => '登录成功',
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'email' => $admin['email']
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => '用户名或密码错误']);
        }
    } elseif (isset($input['action']) && $input['action'] === 'logout') {
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
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        echo json_encode([
            'status' => 'success',
            'admin' => [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => '未登录']);
    }
}
