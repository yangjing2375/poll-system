<?php
require_once 'config/db.php';

try {
    $db = getDB();
    
    $db->exec("ALTER TABLE polls ADD COLUMN option_type VARCHAR(20) DEFAULT 'text' AFTER is_anonymous;");
    echo "Added option_type column to polls table successfully.\n";
    
    $db->exec("ALTER TABLE poll_options ADD COLUMN option_image VARCHAR(500) DEFAULT NULL AFTER option_text;");
    echo "Added option_image column to poll_options table successfully.\n";
    
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
