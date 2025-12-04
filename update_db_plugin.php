<?php
require_once 'config.php';

try {
    $db = getDB();
    
    // bo_plugins 컬럼 추가 (mb1_board_config 테이블)
    try {
        $db->query("SELECT bo_plugins FROM mb1_board_config LIMIT 1");
    } catch (PDOException $e) {
        // 컬럼이 없으면 추가
        $db->exec("ALTER TABLE mb1_board_config ADD COLUMN bo_plugins VARCHAR(255) NOT NULL DEFAULT ''");
        echo "Added column: bo_plugins to mb1_board_config table.<br>";
    }
    
    echo "Database updated successfully for plugin system.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
