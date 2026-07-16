<?php
session_start();
require_once '../config/db.php';
$db = getDB();

$username = 'admin';
$password = '123456';

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("REPLACE INTO admins (username, password, email) VALUES (?, ?, ?)");
$stmt->execute([$username, $hash, 'admin@poll-system.com']);

echo "管理员账号已修复！\n";
echo "用户名: $username\n";
echo "密码: $password\n";
echo "新密码哈希: $hash\n";
?>
