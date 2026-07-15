<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if (isset($_SESSION['user_id'])) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'status' => 'success',
            'data' => $user
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => '用户不存在']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '未登录']);
}
?>