<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
if (!isset($_SESSION['user_id']) && !$is_admin) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

$poll_id = isset($_GET['poll_id']) ? intval($_GET['poll_id']) : 0;

if ($poll_id <= 0) {
    echo json_encode(['success' => false, 'message' => '投票ID无效']);
    exit;
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT p.id, p.title, p.description, p.is_multiple, p.max_options, 
               p.start_time, p.end_time, p.is_active, p.is_anonymous,
               COALESCE(u.username, a.username, '已删除用户') as creator_name
        FROM polls p
        LEFT JOIN users u ON p.creator_id = u.id
        LEFT JOIN admins a ON p.creator_id = a.id
        WHERE p.id = ?
    ");
    $stmt->execute([$poll_id]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($poll['is_anonymous'] && !$is_admin) {
        $poll['creator_name'] = '匿名用户';
    }
    
    if (!$poll) {
        echo json_encode(['success' => false, 'message' => '投票不存在']);
        exit;
    }
    
    $stmt = $db->prepare("
        SELECT id, option_text, vote_count
        FROM poll_options
        WHERE poll_id = ?
        ORDER BY vote_count DESC, id ASC
    ");
    $stmt->execute([$poll_id]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalVotes = array_sum(array_column($options, 'vote_count'));
    
    foreach ($options as &$option) {
        $option['percentage'] = $totalVotes > 0 ? round(($option['vote_count'] / $totalVotes) * 100, 1) : 0;
    }
    
    $stmt = $db->prepare("
        SELECT 
            COALESCE(u.username, a.username, '已删除用户') as username, 
            pv.voted_at, po.option_text
        FROM poll_votes pv
        LEFT JOIN users u ON pv.user_id = u.id
        LEFT JOIN admins a ON pv.user_id = a.id
        JOIN poll_options po ON pv.option_id = po.id
        WHERE pv.poll_id = ?
        ORDER BY pv.voted_at DESC
    ");
    $stmt->execute([$poll_id]);
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'poll' => $poll,
            'options' => $options,
            'total_votes' => $totalVotes,
            'votes' => $votes
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '获取投票结果失败: ' . $e->getMessage()]);
}
?>