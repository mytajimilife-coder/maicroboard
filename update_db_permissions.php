<?php
/**
 * 게시판 권한 설정 기능을 위한 데이터베이스 업데이트
 * 
 * 이 스크립트는 mb1_board_config 테이블에 권한 관련 컬럼을 추가합니다:
 * - bo_read_level: 게시판 목록/글 읽기 권한 레벨
 * - bo_write_level: 글 쓰기 권한 레벨
 * - bo_list_level: 게시판 목록 보기 권한 레벨
 */

require_once 'config.php';

try {
    $db = getDB();
    
    // bo_read_level 컬럼 확인 및 추가
    $stmt = $db->query("SHOW COLUMNS FROM mb1_board_config LIKE 'bo_read_level'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_board_config ADD COLUMN bo_read_level TINYINT(4) NOT NULL DEFAULT 1 COMMENT '읽기 권한 레벨'");
        echo "✓ bo_read_level 컬럼이 추가되었습니다.<br>";
    } else {
        echo "✓ bo_read_level 컬럼이 이미 존재합니다.<br>";
    }
    
    // bo_write_level 컬럼 확인 및 추가
    $stmt = $db->query("SHOW COLUMNS FROM mb1_board_config LIKE 'bo_write_level'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_board_config ADD COLUMN bo_write_level TINYINT(4) NOT NULL DEFAULT 1 COMMENT '쓰기 권한 레벨'");
        echo "✓ bo_write_level 컬럼이 추가되었습니다.<br>";
    } else {
        echo "✓ bo_write_level 컬럼이 이미 존재합니다.<br>";
    }
    
    // bo_list_level 컬럼 확인 및 추가 (기본값 0: 비회원도 볼 수 있음)
    $stmt = $db->query("SHOW COLUMNS FROM mb1_board_config LIKE 'bo_list_level'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_board_config ADD COLUMN bo_list_level TINYINT(4) NOT NULL DEFAULT 0 COMMENT '목록 보기 권한 레벨'");
        echo "✓ bo_list_level 컬럼이 추가되었습니다. (기본값: 0 - 비회원 포함)<br>";
    } else {
        echo "✓ bo_list_level 컬럼이 이미 존재합니다.<br>";
    }
    
    echo "<br><strong>데이터베이스 업데이트가 완료되었습니다!</strong><br>";
    echo "<a href='admin/board.php'>게시판 관리로 이동</a>";
    
} catch (PDOException $e) {
    die("데이터베이스 업데이트 실패: " . $e->getMessage());
}
