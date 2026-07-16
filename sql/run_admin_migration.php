<?php
include '../config/db.php';

try {
    $sql = file_get_contents('create_admins_table.sql');
    $db->exec($sql);
    echo "管理员表创建成功，初始管理员账号：admin / 123456";
} catch (PDOException $e) {
    echo "创建失败: " . $e->getMessage();
}
