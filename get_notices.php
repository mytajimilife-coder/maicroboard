<?php
header('Content-Type: application/json');

// 데이터베이스 연결
require_once 'config.php';

try {
    $db = getDB();

    // 활성화된 공지사항 가져오기
    $stmt = $db->query("SELECT id, title FROM mb1_notice WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC");
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'notices' => $notices
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
