<?php
/**
 * 통합검색 기능을 위한 데이터베이스 업데이트
 * 
 * 이 스크립트는 mb1_board_config 테이블에 검색 반영 여부 컬럼을 추가합니다.
 */

require_once 'config.php';

try {
    $db = getDB();
    
    // bo_use_search 컬럼 확인 및 추가
    $stmt = $db->query("SHOW COLUMNS FROM mb1_board_config LIKE 'bo_use_search'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_board_config ADD COLUMN bo_use_search TINYINT(1) NOT NULL DEFAULT 1 COMMENT '통합검색 반영 여부'");
        echo "✓ bo_use_search 컬럼이 추가되었습니다. (기본값: 1 - 검색 반영)<br>";
    } else {
        echo "✓ bo_use_search 컬럼이 이미 존재합니다.<br>";
    }
    
    echo "<br><strong>데이터베이스 업데이트가 완료되었습니다!</strong><br>";
    echo "<a href='search.php'>통합검색 페이지로 이동</a> | ";
    echo "<a href='admin/board.php'>게시판 관리로 이동</a>";
    
} catch (PDOException $e) {
    die("데이터베이스 업데이트 실패: " . $e->getMessage());
}
