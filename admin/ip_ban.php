<?php
define('IN_ADMIN', true);
require_once 'common.php';

// IP 차단 기능 활성화 여부 확인
$ip_ban_enabled = false;
$db = getDB();
try {
    $stmt = $db->query("SHOW TABLES LIKE 'mb1_ip_ban'");
    if ($stmt->rowCount() > 0) {
        $ip_ban_enabled = true;
    }
} catch (Exception $e) {
    $ip_ban_enabled = false;
}

// IP 차단 테이블 생성
if (!$ip_ban_enabled) {
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `mb1_ip_ban` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ip_address` varchar(45) NOT NULL,
                `reason` text,
                `banned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expires_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `ip_address` (`ip_address`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $ip_ban_enabled = true;
    } catch (Exception $e) {
        $ip_ban_enabled = false;
    }
}

// IP 차단 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'ban_ip') {
            // IP 차단
            $ip_address = $_POST['ip_address'] ?? '';
            $reason = $_POST['reason'] ?? '';
            $expires_at = $_POST['expires_at'] ?? '';

            if (!empty($ip_address)) {
                try {
                    if (!empty($expires_at)) {
                        $expires_at = date('Y-m-d H:i:s', strtotime($expires_at));
                        $stmt = $db->prepare("INSERT INTO mb1_ip_ban (ip_address, reason, expires_at) VALUES (?, ?, ?)");
                        $stmt->execute([$ip_address, $reason, $expires_at]);
                    } else {
                        $stmt = $db->prepare("INSERT INTO mb1_ip_ban (ip_address, reason) VALUES (?, ?)");
                        $stmt->execute([$ip_address, $reason]);
                    }
                    $success = 'IP가 성공적으로 차단되었습니다.';
                } catch (Exception $e) {
                    $error = 'IP 차단 중 오류가 발생했습니다: ' . $e->getMessage();
                }
            } else {
                $error = 'IP 주소를 입력해주세요.';
            }
        } elseif ($_POST['action'] === 'unban_ip') {
            // IP 차단 해제
            $ip_id = $_POST['ip_id'] ?? '';

            if (!empty($ip_id)) {
                try {
                    $stmt = $db->prepare("DELETE FROM mb1_ip_ban WHERE id = ?");
                    $stmt->execute([$ip_id]);
                    $success = 'IP 차단이 성공적으로 해제되었습니다.';
                } catch (Exception $e) {
                    $error = 'IP 차단 해제 중 오류가 발생했습니다: ' . $e->getMessage();
                }
            } else {
                $error = 'IP를 선택해주세요.';
            }
        }
    }
}

// 차단된 IP 목록 가져오기
$banned_ips = [];
if ($ip_ban_enabled) {
    try {
        $stmt = $db->query("SELECT * FROM mb1_ip_ban ORDER BY banned_at DESC");
        $banned_ips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $banned_ips = [];
    }
}
?>

<div class="admin-card">
    <h2 style="margin-top: 0; margin-bottom: 1rem; color: var(--secondary-color);">🚫 IP Ban Management</h2>
    <p style="font-size: 1.1rem; color: var(--text-color); margin-bottom: 2rem;">
        Manage IP bans to block unwanted access to your site.
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

    <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); border: 1px solid var(--border-color);">
        <h3 style="margin-top: 0; margin-bottom: 1rem; color: var(--secondary-color); font-size: 1.1rem;">Ban IP Address</h3>

        <form method="post" style="display: flex; gap: 1rem; align-items: flex-end;">
            <input type="hidden" name="action" value="ban_ip">

            <div style="flex: 1;">
                <label for="ip_address" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">IP Address</label>
                <input type="text" name="ip_address" id="ip_address" placeholder="e.g., 192.168.1.1" required
                       style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);">
            </div>

            <div style="flex: 1;">
                <label for="reason" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Reason</label>
                <input type="text" name="reason" id="reason" placeholder="Reason for ban"
                       style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);">
            </div>

            <div style="flex: 1;">
                <label for="expires_at" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Expires At (Optional)</label>
                <input type="datetime-local" name="expires_at" id="expires_at"
                       style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color);">
            </div>

            <button type="submit" style="padding: 0.75rem 1.5rem; background: var(--primary-color); color: white; border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                Ban IP
            </button>
        </form>
    </div>

    <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); border: 1px solid var(--border-color);">
        <h3 style="margin-top: 0; margin-bottom: 1rem; color: var(--secondary-color); font-size: 1.1rem;">Banned IP List</h3>

        <?php if (empty($banned_ips)): ?>
            <p style="color: var(--text-light);">No banned IPs found.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--bg-color);">
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">IP Address</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Reason</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Banned At</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Expires At</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banned_ips as $ip): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($ip['ip_address']); ?></td>
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($ip['reason'] ?? 'N/A'); ?></td>
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($ip['banned_at']); ?></td>
                            <td style="padding: 0.75rem;"><?php echo htmlspecialchars($ip['expires_at'] ?? 'Never'); ?></td>
                            <td style="padding: 0.75rem;">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="unban_ip">
                                    <input type="hidden" name="ip_id" value="<?php echo $ip['id']; ?>">
                                    <button type="submit" style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: var(--radius); font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                                        Unban
                                    </button>
                                </form>
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
