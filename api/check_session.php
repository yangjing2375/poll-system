<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => '只支持GET请求']);
    exit;
}

if (isset($_SESSION['user_id'], $_SESSION['username'])) {
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT id, username, email FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode([
                'status' => 'success',
                'data' => $user
            ]);
        } else {
            session_destroy();
            echo json_encode(['status' => 'error', 'message' => '用户不存在']);
        }
    } catch (PDOException $e) {
        session_destroy();
        echo json_encode(['status' => 'error', 'message' => '验证失败']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '未登录']);
}
?>