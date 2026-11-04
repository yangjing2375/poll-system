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

if (!isset($data['poll_id']) || !isset($data['option_ids']) || !is_array($data['option_ids']) || count($data['option_ids']) === 0) {
    echo json_encode(['status' => 'error', 'message' => '参数错误']);
    exit;
}

$poll_id = intval($data['poll_id']);
$option_ids = array_values(array_unique(array_map('intval', $data['option_ids'])));

try {
    $db = getDB();

    // 校验投票存在、是否启用、是否已结束
    $stmt = $db->prepare("SELECT id, is_active, end_time, is_multiple, max_options FROM polls WHERE id = ?");
    $stmt->execute([$poll_id]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$poll) {
        echo json_encode(['status' => 'error', 'message' => '投票不存在']);
        exit;
    }
    if (!$poll['is_active']) {
        echo json_encode(['status' => 'error', 'message' => '该投票已禁用']);
        exit;
    }
    if (!empty($poll['end_time']) && strtotime($poll['end_time']) < time()) {
        echo json_encode(['status' => 'error', 'message' => '该投票已结束']);
        exit;
    }

    // 单选只允许提交一个选项，多选不得超过 max_options
    $maxOptions = $poll['is_multiple'] ? max(1, intval($poll['max_options'])) : 1;
    if (count($option_ids) > $maxOptions) {
        echo json_encode(['status' => 'error', 'message' => '最多只能选择 ' . $maxOptions . ' 项']);
        exit;
    }

    // 校验选项均属于该投票
    $placeholders = implode(',', array_fill(0, count($option_ids), '?'));
    $params = array_merge([$poll_id], $option_ids);
    $stmt = $db->prepare("SELECT COUNT(*) FROM poll_options WHERE poll_id = ? AND id IN ($placeholders)");
    $stmt->execute($params);
    if (intval($stmt->fetchColumn()) !== count($option_ids)) {
        echo json_encode(['status' => 'error', 'message' => '选项不存在']);
        exit;
    }

    // 同一用户对同一投票只允许提交一次
    $stmt = $db->prepare("SELECT COUNT(*) FROM poll_votes WHERE poll_id = ? AND user_id = ?");
    $stmt->execute([$poll_id, $user_id]);
    if (intval($stmt->fetchColumn()) > 0) {
        echo json_encode(['status' => 'error', 'message' => '您已参与过该投票']);
        exit;
    }

    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO poll_votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
    $stmtCount = $db->prepare("UPDATE poll_options SET vote_count = vote_count + 1 WHERE id = ?");
    foreach ($option_ids as $option_id) {
        $stmt->execute([$poll_id, $option_id, $user_id]);
        $stmtCount->execute([$option_id]);
    }
    $db->commit();

    echo json_encode(['status' => 'success', 'message' => '投票成功']);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    Logger::error('投票失败', ['poll_id' => $poll_id, 'user_id' => $user_id, 'error' => $e->getMessage()]);
    echo json_encode(['status' => 'error', 'message' => '投票失败，请稍后重试']);
}
?>
