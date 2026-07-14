<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.id, p.title, p.description, p.is_multiple, p.max_options, p.is_active,
               p.start_time, p.end_time, p.created_at,
               u.username as creator_name
        FROM polls p
        JOIN users u ON p.creator_id = u.id
        WHERE p.is_active = 1
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($polls as &$poll) {
        $stmt = $db->prepare("
            SELECT id, option_text, vote_count
            FROM poll_options
            WHERE poll_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$poll['id']]);
        $poll['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalVotes = array_sum(array_column($poll['options'], 'vote_count'));
        $poll['total_votes'] = $totalVotes;
        
        foreach ($poll['options'] as &$option) {
            $option['percentage'] = $totalVotes > 0 ? round(($option['vote_count'] / $totalVotes) * 100, 1) : 0;
        }
        
        if (isset($_SESSION['user_id'])) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM poll_votes WHERE poll_id = ? AND user_id = ?");
            $stmt->execute([$poll['id'], $_SESSION['user_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $poll['has_voted'] = $result['count'] > 0;
        } else {
            $poll['has_voted'] = false;
        }
    }
    
    echo json_encode(['success' => true, 'data' => $polls]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '获取投票列表失败: ' . $e->getMessage()]);
}
?>