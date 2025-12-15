<?php
/**
 * SEO 및 분석 도구 설정을 위한 데이터베이스 업데이트
 * 
 * 이 스크립트는 mb1_seo_config 테이블을 생성합니다.
 */

require_once 'config.php';

try {
    $db = getDB();
    
    // SEO 설정 테이블 생성
    $sql = "CREATE TABLE IF NOT EXISTS `mb1_seo_config` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `bing_verification` VARCHAR(255) DEFAULT NULL COMMENT 'Bing 웹마스터 인증 코드',
        `google_search_console` VARCHAR(255) DEFAULT NULL COMMENT 'Google Search Console 인증 코드',
        `google_analytics` TEXT DEFAULT NULL COMMENT 'Google Analytics 스크립트',
        `google_tag_manager` VARCHAR(255) DEFAULT NULL COMMENT 'Google Tag Manager ID',
        `google_adsense` TEXT DEFAULT NULL COMMENT 'Google AdSense 스크립트',
        `header_script` TEXT DEFAULT NULL COMMENT '헤더 추가 스크립트',
        `footer_script` TEXT DEFAULT NULL COMMENT '푸터 추가 스크립트',
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $db->exec($sql);
    echo "✓ mb1_seo_config 테이블이 생성되었습니다.<br>";
    
    // 기본 레코드 삽입 (없을 경우)
    $stmt = $db->query("SELECT COUNT(*) FROM mb1_seo_config");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO mb1_seo_config (id) VALUES (1)");
        echo "✓ 기본 SEO 설정 레코드가 생성되었습니다.<br>";
    }
    
    echo "<br><strong>데이터베이스 업데이트가 완료되었습니다!</strong><br>";
    echo "<a href='admin/seo.php'>SEO 설정으로 이동</a>";
    
} catch (PDOException $e) {
    die("데이터베이스 업데이트 실패: " . $e->getMessage());
}
