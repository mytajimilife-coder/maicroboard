<?php
require_once 'config.php';
requireLogin();

$bf_no = filter_var($_GET['bf_no'] ?? 0, FILTER_VALIDATE_INT);

if (!$bf_no) {
    die('잘못된 요청입니다.');
}

$file = getFile($bf_no);

if (!$file) {
    die('파일이 존재하지 않습니다.');
}

$filepath = 'data/file/' . $file['bf_file'];

if (!file_exists($filepath)) {
    die('파일을 찾을 수 없습니다.');
}

// 다운로드 횟수 증가 (필요하다면)
// incrementDownload($bf_no);

$original_name = $file['bf_source'];

// 브라우저별 한글 파일명 깨짐 방지
if (preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) && preg_match("/5\.5/", $_SERVER['HTTP_USER_AGENT'])) {
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . iconv('UTF-8', 'EUC-KR', $original_name) . "\"");
    header("Content-Transfer-Encoding: binary");
} else {
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . $original_name . "\"");
    header("Content-Transfer-Encoding: binary");
}

header("Content-Length: " . filesize($filepath));
header("Pragma: no-cache");
header("Expires: 0");

flush();
readfile($filepath);
?>
