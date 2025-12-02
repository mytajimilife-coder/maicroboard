<?php
require_once 'config.php';

if (!isAdmin()) {
    die($lang['admin_only_exec']);
}

$db = getDB();

try {
    // OAuth 설정 테이블 생성
    $db->exec("
        CREATE TABLE IF NOT EXISTS `mb1_oauth_config` (
            `provider` varchar(50) NOT NULL,
            `client_id` varchar(255) NOT NULL,
            `client_secret` varchar(255) NOT NULL,
            `enabled` tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`provider`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ mb1_oauth_config 테이블 생성 완료<br>";
    
    // OAuth 사용자 연동 테이블 생성
    $db->exec("
        CREATE TABLE IF NOT EXISTS `mb1_oauth_users` (
            `mb_id` varchar(50) NOT NULL,
            `provider` varchar(50) NOT NULL,
            `provider_user_id` varchar(255) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`provider`, `provider_user_id`),
            KEY `mb_id` (`mb_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ mb1_oauth_users 테이블 생성 완료<br>";
    
    // mb1_member 테이블에 oauth_provider 컬럼 추가 (이미 있으면 무시)
    $stmt = $db->query("SHOW COLUMNS FROM mb1_member LIKE 'oauth_provider'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_member ADD COLUMN oauth_provider varchar(50) DEFAULT NULL");
        echo "✓ mb1_member 테이블에 oauth_provider 컬럼 추가 완료<br>";
    } else {
        echo "✓ mb1_member 테이블에 oauth_provider 컬럼이 이미 존재합니다.<br>";
    }
    
    // 기본 OAuth 설정 데이터 삽입
    $providers = ['google', 'line', 'apple'];
    foreach ($providers as $provider) {
        $stmt = $db->prepare("INSERT IGNORE INTO mb1_oauth_config (provider, client_id, client_secret, enabled) VALUES (?, '', '', 0)");
        $stmt->execute([$provider]);
    }
    echo "✓ 기본 OAuth 설정 데이터 추가 완료<br>";
    
    echo "<br><strong>" . $lang['db_update_complete'] . "</strong>";
    
} catch (Exception $e) {
    echo $lang['error_occurred'] . ": " . $e->getMessage();
}
?>
