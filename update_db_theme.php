<?php
require_once 'config.php';

try {
    $db = getDB();
    
    // 컬럼 존재 여부 확인 및 추가
    $columns = [
        'cf_theme' => "VARCHAR(20) NOT NULL DEFAULT 'light'",
        'cf_bg_type' => "VARCHAR(20) NOT NULL DEFAULT 'color'",
        'cf_bg_value' => "VARCHAR(255) NOT NULL DEFAULT '#ffffff'"
    ];
    
    foreach ($columns as $col => $def) {
        try {
            $db->query("SELECT $col FROM mb1_config LIMIT 1");
        } catch (PDOException $e) {
            // 컬럼이 없으면 추가
            $db->exec("ALTER TABLE mb1_config ADD COLUMN $col $def");
            echo "Added column: $col<br>";
        }
    }
    
    echo "Database updated successfully for theme settings.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
