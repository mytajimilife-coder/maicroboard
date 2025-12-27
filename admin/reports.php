<?php
define('IN_ADMIN', true);
$admin_title_key = 'report_management';
require_once 'common.php';

$db = getDB();

// 신고 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $report_id = (int)($_POST['report_id'] ?? 0);
    $action = $_POST['action'];
    
    if ($report_id > 0) {
        if ($action === 'resolve') {
            $admin_note = $_POST['admin_note'] ?? '';
            $stmt = $db->prepare("UPDATE mb1_reports SET status = 'resolved', reviewed_at = NOW(), reviewed_by = ?, admin_note = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user'], $admin_note, $report_id]);
            $success_msg = '신고가 처리되었습니다.';
        } elseif ($action === 'reject') {
            $admin_note = $_POST['admin_note'] ?? '';
            $stmt = $db->prepare("UPDATE mb1_reports SET status = 'rejected', reviewed_at = NOW(), reviewed_by = ?, admin_note = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user'], $admin_note, $report_id]);
            $success_msg = '신고가 기각되었습니다.';
        } elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM mb1_reports WHERE id = ?");
            $stmt->execute([$report_id]);
            $success_msg = '신고 기록이 삭제되었습니다.';
        }
    }
}

// 신고 목록 조회
$status_filter = $_GET['status'] ?? 'all';
$where = "1=1";
$params = [];

if ($status_filter !== 'all') {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}

$stmt = $db->prepare("SELECT r.*, m.mb_nickname as reporter_nickname 
                      FROM mb1_reports r 
                      LEFT JOIN mb1_member m ON r.reporter_id = m.mb_id 
                      WHERE {$where} 
                      ORDER BY r.created_at DESC 
                      LIMIT 100");
$stmt->execute($params);
$reports = $stmt->fetchAll();

// 통계
$stats = [
    'total' => 0,
    'pending' => 0,
    'resolved' => 0,
    'rejected' => 0
];

$stmt = $db->query("SELECT status, COUNT(*) as count FROM mb1_reports GROUP BY status");
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
    $stats['total'] += $row['count'];
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
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.filter-tab {
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    text-decoration: none;
    background: var(--bg-secondary);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    transition: all 0.2s;
}
.filter-tab.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}
.report-item {
    background: var(--bg-secondary);
    padding: 1.5rem;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    margin-bottom: 1rem;
}
.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}
.status-pending { background: #fef3c7; color: #92400e; }
.status-resolved { background: #dcfce7; color: #15803d; }
.status-rejected { background: #fee2e2; color: #b91c1c; }
.status-reviewed { background: #dbeafe; color: #1e40af; }
</style>

<div class="admin-card">
    <h2 style="margin-top: 0; margin-bottom: 1.5rem; color: var(--secondary-color);">🚨 신고 관리</h2>
    
    <?php if (isset($success_msg)): ?>
        <div style="background: #dcfce7; color: #15803d; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border-left: 4px solid #16a34a;">
            ✅ <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>
    
    <!-- 통계 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">전체 신고</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #f59e0b;"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">대기 중</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #10b981;"><?php echo $stats['resolved']; ?></div>
            <div class="stat-label">처리 완료</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #ef4444;"><?php echo $stats['rejected']; ?></div>
            <div class="stat-label">기각됨</div>
        </div>
    </div>
    
    <!-- 필터 -->
    <div class="filter-tabs">
        <a href="?status=all" class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">전체</a>
        <a href="?status=pending" class="filter-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">대기 중</a>
        <a href="?status=reviewed" class="filter-tab <?php echo $status_filter === 'reviewed' ? 'active' : ''; ?>">검토됨</a>
        <a href="?status=resolved" class="filter-tab <?php echo $status_filter === 'resolved' ? 'active' : ''; ?>">처리 완료</a>
        <a href="?status=rejected" class="filter-tab <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">기각됨</a>
    </div>
    
    <!-- 신고 목록 -->
    <?php if (empty($reports)): ?>
        <p style="text-align: center; color: var(--text-light); padding: 2rem;">신고 내역이 없습니다.</p>
    <?php else: ?>
        <?php foreach ($reports as $report): ?>
            <div class="report-item">
                <div class="report-header">
                    <div>
                        <span class="status-badge status-<?php echo $report['status']; ?>">
                            <?php 
                            $status_labels = [
                                'pending' => '대기 중',
                                'reviewed' => '검토됨',
                                'resolved' => '처리 완료',
                                'rejected' => '기각됨'
                            ];
                            echo $status_labels[$report['status']] ?? $report['status'];
                            ?>
                        </span>
                        <span style="margin-left: 1rem; color: var(--text-light); font-size: 0.9rem;">
                            <?php echo htmlspecialchars($report['created_at']); ?>
                        </span>
                    </div>
                    <div>
                        <strong><?php echo $report['report_type'] === 'post' ? '게시글' : '댓글'; ?></strong>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <div style="margin-bottom: 0.5rem;">
                        <strong>게시판:</strong> <?php echo htmlspecialchars($report['bo_table']); ?> |
                        <strong>대상 ID:</strong> <?php echo $report['target_id']; ?>
                    </div>
                    <div style="margin-bottom: 0.5rem;">
                        <strong>신고자:</strong> <?php echo htmlspecialchars($report['reporter_nickname'] ?? $report['reporter_id']); ?>
                    </div>
                    <div style="margin-bottom: 0.5rem;">
                        <strong>신고 사유:</strong> 
                        <span style="color: var(--danger-color); font-weight: 600;">
                            <?php echo htmlspecialchars($report['reason']); ?>
                        </span>
                    </div>
                    <?php if (!empty($report['description'])): ?>
                        <div style="background: var(--bg-color); padding: 0.75rem; border-radius: 4px; margin-top: 0.5rem;">
                            <?php echo nl2br(htmlspecialchars($report['description'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($report['status'] !== 'pending'): ?>
                    <div style="background: var(--bg-color); padding: 0.75rem; border-radius: 4px; margin-top: 1rem;">
                        <div style="margin-bottom: 0.5rem;">
                            <strong>처리자:</strong> <?php echo htmlspecialchars($report['reviewed_by'] ?? 'N/A'); ?> |
                            <strong>처리일:</strong> <?php echo htmlspecialchars($report['reviewed_at'] ?? 'N/A'); ?>
                        </div>
                        <?php if (!empty($report['admin_note'])): ?>
                            <div><strong>관리자 메모:</strong> <?php echo nl2br(htmlspecialchars($report['admin_note'])); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($report['status'] === 'pending'): ?>
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button onclick="showResolveForm(<?php echo $report['id']; ?>)" 
                                style="padding: 0.5rem 1rem; background: #10b981; color: white; border: none; border-radius: var(--radius); cursor: pointer;">
                            ✅ 처리 완료
                        </button>
                        <button onclick="showRejectForm(<?php echo $report['id']; ?>)" 
                                style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: var(--radius); cursor: pointer;">
                            ❌ 기각
                        </button>
                        <a href="../<?php echo $report['report_type'] === 'post' ? 'view' : 'list'; ?>.php?bo_table=<?php echo $report['bo_table']; ?>&wr_id=<?php echo $report['target_id']; ?>" 
                           target="_blank"
                           style="padding: 0.5rem 1rem; background: var(--bg-color); color: var(--text-color); border: 1px solid var(--border-color); border-radius: var(--radius); text-decoration: none; display: inline-block;">
                            🔍 확인
                        </a>
                    </div>
                    
                    <div id="form-<?php echo $report['id']; ?>" style="display: none; margin-top: 1rem; padding: 1rem; background: var(--bg-color); border-radius: var(--radius);">
                        <form method="post">
                            <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                            <input type="hidden" name="action" id="action-<?php echo $report['id']; ?>" value="">
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">관리자 메모:</label>
                                <textarea name="admin_note" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;"></textarea>
                            </div>
                            <button type="submit" style="padding: 0.5rem 1rem; background: var(--primary-color); color: white; border: none; border-radius: var(--radius); cursor: pointer;">
                                확인
                            </button>
                            <button type="button" onclick="hideForm(<?php echo $report['id']; ?>)" style="padding: 0.5rem 1rem; background: var(--text-light); color: white; border: none; border-radius: var(--radius); cursor: pointer;">
                                취소
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" onclick="return confirm('이 신고 기록을 삭제하시겠습니까?')" 
                                style="padding: 0.5rem 1rem; background: #6b7280; color: white; border: none; border-radius: var(--radius); cursor: pointer; margin-top: 1rem;">
                            🗑️ 삭제
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function showResolveForm(id) {
    document.getElementById('action-' + id).value = 'resolve';
    document.getElementById('form-' + id).style.display = 'block';
}

function showRejectForm(id) {
    document.getElementById('action-' + id).value = 'reject';
    document.getElementById('form-' + id).style.display = 'block';
}

function hideForm(id) {
    document.getElementById('form-' + id).style.display = 'none';
}
</script>

</main>
</div>
</body>
</html>
