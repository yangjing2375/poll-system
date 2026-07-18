<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

try {
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        echo json_encode(['status' => 'error', 'message' => '无权限访问']);
        exit;
    }

    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $type = isset($_GET['type']) ? $_GET['type'] : 'overview';

        switch ($type) {
            case 'overview':
                $stmt = $db->query("SELECT COUNT(*) as count FROM users");
                $totalUsers = $stmt->fetchColumn();

                $stmt = $db->query("SELECT COUNT(*) as count FROM polls");
                $totalPolls = $stmt->fetchColumn();

                $stmt = $db->query("SELECT COUNT(*) as count FROM polls WHERE is_active = 1");
                $activePolls = $stmt->fetchColumn();

                $stmt = $db->query("SELECT COUNT(*) as count FROM polls WHERE is_hot = 1");
                $hotPolls = $stmt->fetchColumn();

                $stmt = $db->query("SELECT COUNT(*) as count FROM poll_votes");
                $totalVotes = $stmt->fetchColumn();

                $stmt = $db->query("SELECT COUNT(DISTINCT user_id) as count FROM poll_votes");
                $totalVoters = $stmt->fetchColumn();

                $stmt = $db->query("SELECT COUNT(*) as count FROM admins");
                $totalAdmins = $stmt->fetchColumn();

                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'total_users' => $totalUsers,
                        'total_admins' => $totalAdmins,
                        'total_polls' => $totalPolls,
                        'active_polls' => $activePolls,
                        'hot_polls' => $hotPolls,
                        'total_votes' => $totalVotes,
                        'total_voters' => $totalVoters
                    ]
                ]);
                break;

            case 'topics':
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
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM polls WHERE topic = ?");
                    $stmt->execute([$topic]);
                    $pollCount = $stmt->fetchColumn();

                    $stmt = $db->prepare("SELECT COUNT(DISTINCT pv.user_id) as participant_count, COUNT(pv.id) as vote_count FROM poll_votes pv JOIN polls p ON pv.poll_id = p.id WHERE p.topic = ?");
                    $stmt->execute([$topic]);
                    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

                    $result[] = [
                        'topic' => $topic,
                        'poll_count' => $pollCount ?: 0,
                        'participant_count' => $stats['participant_count'] ?: 0,
                        'vote_count' => $stats['vote_count'] ?: 0
                    ];
                }

                echo json_encode(['status' => 'success', 'data' => $result]);
                break;

            case 'poll_ranking':
                $stmt = $db->prepare("SELECT p.id, p.title, p.topic, COUNT(pv.id) as vote_count, COUNT(DISTINCT pv.user_id) as participant_count FROM polls p LEFT JOIN poll_votes pv ON p.id = pv.poll_id GROUP BY p.id ORDER BY vote_count DESC LIMIT 5");
                $stmt->execute();
                $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['status' => 'success', 'data' => $polls]);
                break;

            case 'user_ranking':
                $stmt = $db->prepare("SELECT pv.user_id as id, COALESCE(u.username, a.username, '已删除用户') as username, COALESCE(u.email, a.email, '') as email, COUNT(pv.id) as vote_count, COUNT(DISTINCT pv.poll_id) as poll_count FROM poll_votes pv LEFT JOIN users u ON pv.user_id = u.id LEFT JOIN admins a ON pv.user_id = a.id GROUP BY pv.user_id ORDER BY poll_count DESC, vote_count DESC LIMIT 5");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['status' => 'success', 'data' => $users]);
                break;

            case 'recent_votes':
                $stmt = $db->prepare("SELECT pv.id, pv.voted_at, p.title as poll_title, COALESCE(u.username, a.username, '已删除用户') as user_name, po.option_text FROM poll_votes pv JOIN polls p ON pv.poll_id = p.id LEFT JOIN users u ON pv.user_id = u.id LEFT JOIN admins a ON pv.user_id = a.id JOIN poll_options po ON pv.option_id = po.id ORDER BY pv.voted_at DESC LIMIT 20");
                $stmt->execute();
                $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['status' => 'success', 'data' => $votes]);
                break;

            case 'daily_stats':
                $days = isset($_GET['days']) ? intval($_GET['days']) : 7;
                
                $result = [];
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM polls WHERE DATE(created_at) = ?");
                    $stmt->execute([$date]);
                    $pollCount = $stmt->fetchColumn();

                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM poll_votes WHERE DATE(voted_at) = ?");
                    $stmt->execute([$date]);
                    $voteCount = $stmt->fetchColumn();

                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?");
                    $stmt->execute([$date]);
                    $userCount = $stmt->fetchColumn();

                    $result[] = [
                        'date' => $date,
                        'poll_count' => $pollCount ?: 0,
                        'vote_count' => $voteCount ?: 0,
                        'user_count' => $userCount ?: 0
                    ];
                }

                echo json_encode(['status' => 'success', 'data' => $result]);
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => '未知统计类型']);
                break;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '方法不支持']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => '服务器错误: ' . $e->getMessage()]);
}
?>