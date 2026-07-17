<?php
require_once 'config/db.php';
$db = getDB();

echo "<h2>users 表</h2>";
$stmt = $db->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($users, true) . "</pre>";

echo "<h2>admins 表</h2>";
$stmt = $db->query("SELECT * FROM admins");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($admins, true) . "</pre>";
?>