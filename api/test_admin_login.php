<?php
session_start();
require_once '../config/db.php';
$db = getDB();

$username = 'admin';
$password = '123456';

$stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "admins表中没有admin用户，正在创建...\n";
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hash, 'admin@poll-system.com']);
    
    echo "管理员账号创建成功！密码: $password\n";
} else {
    echo "找到管理员用户\n";
    echo "密码哈希: " . $admin['password'] . "\n";
    echo "验证结果: " . (password_verify($password, $admin['password']) ? '成功' : '失败') . "\n";
}
?>
