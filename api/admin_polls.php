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
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? intval($_GET['status']) : -1;

        $where = '';
        $searchParams = [];
        
        if ($search) {
            $where = "WHERE (p.title LIKE ? OR p.description LIKE ?)";
            $searchParams[] = "%$search%";
            $searchParams[] = "%$search%";
        }
        
        if ($status >= 0) {
            $and = $where ? ' AND' : 'WHERE';
            $where .= " $and p.is_active = ?";
            $searchParams[] = $status;
        }

        $stmt = $db->prepare("SELECT p.id, p.title, p.description, p.topic, p.is_multiple, p.max_options, p.is_active, p.start_time, p.end_time, p.created_at, u.username as creator_name FROM polls p JOIN users u ON p.creator_id = u.id $where ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($searchParams);
        $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT COUNT(*) FROM polls p JOIN users u ON p.creator_id = u.id $where");
        $stmt->execute($searchParams);
        $total = $stmt->fetchColumn();

        foreach ($polls as &$poll) {
            $stmt = $db->prepare("SELECT id, option_text, vote_count FROM poll_options WHERE poll_id = ? ORDER BY id ASC");
            $stmt->execute([$poll['id']]);
            $poll['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $totalVotes = array_sum(array_column($poll['options'], 'vote_count'));
            $poll['total_votes'] = $totalVotes;
            
            $poll['status_text'] = $poll['is_active'] ? '进行中' : '已禁用';
        }

        echo json_encode([
            'status' => 'success',
            'data' => $polls,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'create':
                    if (!isset($input['title']) || empty($input['title'])) {
                        echo json_encode(['status' => 'error', 'message' => '请输入投票标题']);
                        exit;
                    }
                    if (!isset($input['options']) || !is_array($input['options']) || count($input['options']) < 2) {
                        echo json_encode(['status' => 'error', 'message' => '至少需要两个投票选项']);
                        exit;
                    }

                    $validOptions = array_filter($input['options'], function($opt) {
                        return !empty(trim($opt));
                    });

                    if (count($validOptions) < 2) {
                        echo json_encode(['status' => 'error', 'message' => '至少需要两个有效的投票选项']);
                        exit;
                    }

                    $db->beginTransaction();
                    
                    $stmt = $db->prepare("INSERT INTO polls (title, description, topic, creator_id, is_multiple, max_options, end_time, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $input['title'],
                        $input['description'] ?? '',
                        $input['topic'] ?? '',
                        $_SESSION['admin_id'],
                        $input['is_multiple'] ?? 0,
                        $input['max_options'] ?? 1,
                        $input['end_time'] ?? null,
                        $input['is_active'] ?? 1
                    ]);
                    
                    $pollId = $db->lastInsertId();
                    
                    $stmt = $db->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
                    foreach ($validOptions as $option) {
                        $stmt->execute([$pollId, trim($option)]);
                    }
                    
                    $db->commit();
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => '投票创建成功',
                        'poll_id' => $pollId
                    ]);
                    break;

                case 'edit':
                    if (!isset($input['id'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少投票ID']);
                        exit;
                    }
                    if (!isset($input['title']) || empty($input['title'])) {
                        echo json_encode(['status' => 'error', 'message' => '请输入投票标题']);
                        exit;
                    }
                    if (!isset($input['options']) || !is_array($input['options']) || count($input['options']) < 2) {
                        echo json_encode(['status' => 'error', 'message' => '至少需要两个投票选项']);
                        exit;
                    }

                    $validOptions = array_filter($input['options'], function($opt) {
                        return !empty(trim($opt));
                    });

                    if (count($validOptions) < 2) {
                        echo json_encode(['status' => 'error', 'message' => '至少需要两个有效的投票选项']);
                        exit;
                    }

                    $db->beginTransaction();
                    
                    $stmt = $db->prepare("UPDATE polls SET title = ?, description = ?, topic = ?, is_multiple = ?, max_options = ?, end_time = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([
                        $input['title'],
                        $input['description'] ?? '',
                        $input['topic'] ?? '',
                        $input['is_multiple'] ?? 0,
                        $input['max_options'] ?? 1,
                        $input['end_time'] ?? null,
                        $input['is_active'] ?? 1,
                        $input['id']
                    ]);

                    $stmt = $db->prepare("DELETE FROM poll_options WHERE poll_id = ?");
                    $stmt->execute([$input['id']]);
                    
                    $stmt = $db->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
                    foreach ($validOptions as $option) {
                        $stmt->execute([$input['id'], trim($option)]);
                    }
                    
                    $db->commit();
                    
                    echo json_encode(['status' => 'success', 'message' => '投票更新成功']);
                    break;

                case 'delete':
                    if (!isset($input['id'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少投票ID']);
                        exit;
                    }

                    $stmt = $db->prepare("DELETE FROM poll_votes WHERE poll_id = ?");
                    $stmt->execute([$input['id']]);

                    $stmt = $db->prepare("DELETE FROM poll_options WHERE poll_id = ?");
                    $stmt->execute([$input['id']]);

                    $stmt = $db->prepare("DELETE FROM polls WHERE id = ?");
                    $stmt->execute([$input['id']]);

                    echo json_encode(['status' => 'success', 'message' => '投票删除成功']);
                    break;

                case 'toggle_active':
                    if (!isset($input['id'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少投票ID']);
                        exit;
                    }

                    $stmt = $db->prepare("UPDATE polls SET is_active = 1 - is_active WHERE id = ?");
                    $stmt->execute([$input['id']]);

                    $stmt = $db->prepare("SELECT is_active FROM polls WHERE id = ?");
                    $stmt->execute([$input['id']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $statusText = $result['is_active'] ? '已启用' : '已禁用';

                    echo json_encode(['status' => 'success', 'message' => '投票' . $statusText]);
                    break;

                default:
                    echo json_encode(['status' => 'error', 'message' => '未知操作']);
                    break;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => '缺少操作类型']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => '方法不支持']);
    }
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => '服务器错误: ' . $e->getMessage()]);
}
?>