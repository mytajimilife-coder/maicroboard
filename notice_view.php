<?php
require_once 'inc/header.php';

// 공지사항 가져오기
$db = getDB();
$notices = [];
try {
    $stmt = $db->query("SELECT * FROM mb1_notice WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC");
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $notices = [];
}

// 공지사항 ID 가져오기
$notice_id = $_GET['id'] ?? '';
$notice = null;

if (!empty($notice_id)) {
    try {
        $stmt = $db->prepare("SELECT * FROM mb1_notice WHERE id = ?");
        $stmt->execute([$notice_id]);
        $notice = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $notice = null;
    }
}
?>

<div class="main-content">
    <div class="container">
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 2rem;">Notice</h1>

        <?php if (empty($notices)): ?>
            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border-color); text-align: center;">
                <p style="color: var(--text-light);">No active notices found.</p>
            </div>
        <?php else: ?>
            <?php if ($notice): ?>
                <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); border: 1px solid var(--border-color);">
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 1rem;"><?php echo htmlspecialchars($notice['title']); ?></h2>
                    <div style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($notice['created_at']); ?>
                        <?php if (!empty($notice['expires_at'])): ?>
                            | Expires: <?php echo htmlspecialchars($notice['expires_at']); ?>
                        <?php endif; ?>
                    </div>
                    <div style="line-height: 1.6; color: var(--text-color);">
                        <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); border: 1px solid var(--border-color);">
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 1rem;">Notice List</h2>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($notices as $notice): ?>
                            <li style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <a href="?id=<?php echo $notice['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                                    <?php echo htmlspecialchars($notice['title']); ?>
                                </a>
                                <div style="color: var(--text-light); font-size: 0.8rem; margin-top: 0.5rem;">
                                    <?php echo htmlspecialchars($notice['created_at']); ?>
                                    <?php if (!empty($notice['expires_at'])): ?>
                                        | Expires: <?php echo htmlspecialchars($notice['expires_at']); ?>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>
