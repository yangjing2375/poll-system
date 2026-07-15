<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => '请先登录']);
    exit;
}

$user_id = $_SESSION['user_id'];
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (!$url) {
    echo json_encode(['status' => 'error', 'message' => '请提供图片URL']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("UPDATE users SET avatar = ?, avatar_data = NULL WHERE id = ?");
$stmt->execute([$url, $user_id]);

echo json_encode(['status' => 'success', 'message' => '头像URL已设置']);
?>