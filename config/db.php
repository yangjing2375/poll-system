<?php
require_once 'env.php';
require_once 'logger.php';

define('DB_HOST', Env::get('DB_HOST', 'localhost'));
define('DB_NAME', Env::get('DB_NAME', 'poll_system'));
define('DB_USER', Env::get('DB_USER', 'root'));
define('DB_PASS', Env::get('DB_PASS', 'root'));
define('ALLOWED_ORIGIN', Env::get('APP_URL', 'http://localhost:8080'));

function setCORSHeaders() {
    header('Content-Type: application/json');
    
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    if (in_array($origin, ['http://localhost:8080', 'http://poll-system.local:8080', Env::get('APP_URL')])) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } else {
        header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    }
    
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
            Logger::info('数据库连接成功');
        } catch (PDOException $e) {
            Logger::error('数据库连接失败', ['error' => $e->getMessage()]);
            die(json_encode(['status' => 'error', 'message' => '数据库连接失败']));
        }
    }
    return $db;
}
?>
