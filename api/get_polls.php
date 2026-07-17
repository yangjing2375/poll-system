<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

try {
    $db = getDB();
    
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $topic = isset($_GET['topic']) ? trim($_GET['topic']) : '';
    
    $sql = "
        SELECT p.id, p.title, p.description, p.topic, p.is_multiple, p.max_options, p.is_active, p.is_hot,
               p.start_time, p.end_time, p.created_at,
               COALESCE(u.username, a.username, '已删除用户') as creator_name
        FROM polls p
        LEFT JOIN users u ON p.creator_id = u.id
        LEFT JOIN admins a ON p.creator_id = a.id
        WHERE p.is_active = 1
    ";
    
    $params = [];
    
    if ($topic) {
        $sql .= " AND p.topic = ?";
        $params[] = $topic;
    }
    
    if ($keyword) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
        $params[] = "%{$keyword}%";
        $params[] = "%{$keyword}%";
    }
    
    $sql .= " ORDER BY p.is_hot DESC, p.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
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
        
        $is_logged_in = (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) || isset($_SESSION['user_id']) && $_SESSION['user_id'];
        if ($is_logged_in) {
            $current_user_id = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? $_SESSION['admin_id'] : $_SESSION['user_id'];
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM poll_votes WHERE poll_id = ? AND user_id = ?");
            $stmt->execute([$poll['id'], $current_user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $poll['has_voted'] = $result['count'] > 0;
        } else {
            $poll['has_voted'] = null;
        }
    }
    
    echo json_encode(['success' => true, 'data' => $polls]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '获取投票列表失败: ' . $e->getMessage()]);
}
?>