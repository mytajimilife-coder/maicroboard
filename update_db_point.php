<?php
require_once 'config.php';

// 관리자 확인
if (!isAdmin()) {
    die('관리자만 실행할 수 있습니다.');
}

$db = getDB();

try {
    // 1. g5_member 테이블에 mb_point 컬럼 추가
    try {
        $db->exec("ALTER TABLE g5_member ADD COLUMN mb_point int(11) NOT NULL DEFAULT 0");
        echo "g5_member 테이블에 mb_point 컬럼 추가 완료<br>";
    } catch (PDOException $e) {
        // 이미 존재하는 경우 무시
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "g5_member 테이블에 mb_point 컬럼이 이미 존재합니다.<br>";
        } else {
            throw $e;
        }
    }

    // 2. g5_config 테이블 생성
    $db->exec("
        CREATE TABLE IF NOT EXISTS `g5_config` (
            `cf_id` int(11) NOT NULL AUTO_INCREMENT,
            `cf_title` varchar(255) NOT NULL DEFAULT 'MicroBoard',
            `cf_use_point` tinyint(1) NOT NULL DEFAULT 0,
            `cf_write_point` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`cf_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "g5_config 테이블 생성 완료<br>";

    // 기본 설정 데이터 추가
    $stmt = $db->query("SELECT COUNT(*) FROM g5_config");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO g5_config (cf_title, cf_use_point, cf_write_point) VALUES ('MicroBoard', 0, 0)");
        echo "기본 설정 데이터 추가 완료<br>";
    }

    // 3. g5_point 테이블 생성
    $db->exec("
        CREATE TABLE IF NOT EXISTS `g5_point` (
            `po_id` int(11) NOT NULL AUTO_INCREMENT,
            `mb_id` varchar(50) NOT NULL,
            `po_datetime` datetime NOT NULL,
            `po_content` varchar(255) NOT NULL,
            `po_point` int(11) NOT NULL DEFAULT 0,
            `po_rel_table` varchar(20) DEFAULT NULL,
            `po_rel_id` varchar(20) DEFAULT NULL,
            `po_rel_action` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`po_id`),
            KEY `index1` (`mb_id`, `po_rel_table`, `po_rel_id`, `po_rel_action`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "g5_point 테이블 생성 완료<br>";

    echo "<br><strong>모든 데이터베이스 업데이트가 완료되었습니다.</strong>";

} catch (PDOException $e) {
    echo "오류 발생: " . $e->getMessage();
}
?>
