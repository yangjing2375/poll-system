<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => '请先登录']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $db = getDB();
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