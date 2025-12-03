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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo sprintf($lang['table_created'], 'mb1_config') . "<br>";

    // 기본 설정 데이터 추가
    $stmt = $db->query("SELECT COUNT(*) FROM mb1_config");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO mb1_config (cf_title, cf_use_point, cf_write_point) VALUES ('MaicroBoard', 0, 0)");
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
