<?php
require_once 'config/db.php';
$db = getDB();

try {
    $stmt = $db->prepare("ALTER TABLE polls ADD COLUMN is_anonymous TINYINT(1) NOT NULL DEFAULT 0");
    $stmt->execute();
    echo "✅ 成功添加 is_anonymous 字段到 polls 表！";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "⚠️ is_anonymous 字段已存在，无需重复添加";
    } else {
        echo "❌ 添加字段失败: " . $e->getMessage();
    }
}
?>