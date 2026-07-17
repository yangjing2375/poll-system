<?php
require_once 'config/db.php';

try {
    $db = getDB();
    
    $db->exec("ALTER TABLE poll_options MODIFY COLUMN option_image TEXT DEFAULT NULL;");
    echo "Changed option_image column type to TEXT successfully.\n";
    
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
