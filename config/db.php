<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'poll_system');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('ALLOWED_ORIGIN', 'http://localhost:8080');

function setCORSHeaders() {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit;
    }
}

function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die(json_encode(['status' => 'error', 'message' => '数据库连接失败: ' . $e->getMessage()]));
        }
    }
    return $db;
}
?>