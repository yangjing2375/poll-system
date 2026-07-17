<?php
session_start();
require_once '../config/db.php';
setCORSHeaders();

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
if (!isset($_SESSION['user_id']) && !$is_admin) {
    echo json_encode(['status' => 'error', 'message' => '请先登录']);
    exit;
}

$user_id = $is_admin ? $_SESSION['admin_id'] : $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '请求方法错误']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['poll_id']) || !isset($data['option_ids']) || !is_array($data['option_ids'])) {
    echo json_encode(['status' => 'error', 'message' => '参数错误']);
    exit;
}

$poll_id = intval($data['poll_id']);
$option_ids = array_map('intval', $data['option_ids']);

try {
    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM polls WHERE id = ?");
    $stmt->execute([$poll_id]);
    if ($stmt->rowCount() == 0) {
        echo json_encode(['status' => 'error', 'message' => '投票不存在']);
        exit;
    }

    foreach ($option_ids as $option_id) {
        $stmt = $db->prepare("SELECT id FROM poll_options WHERE poll_id = ? AND id = ?");
        $stmt->execute([$poll_id, $option_id]);
        if ($stmt->rowCount() == 0) {
            echo json_encode(['status' => 'error', 'message' => '选项不存在']);
            exit;
        }
    }

    foreach ($option_ids as $option_id) {
        $stmt = $db->prepare("SELECT id FROM poll_votes WHERE poll_id = ? AND user_id = ? AND option_id = ?");
        $stmt->execute([$poll_id, $user_id, $option_id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => '您已对该选项投过票']);
            exit;
        }
    }

    foreach ($option_ids as $option_id) {
        $stmt = $db->prepare("INSERT INTO poll_votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$poll_id, $option_id, $user_id]);

        $stmt = $db->prepare("UPDATE poll_options SET vote_count = vote_count + 1 WHERE id = ?");
        $stmt->execute([$option_id]);
    }

    echo json_encode(['status' => 'success', 'message' => '投票成功']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => '服务器错误: ' . $e->getMessage()]);
}
?>