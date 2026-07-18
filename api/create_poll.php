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
    if (is_array($opt)) {
        $hasText = !empty(trim($opt['text']));
        $hasImage = !empty($opt['image']);
        return $hasText || $hasImage;
    }
    return !empty(trim($opt));
});

if (count($validOptions) < 2) {
    echo json_encode(['success' => false, 'message' => '至少需要两个有效的投票选项']);
    exit;
}

try {
    $db = getDB();
    $db->beginTransaction();
    
    $stmt = $db->prepare("INSERT INTO polls (title, description, topic, creator_id, is_multiple, max_options, end_time, is_anonymous, option_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['topic'] ?? '',
            $creator_id,
            $data['is_multiple'] ?? 0,
            $data['max_options'] ?? 1,
            $data['end_time'] ?? null,
            $data['is_anonymous'] ?? 0,
            $data['option_type'] ?? 'text'
        ]);
    
    $pollId = $db->lastInsertId();
    
    $stmt = $db->prepare("INSERT INTO poll_options (poll_id, option_text, option_image) VALUES (?, ?, ?)");
    foreach ($validOptions as $option) {
        if (is_array($option)) {
            $text = !empty(trim($option['text'])) ? trim($option['text']) : '图片选项';
            $image = $option['image'];
        } else {
            $text = trim($option);
            $image = null;
        }
        $stmt->execute([$pollId, $text, $image]);
    }
    
    $db->commit();
    
    Logger::info('投票创建成功', [
        'poll_id' => $pollId, 
        'title' => $data['title'], 
        'creator_id' => $creator_id,
        'option_type' => $data['option_type'] ?? 'text'
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => '投票创建成功',
        'poll_id' => $pollId
    ]);
} catch (PDOException $e) {
    $db->rollBack();
    Logger::error('投票创建失败', ['title' => $data['title'], 'error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'message' => '创建失败']);
}
?>