<?php
/**
 * 방문자 추적 스크립트
 * 모든 페이지에서 include하여 사용
 */

// 이미 추적된 경우 중복 실행 방지
if (defined('VISIT_TRACKED')) {
    return;
}
define('VISIT_TRACKED', true);

// 봇/크롤러 제외
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$bot_patterns = ['bot', 'crawl', 'spider', 'slurp', 'facebook', 'google'];
$is_bot = false;

foreach ($bot_patterns as $pattern) {
    if (stripos($user_agent, $pattern) !== false) {
        $is_bot = true;
        break;
    }
}

if ($is_bot) {
    return;
}

try {
    $db = getDB();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $today = date('Y-m-d');
    $page_url = $_SERVER['REQUEST_URI'] ?? '';
    
    // 방문 로그 기록 (중복 방지: 같은 IP, 같은 날짜)
    $stmt = $db->prepare("INSERT IGNORE INTO mb1_visit_log (ip_address, user_agent, visit_date, page_url) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$ip_address, $user_agent, $today, $page_url]);
    
    // 오늘 통계 업데이트
    // 1. 총 방문 수 증가
    $stmt = $db->prepare("INSERT INTO mb1_visit_stats (visit_date, visit_count, unique_visitors, page_views) 
                          VALUES (?, 1, 0, 1) 
                          ON DUPLICATE KEY UPDATE 
                          visit_count = visit_count + 1,
                          page_views = page_views + 1");
    $stmt->execute([$today]);
    
    // 2. 순 방문자 수 계산 (오늘 날짜의 unique IP 수)
    $stmt = $db->prepare("SELECT COUNT(DISTINCT ip_address) as unique_count 
                          FROM mb1_visit_log 
                          WHERE visit_date = ?");
    $stmt->execute([$today]);
    $result = $stmt->fetch();
    
    if ($result) {
        $stmt = $db->prepare("UPDATE mb1_visit_stats SET unique_visitors = ? WHERE visit_date = ?");
        $stmt->execute([$result['unique_count'], $today]);
    }
    
} catch (Exception $e) {
    // 오류 무시 (방문자 추적 실패해도 페이지는 정상 작동)
    error_log("Visit tracking error: " . $e->getMessage());
}
