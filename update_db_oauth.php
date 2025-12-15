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
    echo $lang['oauth_config_table_created'] . "<br>";

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
    echo $lang['oauth_users_table_created'] . "<br>";

    // mb1_member 테이블에 oauth_provider 컬럼 추가 (이미 있으면 무시)
    $stmt = $db->query("SHOW COLUMNS FROM mb1_member LIKE 'oauth_provider'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE mb1_member ADD COLUMN oauth_provider varchar(50) DEFAULT NULL");
        echo $lang['oauth_provider_column_added'] . "<br>";
    } else {
        echo $lang['oauth_provider_column_exists'] . "<br>";
    }

    // 기본 OAuth 설정 데이터 삽입
    $providers = ['google', 'line', 'apple'];
    foreach ($providers as $provider) {
        $stmt = $db->prepare("INSERT IGNORE INTO mb1_oauth_config (provider, client_id, client_secret, enabled) VALUES (?, '', '', 0)");
        $stmt->execute([$provider]);
    }
    echo $lang['oauth_default_data_added'] . "<br>";
    
    echo "<br><strong>" . $lang['db_update_complete'] . "</strong>";
    
} catch (Exception $e) {
    echo $lang['error_occurred'] . ": " . $e->getMessage();
}
?>
