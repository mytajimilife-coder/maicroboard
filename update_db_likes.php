<?php
/**
 * 게시글 추천/좋아요 데이터베이스 업데이트 스크립트
 * 
 * 이 스크립트는 다음 테이블을 생성합니다:
 * - mb1_post_likes: 게시글 추천 기록
 * - mb1_board_file 테이블에 다운로드 카운터 증가 로직 추가
 */

require_once 'config.php';

$db = getDB();
$messages = [];

try {
    // 1. 게시글 추천 테이블 생성
    $db->exec("CREATE TABLE IF NOT EXISTS `mb1_post_likes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bo_table` varchar(100) NOT NULL,
        `wr_id` int(11) NOT NULL,
        `mb_id` varchar(50) NOT NULL,
        `liked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_like` (`bo_table`, `wr_id`, `mb_id`),
        KEY `bo_table` (`bo_table`),
        KEY `wr_id` (`wr_id`),
        KEY `mb_id` (`mb_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $messages[] = "✅ mb1_post_likes 테이블이 생성되었습니다.";
    
    // 2. 모든 게시판 테이블에 wr_likes 컬럼 추가
    $stmt = $db->query("SELECT bo_table FROM mb1_board_config");
    $boards = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($boards as $bo_table) {
        $write_table = "mb1_write_" . $bo_table;
        
        // wr_likes 컬럼 추가
        try {
            $stmt = $db->query("SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_likes'");
            if ($stmt->rowCount() == 0) {
                $db->exec("ALTER TABLE `{$write_table}` ADD COLUMN `wr_likes` int(11) NOT NULL DEFAULT 0");
                $messages[] = "✅ {$write_table} 테이블에 wr_likes 컬럼이 추가되었습니다.";
            }
        } catch (Exception $e) {
            $messages[] = "⚠️ {$write_table} 테이블 업데이트 실패: " . $e->getMessage();
        }
    }
    
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
    <title>게시글 추천 시스템 설치</title>
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
        .warning {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
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
        <h1>🎯 게시글 추천 시스템 설치</h1>
        
        <?php foreach ($messages as $message): ?>
            <?php
            $class = 'success';
            if (strpos($message, '❌') !== false) $class = 'error';
            elseif (strpos($message, '⚠️') !== false) $class = 'warning';
            ?>
            <div class="message <?php echo $class; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endforeach; ?>
        
        <?php if ($success): ?>
            <div class="message success">
                <strong>✅ 설치가 완료되었습니다!</strong><br>
                이제 게시글에 추천/좋아요 기능을 사용할 수 있습니다.
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="btn">← 메인으로 돌아가기</a>
    </div>
</body>
</html>
