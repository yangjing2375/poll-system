<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

try {
    $db = getDB();
    
    $topics = [
        '家庭&情感',
        '美食专题',
        '校园专题',
        '职场专题',
        '影视专题',
        '运动&出行',
        '娱乐专题',
        '旅游专题',
        '汽车专题',
        '游戏专题'
    ];
    
    $result = [];
    
    foreach ($topics as $topic) {
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT pv.user_id) as participant_count,
                   COUNT(pv.id) as vote_count
            FROM poll_votes pv
            JOIN polls p ON pv.poll_id = p.id
            WHERE p.topic = ?
        ");
        $stmt->execute([$topic]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $result[] = [
            'topic' => $topic,
            'participant_count' => $stats['participant_count'] ?: 0,
            'vote_count' => $stats['vote_count'] ?: 0
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $result]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '获取专题统计失败: ' . $e->getMessage()]);
}
?>