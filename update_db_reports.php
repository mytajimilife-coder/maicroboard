<?php
/**
 * 신고 시스템 데이터베이스 업데이트 스크립트
 * 
 * 이 스크립트는 다음 테이블을 생성합니다:
 * - mb1_reports: 게시글/댓글 신고 기록
 */

require_once 'config.php';

$db = getDB();
$messages = [];

try {
    // 신고 테이블 생성
    $db->exec("CREATE TABLE IF NOT EXISTS `mb1_reports` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `report_type` enum('post','comment') NOT NULL DEFAULT 'post',
        `bo_table` varchar(100) NOT NULL,
        `target_id` int(11) NOT NULL COMMENT '게시글 ID 또는 댓글 ID',
        `reporter_id` varchar(50) NOT NULL COMMENT '신고자 ID',
        `reason` varchar(50) NOT NULL COMMENT '신고 사유',
        `description` text COMMENT '상세 설명',
        `status` enum('pending','reviewed','resolved','rejected') NOT NULL DEFAULT 'pending',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `reviewed_at` datetime DEFAULT NULL,
        `reviewed_by` varchar(50) DEFAULT NULL COMMENT '처리한 관리자',
        `admin_note` text COMMENT '관리자 메모',
        PRIMARY KEY (`id`),
        KEY `report_type` (`report_type`),
        KEY `bo_table` (`bo_table`),
        KEY `target_id` (`target_id`),
        KEY `reporter_id` (`reporter_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $messages[] = "✅ mb1_reports 테이블이 생성되었습니다.";
    
    // 방문자 통계 테이블 생성
    $db->exec("CREATE TABLE IF NOT EXISTS `mb1_visit_stats` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `visit_date` date NOT NULL,
        `visit_count` int(11) NOT NULL DEFAULT 0,
        `unique_visitors` int(11) NOT NULL DEFAULT 0,
        `page_views` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `visit_date` (`visit_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $messages[] = "✅ mb1_visit_stats 테이블이 생성되었습니다.";
    
    // 방문자 로그 테이블 생성 (일별 unique visitor 추적용)
    $db->exec("CREATE TABLE IF NOT EXISTS `mb1_visit_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ip_address` varchar(45) NOT NULL,
        `user_agent` varchar(255) DEFAULT NULL,
        `visit_date` date NOT NULL,
        `visit_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `page_url` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_daily_visit` (`ip_address`, `visit_date`),
        KEY `visit_date` (`visit_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $messages[] = "✅ mb1_visit_log 테이블이 생성되었습니다.";
    
    $success = true;
} catch (Exception $e) {
    $messages[] = "❌ 오류 발생: " . $e->getMessage();
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>신고 시스템 및 방문자 통계 설치</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .message {
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            border-radius: 4px;
            border-left: 4px solid;
        }
        .success {
            background: #dcfce7;
            border-color: #16a34a;
            color: #15803d;
        }
        .error {
            background: #fee2e2;
            border-color: #ef4444;
            color: #b91c1c;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚨 신고 시스템 및 방문자 통계 설치</h1>
        
        <?php foreach ($messages as $message): ?>
            <?php
            $class = 'success';
            if (strpos($message, '❌') !== false) $class = 'error';
            ?>
            <div class="message <?php echo $class; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endforeach; ?>
        
        <?php if ($success): ?>
            <div class="message success">
                <strong>✅ 설치가 완료되었습니다!</strong><br>
                이제 신고 시스템과 방문자 통계 기능을 사용할 수 있습니다.
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="btn">← 메인으로 돌아가기</a>
        <a href="admin/" class="btn" style="background: #10b981;">관리자 페이지로 이동</a>
    </div>
</body>
</html>
