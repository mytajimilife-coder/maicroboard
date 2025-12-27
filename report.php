<?php
/**
 * 게시글/댓글 신고 API
 */

require_once 'config.php';

header('Content-Type: application/json');

// 로그인 확인
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => $lang['login_required'] ?? '로그인이 필요합니다.']);
    exit;
}

$report_type = $_POST['report_type'] ?? 'post'; // post 또는 comment
$bo_table = $_POST['bo_table'] ?? '';
$target_id = (int)($_POST['target_id'] ?? 0);
$reason = $_POST['reason'] ?? '';
$description = $_POST['description'] ?? '';
$reporter_id = $_SESSION['user'];

// 입력값 검증
if (empty($bo_table) || $target_id <= 0 || empty($reason)) {
    echo json_encode(['success' => false, 'message' => '필수 정보가 누락되었습니다.']);
    exit;
}

// 테이블명 검증
if (!preg_match('/^[a-zA-Z0-9_]+$/', $bo_table)) {
    echo json_encode(['success' => false, 'message' => '잘못된 게시판입니다.']);
    exit;
}

// 신고 타입 검증
if (!in_array($report_type, ['post', 'comment'])) {
    echo json_encode(['success' => false, 'message' => '잘못된 신고 타입입니다.']);
    exit;
}

$db = getDB();

try {
    // 중복 신고 확인 (같은 사용자가 같은 대상을 신고했는지)
    $stmt = $db->prepare("SELECT id FROM mb1_reports 
                          WHERE report_type = ? AND bo_table = ? AND target_id = ? AND reporter_id = ? AND status = 'pending'");
    $stmt->execute([$report_type, $bo_table, $target_id, $reporter_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => $lang['already_reported'] ?? '이미 신고하신 내용입니다.']);
        exit;
    }
    
    // 신고 기록 추가
    $stmt = $db->prepare("INSERT INTO mb1_reports (report_type, bo_table, target_id, reporter_id, reason, description) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$report_type, $bo_table, $target_id, $reporter_id, $reason, $description]);
    
    echo json_encode([
        'success' => true,
        'message' => $lang['report_success'] ?? '신고가 접수되었습니다. 관리자가 확인 후 조치하겠습니다.'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
}
