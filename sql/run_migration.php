<?php
require_once '../config/db.php';

try {
    $db = getDB();
    $sql = "ALTER TABLE polls ADD COLUMN topic VARCHAR(50) DEFAULT NULL AFTER description";
    $db->exec($sql);
    echo "Migration executed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>