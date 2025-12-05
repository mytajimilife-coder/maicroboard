<?php
require_once 'config.php';
requireLogin();

$action = $_POST['action'] ?? '';
$wr_id = filter_var($_POST['wr_id'] ?? 0, FILTER_VALIDATE_INT);
$bo_table = $_POST['bo_table'] ?? '';

if (!$wr_id || !$bo_table) {
    die('잘못된 요청입니다.');
}

if ($action === 'insert') {
    $content = trim($_POST['co_content']);
    if (!$content) {
        die('내용을 입력해주세요.');
    }
    
    insertComment($bo_table, $wr_id, $_SESSION['user'], $content);
} elseif ($action === 'delete') {
    $co_id = filter_var($_POST['co_id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$co_id) {
        die('잘못된 요청입니다.');
    }
    
    // 본인 확인 또는 관리자 확인 필요 (여기서는 간단히 처리)
    // 실제로는 getComment($co_id)를 통해 작성자를 확인해야 함
    
    deleteComment($bo_table, $co_id);
}

header('Location: view.php?id=' . $wr_id . '&bo_table=' . $bo_table);
exit;
?>
