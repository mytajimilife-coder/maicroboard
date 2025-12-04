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
    // 1. mb1_member 테이블에 mb_level 컬럼 추가 (회원등급: 1-10)
    $stmt = $db->query("SHOW COLUMNS FROM mb1_member LIKE 'mb_level'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_member ADD COLUMN mb_level int(11) NOT NULL DEFAULT 1");
        echo sprintf($lang['column_added'], 'mb1_member', 'mb_level') . "<br>";
    } else {
        echo sprintf($lang['column_exists'], 'mb1_member', 'mb_level') . "<br>";
    }

    // 2. mb1_member 테이블에 mb_blocked 컬럼 추가 (차단 여부)
    $stmt = $db->query("SHOW COLUMNS FROM mb1_member LIKE 'mb_blocked'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_member ADD COLUMN mb_blocked tinyint(1) NOT NULL DEFAULT 0");
        echo sprintf($lang['column_added'], 'mb1_member', 'mb_blocked') . "<br>";
    } else {
        echo sprintf($lang['column_exists'], 'mb1_member', 'mb_blocked') . "<br>";
    }

    // 3. mb1_member 테이블에 mb_blocked_reason 컬럼 추가 (차단 사유)
    $stmt = $db->query("SHOW COLUMNS FROM mb1_member LIKE 'mb_blocked_reason'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_member ADD COLUMN mb_blocked_reason varchar(255) DEFAULT NULL");
        echo sprintf($lang['column_added'], 'mb1_member', 'mb_blocked_reason') . "<br>";
    } else {
        echo sprintf($lang['column_exists'], 'mb1_member', 'mb_blocked_reason') . "<br>";
    }

    // 4. mb1_member 테이블에 mb_leave_date 컬럼 추가 (탈퇴일)
    $stmt = $db->query("SHOW COLUMNS FROM mb1_member LIKE 'mb_leave_date'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_member ADD COLUMN mb_leave_date datetime DEFAULT NULL");
        echo sprintf($lang['column_added'], 'mb1_member', 'mb_leave_date') . "<br>";
    } else {
        echo sprintf($lang['column_exists'], 'mb1_member', 'mb_leave_date') . "<br>";
    }

    // 5. 관리자 계정의 등급을 10으로 설정
    $db->exec("UPDATE mb1_member SET mb_level = 10 WHERE mb_id = 'admin'");
    echo "✓ 관리자 계정 등급을 10으로 설정했습니다.<br>";

    echo "<br><strong>" . $lang['db_update_complete'] . "</strong>";
    echo "<br><br><a href='admin/index.php' class='btn'>관리자 페이지로 돌아가기</a>";

} catch (PDOException $e) {
    echo $lang['error_occurred'] . ": " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>회원 기능 업데이트</title>
    <link rel="stylesheet" href="skin/default/style.css">
</head>
<body>
</body>
</html>
