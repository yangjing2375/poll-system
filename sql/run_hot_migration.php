<?php
require_once dirname(__DIR__) . '/config/db.php';

try {
    $db = getDB();
    $sql = "ALTER TABLE polls ADD COLUMN is_hot TINYINT(1) DEFAULT 0 AFTER is_active";
    $db->exec($sql);
    echo "Migration executed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>