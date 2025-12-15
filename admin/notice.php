<?php
define('IN_ADMIN', true);
require_once 'common.php';

// 공지사항 기능 활성화 여부 확인
$notice_enabled = false;
$db = getDB();
try {
    $stmt = $db->query("SHOW TABLES LIKE 'mb1_notice'");
    if ($stmt->rowCount() > 0) {
        $notice_enabled = true;
    }
} catch (Exception $e) {
    $notice_enabled = false;
}

// 공지사항 테이블 생성
if (!$notice_enabled) {
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `mb1_notice` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `content` longtext NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `expires_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $notice_enabled = true;
    } catch (Exception $e) {
        $notice_enabled = false;
    }
}

// 공지사항 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_notice') {
            // 공지사항 생성
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $expires_at = $_POST['expires_at'] ?? '';

            if (!empty($title) && !empty($content)) {
                try {
                    if (!empty($expires_at)) {
                        $expires_at = date('Y-m-d H:i:s', strtotime($expires_at));
                        $stmt = $db->prepare("INSERT INTO mb1_notice (title, content, expires_at) VALUES (?, ?, ?)");
                        $stmt->execute([$title, $content, $expires_at]);
                    } else {
                        $stmt = $db->prepare("INSERT INTO mb1_notice (title, content) VALUES (?, ?)");
                        $stmt->execute([$title, $content]);
                    }
                    $success = '공지사항이 성공적으로 생성되었습니다.';
                } catch (Exception $e) {
                    $error = '공지사항 생성 중 오류가 발생했습니다: ' . $e->getMessage();
                }
            } else {
                $error = '제목과 내용을 입력해주세요.';
            }
        } elseif ($_POST['action'] === 'update_notice') {
            // 공지사항 업데이트
            $notice_id = $_POST['notice_id'] ?? '';
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $expires_at = $_POST['expires_at'] ?? '';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($notice_id) && !empty($title) && !empty($content)) {
                try {
                    if (!empty($expires_at)) {
                        $expires_at = date('Y-m-d H:i:s', strtotime($expires_at));
                        $stmt = $db->prepare("UPDATE mb1_notice SET title = ?, content = ?, is_active = ?, expires_at = ? WHERE id = ?");
                        $stmt->execute([$title, $content, $is_active, $expires_at, $notice_id]);
                    } else {
                        $stmt = $db->prepare("UPDATE mb1_notice SET title = ?, content = ?, is_active = ?, expires_at = NULL WHERE id = ?");
                        $stmt->execute([$title, $content, $is_active, $notice_id]);
                    }
                    $success = '공지사항이 성공적으로 업데이트되었습니다.';
                } catch (Exception $e) {
                    $error = '공지사항 업데이트 중 오류가 발생했습니다: ' . $e->getMessage();
                }
            } else {
                $error = '제목과 내용을 입력해주세요.';
            }
        } elseif ($_POST['action'] === 'delete_notice') {
            // 공지사항 삭제
            $notice_id = $_POST['notice_id'] ?? '';

            if (!empty($notice_id)) {
                try {
                    $stmt = $db->prepare("DELETE FROM mb1_notice WHERE id = ?");
                    $stmt->execute([$notice_id]);
                    $success = '공지사항이 성공적으로 삭제되었습니다.';
                } catch (Exception $e) {
                    $error = '공지사항 삭제 중 오류가 발생했습니다: ' . $e->getMessage();
                }
            } else {
                $error = '공지사항을 선택해주세요.';
            }
        }
    }
}

// 공지사항 목록 가져오기
$notices = [];
if ($notice_enabled) {
    try {
        $stmt = $db->query("SELECT * FROM mb1_notice ORDER BY created_at DESC");
        $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $notices = [];
    }
}

// 공지사항 가져오기 (편집용)
$notice = null;
if (isset($_GET['edit']) && $notice_enabled) {
    $notice_id = $_GET['edit'];
    try {
        $stmt = $db->prepare("SELECT * FROM mb1_notice WHERE id = ?");
        $stmt->execute([$notice_id]);
        $notice = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $notice = null;
    }
}
?>

<div class="admin-card">
    <h2 style="margin-top: 0; margin-bottom: 1rem; color: var(--secondary-color);">📢 Notice Management</h2>
    <p style="font-size: 1.1rem; color: var(--text-color); margin-bottom: 2rem;">
        Manage notices to display important information to your users.
    </p>

    <?php if (isset($error)): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border-left: 4px solid #ef4444;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div style="background: #dcfce7; color: #15803d; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border-left: 4px solid #16a34a;">
            ✅ <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($notice)): ?>
        <!-- 공지사항 편집 폼 -->
        <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); border: 1px solid var(--border-color);">
            <h3 style="margin-top: 0; margin-bottom: 1rem; color: var(--secondary-color); font-size: 1.1rem;">Edit Notice</h3>

            <form method="post" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" name="action" value="update_notice">
                <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">

                <div>
                    <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Title</label>
                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($notice['title']); ?>" required
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);">
                </div>

                <div>
                    <label for="content" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Content</label>
                    <textarea name="content" id="content" rows="10" required
                              style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);"><?php echo htmlspecialchars($notice['content']); ?></textarea>
                </div>

                <div>
                    <label for="expires_at" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Expires At (Optional)</label>
                    <input type="datetime-local" name="expires_at" id="expires_at"
                           value="<?php echo !empty($notice['expires_at']) ? date('Y-m-d\TH:i', strtotime($notice['expires_at'])) : ''; ?>"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);">
                </div>

                <div>
                    <input type="checkbox" name="is_active" id="is_active" <?php echo $notice['is_active'] ? 'checked' : ''; ?>>
                    <label for="is_active" style="font-weight: 600; color: var(--text-color); margin-left: 0.5rem;">Active</label>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" style="padding: 0.75rem 1.5rem; background: var(--primary-color); color: white; border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                        Update Notice
                    </button>
                    <a href="notice.php" style="padding: 0.75rem 1.5rem; background: var(--text-light); color: var(--text-color); border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; text-decoration: none;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- 공지사항 생성 폼 -->
        <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); border: 1px solid var(--border-color);">
            <h3 style="margin-top: 0; margin-bottom: 1rem; color: var(--secondary-color); font-size: 1.1rem;">Create New Notice</h3>

            <form method="post" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" name="action" value="create_notice">

                <div>
                    <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Title</label>
                    <input type="text" name="title" id="title" placeholder="Notice title" required
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);">
                </div>

                <div>
                    <label for="content" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Content</label>
                    <textarea name="content" id="content" rows="10" placeholder="Notice content" required
                              style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);"></textarea>
                </div>

                <div>
                    <label for="expires_at" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Expires At (Optional)</label>
                    <input type="datetime-local" name="expires_at" id="expires_at"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);">
                </div>

                <button type="submit" style="padding: 0.75rem 1.5rem; background: var(--primary-color); color: white; border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    Create Notice
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- 공지사항 목록 -->
    <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); border: 1px solid var(--border-color);">
        <h3 style="margin-top: 0; margin-bottom: 1rem; color: var(--secondary-color); font-size: 1.1rem;">Notice List</h3>

        <?php if (empty($notices)): ?>
            <p style="color: var(--text-light);">No notices found.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--bg-color);">
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Title</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Status</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Created At</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Expires At</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notices as $notice): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($notice['title']); ?></td>
                            <td style="padding: 0.75rem;">
                                <span style="color: <?php echo $notice['is_active'] ? '#10b981' : '#6b7280'; ?>; font-weight: 600;">
                                    <?php echo $notice['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($notice['created_at']); ?></td>
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($notice['expires_at'] ?? 'Never'); ?></td>
                            <td style="padding: 0.75rem;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="?edit=<?php echo $notice['id']; ?>" style="padding: 0.5rem 1rem; background: var(--primary-color); color: white; border: none; border-radius: var(--radius); font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: background 0.2s; text-decoration: none;">
                                        Edit
                                    </a>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_notice">
                                        <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                                        <button type="submit" style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: var(--radius); font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</main> <!-- admin-main end -->
</div> <!-- admin-layout end -->
</body>
</html>
