<?php
define('IN_ADMIN', true);
$admin_title_key = 'visit_stats';
require_once 'common.php';

$db = getDB();

// 기간 설정
$period = $_GET['period'] ?? '30'; // 기본 30일
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime("-{$period} days"));

// 방문자 통계 조회
$stmt = $db->prepare("SELECT * FROM mb1_visit_stats 
                      WHERE visit_date BETWEEN ? AND ? 
                      ORDER BY visit_date DESC");
$stmt->execute([$start_date, $end_date]);
$stats = $stmt->fetchAll();

// 총계 계산
$total_visits = 0;
$total_unique = 0;
$total_pageviews = 0;

foreach ($stats as $stat) {
    $total_visits += $stat['visit_count'];
    $total_unique += $stat['unique_visitors'];
    $total_pageviews += $stat['page_views'];
}

$avg_visits = count($stats) > 0 ? round($total_visits / count($stats)) : 0;
$avg_unique = count($stats) > 0 ? round($total_unique / count($stats)) : 0;
$avg_pageviews = count($stats) > 0 ? round($total_pageviews / count($stats)) : 0;

// 오늘 통계
$today_stats = null;
foreach ($stats as $stat) {
    if ($stat['visit_date'] === date('Y-m-d')) {
        $today_stats = $stat;
        break;
    }
}
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.stat-card {
    background: var(--bg-secondary);
    padding: 1.5rem;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
}
.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}
.stat-label {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-top: 0.5rem;
}
.period-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.period-tab {
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    text-decoration: none;
    background: var(--bg-secondary);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    transition: all 0.2s;
}
.period-tab.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}
.chart-container {
    background: var(--bg-secondary);
    padding: 1.5rem;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    margin-bottom: 2rem;
}
</style>

<div class="admin-card">
    <h2 style="margin-top: 0; margin-bottom: 1.5rem; color: var(--secondary-color);">📊 방문자 통계</h2>
    
    <!-- 오늘 통계 -->
    <?php if ($today_stats): ?>
        <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 1.5rem; border-radius: var(--radius); margin-bottom: 2rem;">
            <h3 style="margin-top: 0; margin-bottom: 1rem;">📅 오늘의 통계</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo number_format($today_stats['visit_count']); ?></div>
                    <div style="opacity: 0.9; font-size: 0.9rem;">총 방문</div>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo number_format($today_stats['unique_visitors']); ?></div>
                    <div style="opacity: 0.9; font-size: 0.9rem;">순 방문자</div>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo number_format($today_stats['page_views']); ?></div>
                    <div style="opacity: 0.9; font-size: 0.9rem;">페이지뷰</div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- 기간별 총계 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($total_visits); ?></div>
            <div class="stat-label">총 방문 (<?php echo $period; ?>일)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #10b981;"><?php echo number_format($total_unique); ?></div>
            <div class="stat-label">순 방문자</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #f59e0b;"><?php echo number_format($total_pageviews); ?></div>
            <div class="stat-label">페이지뷰</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #8b5cf6;"><?php echo number_format($avg_visits); ?></div>
            <div class="stat-label">일평균 방문</div>
        </div>
    </div>
    
    <!-- 기간 선택 -->
    <div class="period-tabs">
        <a href="?period=7" class="period-tab <?php echo $period === '7' ? 'active' : ''; ?>">7일</a>
        <a href="?period=30" class="period-tab <?php echo $period === '30' ? 'active' : ''; ?>">30일</a>
        <a href="?period=90" class="period-tab <?php echo $period === '90' ? 'active' : ''; ?>">90일</a>
        <a href="?period=365" class="period-tab <?php echo $period === '365' ? 'active' : ''; ?>">1년</a>
    </div>
    
    <!-- 차트 (간단한 막대 그래프) -->
    <div class="chart-container">
        <h3 style="margin-top: 0; margin-bottom: 1rem;">일별 방문자 추이</h3>
        <?php if (empty($stats)): ?>
            <p style="text-align: center; color: var(--text-light); padding: 2rem;">통계 데이터가 없습니다.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <div style="min-width: 600px;">
                    <?php 
                    $max_value = max(array_column($stats, 'visit_count'));
                    $reversed_stats = array_reverse($stats);
                    foreach ($reversed_stats as $stat): 
                        $percentage = $max_value > 0 ? ($stat['visit_count'] / $max_value) * 100 : 0;
                    ?>
                        <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <div style="width: 100px; font-size: 0.85rem; color: var(--text-light);">
                                <?php echo date('m/d', strtotime($stat['visit_date'])); ?>
                            </div>
                            <div style="flex: 1; background: var(--bg-color); border-radius: 4px; height: 30px; position: relative; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); height: 100%; width: <?php echo $percentage; ?>%; border-radius: 4px; display: flex; align-items: center; padding: 0 0.5rem;">
                                    <span style="color: white; font-size: 0.85rem; font-weight: 600;">
                                        <?php echo number_format($stat['visit_count']); ?>
                                    </span>
                                </div>
                            </div>
                            <div style="width: 80px; text-align: right; font-size: 0.85rem; color: var(--text-light); padding-left: 0.5rem;">
                                UV: <?php echo number_format($stat['unique_visitors']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- 상세 테이블 -->
    <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border-color);">
        <h3 style="margin-top: 0; margin-bottom: 1rem;">상세 통계</h3>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>날짜</th>
                        <th style="text-align: right;">총 방문</th>
                        <th style="text-align: right;">순 방문자</th>
                        <th style="text-align: right;">페이지뷰</th>
                        <th style="text-align: right;">방문당 PV</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $stat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($stat['visit_date']); ?></td>
                            <td style="text-align: right;"><?php echo number_format($stat['visit_count']); ?></td>
                            <td style="text-align: right;"><?php echo number_format($stat['unique_visitors']); ?></td>
                            <td style="text-align: right;"><?php echo number_format($stat['page_views']); ?></td>
                            <td style="text-align: right;">
                                <?php echo $stat['visit_count'] > 0 ? number_format($stat['page_views'] / $stat['visit_count'], 2) : '0'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
</div>
</body>
</html>
