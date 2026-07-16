<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

if (!isset($_SESSION['user_id']) && !$is_admin) {
    echo json_encode(['status' => 'error', 'message' => '请先登录']);
    exit;
}

$user_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['user_id'];
$table_name = $is_admin ? 'admins' : 'users';

if (!isset($_FILES['avatar'])) {
    echo json_encode(['status' => 'error', 'message' => '请选择图片']);
    exit;
}

$file = $_FILES['avatar'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => '文件上传失败']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => '只支持JPG、PNG、GIF、WebP格式']);
    exit;
}

$maxSize = 2 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['status' => 'error', 'message' => '图片大小不能超过2MB']);
    exit;
}

$imageData = file_get_contents($file['tmp_name']);
if ($imageData === false) {
    echo json_encode(['status' => 'error', 'message' => '无法读取图片文件']);
    exit;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$avatarUrl = $protocol . '://' . $host . '/api/get_avatar.php?user_id=' . $user_id . '&table=' . $table_name;

$db = getDB();
$stmt = $db->prepare("UPDATE $table_name SET avatar_data = ?, avatar = ? WHERE id = ?");
$stmt->execute([$imageData, $avatarUrl, $user_id]);

echo json_encode([
    'status' => 'success', 
    'message' => '头像上传成功',
    'avatar_url' => $avatarUrl
]);
?>
