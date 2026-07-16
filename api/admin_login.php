<?php
include '../config/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($input['action']) && $input['action'] === 'login') {
        $username = $input['username'];
        $password = $input['password'];

        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            session_start();
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
        session_start();
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => '退出成功']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    session_start();
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
