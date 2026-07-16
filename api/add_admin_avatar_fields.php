<?php
session_start();
require_once '../config/db.php';
$db = getDB();

try {
    $db->exec("ALTER TABLE admins ADD COLUMN avatar_data LONGBLOB DEFAULT NULL");
    $db->exec("ALTER TABLE admins ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    echo "字段添加成功！";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "字段已存在，无需重复添加";
    } else {
        echo "添加失败: " . $e->getMessage();
    }
}
?>
