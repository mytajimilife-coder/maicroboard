<?php
require_once 'config.php';

// 언어 파일 로드
$lang_code = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko';
$lang_file = "lang/{$lang_code}.php";
if (file_exists($lang_file)) {
    $lang = require $lang_file;
} else {
    $lang = require 'lang/ko.php';
}

// 관리자 확인
if (!isAdmin()) {
    die($lang['admin_only_exec']);
}

$db = getDB();

try {
    // 1. mb1_member 테이블에 mb_point 컬럼 추가
    try {
        $db->exec("ALTER TABLE mb1_member ADD COLUMN mb_point int(11) NOT NULL DEFAULT 0");
        echo sprintf($lang['column_added'], 'mb1_member', 'mb_point') . "<br>";
    } catch (PDOException $e) {
        // 이미 존재하는 경우 무시
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo sprintf($lang['column_exists'], 'mb1_member', 'mb_point') . "<br>";
        } else {
            throw $e;
        }
    }

    // 2. mb1_config 테이블 생성
    $db->exec("
        CREATE TABLE IF NOT EXISTS `mb1_config` (
            `cf_id` int(11) NOT NULL AUTO_INCREMENT,
            `cf_title` varchar(255) NOT NULL DEFAULT 'MicroBoard',
            `cf_use_point` tinyint(1) NOT NULL DEFAULT 0,
            `cf_write_point` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`cf_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo sprintf($lang['table_created'], 'mb1_config') . "<br>";

    // 기본 설정 데이터 추가
    $stmt = $db->query("SELECT COUNT(*) FROM mb1_config");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO mb1_config (cf_title, cf_use_point, cf_write_point) VALUES ('MicroBoard', 0, 0)");
        echo $lang['data_added'] . "<br>";
    }

    // 3. mb1_point 테이블 생성
    $db->exec("
        CREATE TABLE IF NOT EXISTS `mb1_point` (
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
    echo sprintf($lang['table_created'], 'mb1_point') . "<br>";

    echo "<br><strong>" . $lang['db_update_complete'] . "</strong>";

} catch (PDOException $e) {
    echo $lang['error_occurred'] . ": " . $e->getMessage();
}
?>
