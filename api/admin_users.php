<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

try {
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        echo json_encode(['status' => 'error', 'message' => '无权限访问', 'session' => $_SESSION]);
        exit;
    }

    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $where = '';
        $searchParams = [];
        
        if ($search) {
            $where = "WHERE username LIKE ? OR email LIKE ?";
            $searchParams[] = "%$search%";
            $searchParams[] = "%$search%";
        }

        $stmt = $db->prepare("SELECT id, username, email, gender, birthday, age, created_at FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($searchParams);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT COUNT(*) FROM users $where");
        $stmt->execute($searchParams);
        $total = $stmt->fetchColumn();

        foreach ($users as &$user) {
            $user['gender_text'] = $user['gender'] === 'male' ? '男' : ($user['gender'] === 'female' ? '女' : '未设置');
            $user['is_admin'] = false;
            $stmt_check = $db->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt_check->execute([$user['username']]);
            if ($stmt_check->fetch()) {
                $user['is_admin'] = true;
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'add':
                    if (!isset($input['username']) || !isset($input['password']) || !isset($input['email'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少必填字段']);
                        exit;
                    }

                    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$input['username']]);
                    if ($stmt->fetch()) {
                        echo json_encode(['status' => 'error', 'message' => '用户名已存在']);
                        exit;
                    }

                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$input['email']]);
                    if ($stmt->fetch()) {
                        echo json_encode(['status' => 'error', 'message' => '邮箱已存在']);
                        exit;
                    }

                    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (username, password, email, gender, birthday, age) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $input['username'],
                        $hashedPassword,
                        $input['email'],
                        $input['gender'] ?? null,
                        $input['birthday'] ?? null,
                        $input['age'] ?? null
                    ]);

                    echo json_encode(['status' => 'success', 'message' => '用户添加成功']);
                    break;

                case 'delete':
                    if (!isset($input['id'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少用户ID']);
                        exit;
                    }

                    $stmt = $db->prepare("DELETE FROM poll_votes WHERE user_id = ?");
                    $stmt->execute([$input['id']]);

                    $stmt = $db->prepare("SELECT id FROM polls WHERE creator_id = ?");
                    $stmt->execute([$input['id']]);
                    $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($polls as $poll) {
                        $stmt = $db->prepare("DELETE FROM poll_options WHERE poll_id = ?");
                        $stmt->execute([$poll['id']]);
                        $stmt = $db->prepare("DELETE FROM poll_votes WHERE poll_id = ?");
                        $stmt->execute([$poll['id']]);
                    }

                    $stmt = $db->prepare("DELETE FROM polls WHERE creator_id = ?");
                    $stmt->execute([$input['id']]);

                    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$input['id']]);

                    echo json_encode(['status' => 'success', 'message' => '用户删除成功']);
                    break;

                case 'set_admin':
                    if (!isset($input['id']) || !isset($input['is_admin'])) {
                        echo json_encode(['status' => 'error', 'message' => '缺少参数']);
                        exit;
                    }

                    $stmt = $db->prepare("SELECT username, email, password FROM users WHERE id = ?");
                    $stmt->execute([$input['id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$user) {
                        echo json_encode(['status' => 'error', 'message' => '用户不存在']);
                        exit;
                    }

                    if ($input['is_admin']) {
                        $stmt = $db->prepare("SELECT id FROM admins WHERE username = ?");
                        $stmt->execute([$user['username']]);
                        if ($stmt->fetch()) {
                            echo json_encode(['status' => 'error', 'message' => '该用户已是管理员']);
                            exit;
                        }

                        $stmt = $db->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
                        $stmt->execute([$user['username'], $user['password'], $user['email']]);
                        echo json_encode(['status' => 'success', 'message' => '已设置为管理员']);
                    } else {
                        $stmt = $db->prepare("DELETE FROM admins WHERE username = ?");
                        $stmt->execute([$user['username']]);
                        echo json_encode(['status' => 'success', 'message' => '已取消管理员权限']);
                    }
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
