<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

$creator_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '方法不支持']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['title'])) {
    echo json_encode(['success' => false, 'message' => '请输入投票标题']);
    exit;
}

if (!isset($data['options']) || !is_array($data['options']) || count($data['options']) < 2) {
    echo json_encode(['success' => false, 'message' => '至少需要两个投票选项']);
    exit;
}

$validOptions = array_filter($data['options'], function($opt) {
    return !empty(trim($opt));
});

if (count($validOptions) < 2) {
    echo json_encode(['success' => false, 'message' => '至少需要两个有效的投票选项']);
    exit;
}

try {
    $db = getDB();
    $db->beginTransaction();
    
    $stmt = $db->prepare("INSERT INTO polls (title, description, topic, creator_id, is_multiple, max_options, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['title'],
        $data['description'] ?? '',
        $data['topic'] ?? '',
        $creator_id,
        $data['is_multiple'] ?? 0,
        $data['max_options'] ?? 1,
        $data['end_time'] ?? null
    ]);
    
    $pollId = $db->lastInsertId();
    
    $stmt = $db->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
    foreach ($validOptions as $option) {
        $stmt->execute([$pollId, trim($option)]);
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => '投票创建成功',
        'poll_id' => $pollId
    ]);
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => '创建失败: ' . $e->getMessage()]);
}
?>