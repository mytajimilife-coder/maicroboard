<?php
require_once 'config.php';

// 검색어
$keyword = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// SEO 메타 데이터
$page_title = $keyword ? "'{$keyword}' 검색 결과" : '통합검색';
$meta_description = $keyword ? "'{$keyword}'에 대한 검색 결과입니다." : 'MicroBoard 통합검색';

require_once 'inc/header.php';

$db = getDB();
$results = [];
$total_count = 0;

if ($keyword) {
    // 검색 반영된 게시판 목록 가져오기
    $stmt = $db->query("SELECT bo_table, bo_subject FROM mb1_board_config WHERE bo_use_search = 1");
    $search_boards = $stmt->fetchAll();
    
    if (!empty($search_boards)) {
        $union_queries = [];
        $count_queries = [];
        
        foreach ($search_boards as $board) {
            $table_name = "mb1_write_" . $board['bo_table'];
            
            // 테이블 존재 확인
            try {
                $db->query("SELECT 1 FROM {$table_name} LIMIT 1");
                
                $union_queries[] = "
                    SELECT 
                        '{$board['bo_table']}' as bo_table,
                        '{$board['bo_subject']}' as bo_subject,
                        wr_id,
                        wr_subject,
                        wr_content,
                        wr_name,
                        wr_datetime,
                        wr_hit
                    FROM {$table_name}
                    WHERE wr_subject LIKE :keyword OR wr_content LIKE :keyword
                ";
                
                $count_queries[] = "SELECT COUNT(*) FROM {$table_name} WHERE wr_subject LIKE :keyword OR wr_content LIKE :keyword";
            } catch (Exception $e) {
                // 테이블이 없으면 건너뛰기
                continue;
            }
        }
        
        if (!empty($union_queries)) {
            // 전체 개수 구하기
            $count_sql = "SELECT SUM(cnt) as total FROM (" . implode(" UNION ALL ", array_map(function($q) {
                return "SELECT ({$q}) as cnt";
            }, $count_queries)) . ") as counts";
            
            $stmt = $db->prepare($count_sql);
            $stmt->execute(['keyword' => "%{$keyword}%"]);
            $total_count = $stmt->fetchColumn();
            
            // 검색 결과 가져오기
            $search_sql = implode(" UNION ALL ", $union_queries) . " ORDER BY wr_datetime DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($search_sql);
            $stmt->bindValue(':keyword', "%{$keyword}%", PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
        }
    }
}

$total_pages = $total_count > 0 ? ceil($total_count / $limit) : 0;
?>

<style>
.search-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.search-header {
    text-align: center;
    margin-bottom: 2rem;
}

.search-header h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.search-box {
    max-width: 600px;
    margin: 0 auto 2rem;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 1rem 3.5rem 1rem 1.5rem;
    font-size: 1.1rem;
    border: 2px solid var(--border-color);
    border-radius: 50px;
    background: var(--bg-secondary);
    color: var(--text-color);
    transition: all 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.search-box button {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: opacity 0.2s;
}

.search-box button:hover {
    opacity: 0.9;
}

.search-info {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--text-light);
    font-size: 1rem;
}

.search-info strong {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.search-results {
    background: var(--bg-secondary);
    border-radius: var(--radius);
    overflow: hidden;
}

.search-result-item {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.2s;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover {
    background: var(--bg-tertiary);
}

.result-board {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.result-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.result-title a {
    color: var(--text-color);
    text-decoration: none;
}

.result-title a:hover {
    color: var(--primary-color);
}

.result-content {
    color: var(--text-light);
    margin-bottom: 0.75rem;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.result-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: var(--text-light);
}

.no-results {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-light);
}

.no-results-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--text-color);
    background: var(--bg-secondary);
    transition: all 0.2s;
}

.pagination a:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.pagination .current {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.highlight {
    background: rgba(255, 235, 59, 0.3);
    font-weight: 600;
}
</style>

<div class="search-container">
    <div class="search-header">
        <h1>🔍 <?php echo $lang['integrated_search'] ?? '통합검색'; ?></h1>
    </div>
    
    <form method="get" class="search-box">
        <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" 
               placeholder="<?php echo $lang['search_placeholder'] ?? '검색어를 입력하세요...'; ?>" 
               required autofocus>
        <button type="submit">🔍 <?php echo $lang['search'] ?? '검색'; ?></button>
    </form>
    
    <?php if ($keyword): ?>
        <div class="search-info">
            <strong><?php echo number_format($total_count); ?></strong><?php echo $lang['search_results_count'] ?? '개의 검색 결과'; ?>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="search-results">
                <?php foreach ($results as $result): ?>
                    <div class="search-result-item">
                        <div class="result-board"><?php echo htmlspecialchars($result['bo_subject']); ?></div>
                        <div class="result-title">
                            <a href="view.php?bo_table=<?php echo urlencode($result['bo_table']); ?>&id=<?php echo $result['wr_id']; ?>">
                                <?php 
                                $title = htmlspecialchars($result['wr_subject']);
                                // 검색어 하이라이트
                                $title = preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<span class="highlight">$1</span>', $title);
                                echo $title;
                                ?>
                            </a>
                        </div>
                        <div class="result-content">
                            <?php 
                            $content = strip_tags($result['wr_content']);
                            $content = mb_substr($content, 0, 200, 'UTF-8');
                            $content = htmlspecialchars($content);
                            // 검색어 하이라이트
                            $content = preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<span class="highlight">$1</span>', $content);
                            echo $content . '...';
                            ?>
                        </div>
                        <div class="result-meta">
                            <span>👤 <?php echo htmlspecialchars($result['wr_name']); ?></span>
                            <span>📅 <?php echo date('Y-m-d H:i', strtotime($result['wr_datetime'])); ?></span>
                            <span>👁️ <?php echo number_format($result['wr_hit']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?q=<?php echo urlencode($keyword); ?>&page=<?php echo $page - 1; ?>">‹ <?php echo $lang['prev'] ?? '이전'; ?></a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?q=<?php echo urlencode($keyword); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?q=<?php echo urlencode($keyword); ?>&page=<?php echo $page + 1; ?>"><?php echo $lang['next'] ?? '다음'; ?> ›</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h3><?php echo $lang['no_search_results'] ?? '검색 결과가 없습니다'; ?></h3>
                <p><?php echo $lang['no_search_results_desc'] ?? '다른 검색어로 시도해보세요.'; ?></p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <div class="no-results-icon">🔍</div>
            <h3><?php echo $lang['search_guide'] ?? '검색어를 입력하세요'; ?></h3>
            <p><?php echo $lang['search_guide_desc'] ?? '모든 게시판에서 제목과 내용을 검색합니다.'; ?></p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'inc/footer.php'; ?>
