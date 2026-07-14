<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => '未登录']);
}
?>