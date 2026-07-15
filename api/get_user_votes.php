<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT pv.id as vote_id, pv.poll_id, pv.option_id, pv.voted_at,
               p.title, p.description, p.is_multiple, p.max_options, p.end_time,
               po.option_text,
               u.username as creator_name
        FROM poll_votes pv
        JOIN polls p ON pv.poll_id = p.id
        JOIN poll_options po ON pv.option_id = po.id
        JOIN users u ON p.creator_id = u.id
        WHERE pv.user_id = ?
        ORDER BY pv.voted_at DESC
    ");
    $stmt->execute([$user_id]);
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [];
    $pollMap = [];
    
    foreach ($votes as $vote) {
        $poll_id = $vote['poll_id'];
        
        if (!isset($pollMap[$poll_id])) {
            $pollMap[$poll_id] = [
                'poll_id' => $poll_id,
                'title' => $vote['title'],
                'description' => $vote['description'],
                'is_multiple' => $vote['is_multiple'],
                'max_options' => $vote['max_options'],
                'end_time' => $vote['end_time'],
                'creator_name' => $vote['creator_name'],
                'voted_at' => $vote['voted_at'],
                'selected_options' => []
            ];
        }
        
        $pollMap[$poll_id]['selected_options'][] = [
            'option_id' => $vote['option_id'],
            'option_text' => $vote['option_text']
        ];
    }
    
    $result = array_values($pollMap);
    
    echo json_encode(['success' => true, 'data' => $result]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '获取投票记录失败: ' . $e->getMessage()]);
}
?>