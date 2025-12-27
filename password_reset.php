<?php
require_once 'config.php';

$message = '';
$error = '';

// 비밀번호 재설정 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_reset') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = $lang['email_required'] ?? '이메일을 입력해주세요.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT mb_id, mb_email FROM mb1_member WHERE mb_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // 재설정 토큰 생성
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // 토큰 저장 (mb1_password_reset 테이블)
            try {
                // 테이블이 없으면 생성
                $db->exec("CREATE TABLE IF NOT EXISTS `mb1_password_reset` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `mb_id` varchar(50) NOT NULL,
                    `token` varchar(255) NOT NULL,
                    `expires_at` datetime NOT NULL,
                    `used` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY `token` (`token`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                
                $stmt = $db->prepare("INSERT INTO mb1_password_reset (mb_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['mb_id'], $token, $expires]);
                
                // 이메일 발송 (실제 환경에서는 메일 서버 설정 필요)
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/password_reset_confirm.php?token=" . $token;
                
                // 간단한 메일 발송 (실제로는 PHPMailer 등 사용 권장)
                $subject = ($lang['password_reset_subject'] ?? '비밀번호 재설정') . " - MicroBoard";
                $body = ($lang['password_reset_email_body'] ?? '다음 링크를 클릭하여 비밀번호를 재설정하세요:') . "\n\n" . $reset_link . "\n\n" . 
                        ($lang['password_reset_expire_notice'] ?? '이 링크는 1시간 후 만료됩니다.');
                
                // 헤더 설정
                $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                if (@mail($email, $subject, $body, $headers)) {
                    $message = $lang['password_reset_sent'] ?? '비밀번호 재설정 링크가 이메일로 전송되었습니다.';
                } else {
                    // 메일 발송 실패 시에도 토큰 링크 표시 (개발 환경용)
                    $message = ($lang['password_reset_link'] ?? '비밀번호 재설정 링크:') . ' <a href="' . $reset_link . '">' . $reset_link . '</a>';
                }
            } catch (Exception $e) {
                $error = $lang['error_occurred'] ?? '오류가 발생했습니다.';
            }
        } else {
            // 보안을 위해 이메일이 없어도 같은 메시지 표시
            $message = $lang['password_reset_sent'] ?? '비밀번호 재설정 링크가 이메일로 전송되었습니다.';
        }
    }
}

require_once 'inc/header.php';
?>

<div style="max-width: 500px; margin: 4rem auto; padding: 2rem; background: var(--bg-secondary); border-radius: var(--radius); box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h2 style="margin-top: 0; margin-bottom: 1.5rem; color: var(--secondary-color); text-align: center;">
        🔑 <?php echo $lang['password_reset'] ?? '비밀번호 재설정'; ?>
    </h2>
    
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border-left: 4px solid #ef4444;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($message): ?>
        <div style="background: #dcfce7; color: #15803d; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border-left: 4px solid #16a34a;">
            ✅ <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="post" style="display: flex; flex-direction: column; gap: 1.5rem;">
        <input type="hidden" name="action" value="request_reset">
        
        <div>
            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">
                <?php echo $lang['email'] ?? '이메일'; ?>
            </label>
            <input type="email" name="email" id="email" required
                   placeholder="<?php echo $lang['enter_email'] ?? '이메일을 입력하세요'; ?>"
                   style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color); color: var(--text-color);">
        </div>
        
        <button type="submit" style="padding: 0.75rem 1.5rem; background: var(--primary-color); color: white; border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
            <?php echo $lang['send_reset_link'] ?? '재설정 링크 보내기'; ?>
        </button>
        
        <div style="text-align: center; margin-top: 1rem;">
            <a href="login.php" style="color: var(--primary-color); text-decoration: none;">
                ← <?php echo $lang['back_to_login'] ?? '로그인으로 돌아가기'; ?>
            </a>
        </div>
    </form>
</div>

<?php require_once 'inc/footer.php'; ?>
