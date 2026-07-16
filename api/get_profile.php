<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => '请先登录']);
    exit;
}

$db = getDB();

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] && isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $stmt = $db->prepare("SELECT id, username, email, avatar, gender, age, birthday FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo json_encode(['status' => 'error', 'message' => '管理员不存在']);
        exit;
    }
    
    echo json_encode(['status' => 'success', 'data' => $admin]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $db->prepare("SELECT id, username, email, avatar, gender, age, birthday FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => '用户不存在']);
        exit;
    }

    echo json_encode(['status' => 'success', 'data' => $user]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '获取用户信息失败: ' . $e->getMessage()]);
}
?>
