<?php
require_once 'config.php';

header('Content-Type: application/xml; charset=utf-8');

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
// dirname이 '\'를 반환할 수 있으므로 '/'로 변환 및 끝에 '/' 제거
$base_url = rtrim(str_replace('\\', '/', $base_url), '/');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- 메인 페이지 -->
    <url>
        <loc><?php echo $base_url; ?>/list.php</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <?php
    try {
        $db = getDB();
        
        // 모든 게시판 가져오기
        $stmt = $db->query("SELECT bo_table, bo_subject FROM mb1_board_config");
        $boards = $stmt->fetchAll();
        
        foreach ($boards as $board) {
            $bo_table = $board['bo_table'];
            
            // 게시판 목록 페이지
            echo "<url>\n";
            echo "  <loc>{$base_url}/list.php?bo_table={$bo_table}</loc>\n";
            echo "  <changefreq>daily</changefreq>\n";
            echo "  <priority>0.8</priority>\n";
            echo "</url>\n";
            
            // 게시글 가져오기 (최신 100개만)
            $write_table = "mb1_write_" . $bo_table;
            
            // 테이블 존재 여부 확인
            try {
                $check = $db->query("SELECT 1 FROM {$write_table} LIMIT 1");
            } catch (Exception $e) {
                continue; // 테이블이 없으면 건너뜀
            }
            
            $stmt_post = $db->query("SELECT wr_id, wr_datetime FROM {$write_table} ORDER BY wr_id DESC LIMIT 100");
            while ($post = $stmt_post->fetch()) {
                $wr_id = $post['wr_id'];
                $date = date('Y-m-d', strtotime($post['wr_datetime']));
                
                echo "<url>\n";
                echo "  <loc>{$base_url}/view.php?bo_table={$bo_table}&amp;id={$wr_id}</loc>\n";
                echo "  <lastmod>{$date}</lastmod>\n";
                echo "  <changefreq>weekly</changefreq>\n";
                echo "  <priority>0.6</priority>\n";
                echo "</url>\n";
            }
        }
    } catch (Exception $e) {
        // 에러 무시 (XML 깨짐 방지)
    }
    ?>
</urlset>
