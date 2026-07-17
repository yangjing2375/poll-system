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
        $isHot = isset($_GET['is_hot']) ? intval($_GET['is_hot']) : -1;

        $where = '';
        $searchParams = [];
        
        if ($search) {
            $where = "WHERE (p.title LIKE ? OR p.description LIKE ?)";
            $searchParams[] = "%$search%";
            $searchParams[] = "%$search%";
        }
        
        if ($isHot >= 0) {
            $and = $where ? ' AND' : 'WHERE';
            $where .= " $and p.is_hot = ?";
            $searchParams[] = $isHot;
        }

        $stmt = $db->prepare("SELECT p.id, p.title, p.description, p.topic, p.is_multiple, p.is_hot, p.is_active, p.created_at, u.username as creator_name FROM polls p JOIN users u ON p.creator_id = u.id $where ORDER BY p.is_hot DESC, p.created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($searchParams);
        $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT COUNT(*) FROM polls p JOIN users u ON p.creator_id = u.id $where");
        $stmt->execute($searchParams);
        $total = $stmt->fetchColumn();

        foreach ($polls as &$poll) {
            $stmt = $db->prepare("SELECT SUM(vote_count) as total FROM poll_options WHERE poll_id = ?");
            $stmt->execute([$poll['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $poll['total_votes'] = $result['total'] ?: 0;
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
                case 'toggle_hot':
                    if (!isset($input['id'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少投票ID']);
                        exit;
                    }

                    $stmt = $db->prepare("UPDATE polls SET is_hot = 1 - is_hot WHERE id = ?");
                    $stmt->execute([$input['id']]);

                    $stmt = $db->prepare("SELECT is_hot FROM polls WHERE id = ?");
                    $stmt->execute([$input['id']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $statusText = $result['is_hot'] ? '已设为热门' : '已取消热门';

                    echo json_encode(['status' => 'success', 'message' => '投票' . $statusText]);
                    break;

                case 'batch_set_hot':
                    if (!isset($input['ids']) || !is_array($input['ids'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少投票ID列表']);
                        exit;
                    }

                    $placeholders = rtrim(str_repeat('?,', count($input['ids'])), ',');
                    $stmt = $db->prepare("UPDATE polls SET is_hot = 1 WHERE id IN ($placeholders)");
                    $stmt->execute($input['ids']);

                    echo json_encode(['status' => 'success', 'message' => '批量设为热门成功']);
                    break;

                case 'batch_cancel_hot':
                    if (!isset($input['ids']) || !is_array($input['ids'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少投票ID列表']);
                        exit;
                    }

                    $placeholders = rtrim(str_repeat('?,', count($input['ids'])), ',');
                    $stmt = $db->prepare("UPDATE polls SET is_hot = 0 WHERE id IN ($placeholders)");
                    $stmt->execute($input['ids']);

                    echo json_encode(['status' => 'success', 'message' => '批量取消热门成功']);
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
    echo json_encode(['status' => 'error', 'message' => '服务器错误: ' . $e->getMessage()]);
}
?>