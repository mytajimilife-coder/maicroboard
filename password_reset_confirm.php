<?php
require_once 'config.php';

$message = '';
$error = '';
$token = $_GET['token'] ?? '';
$valid_token = false;

$db = getDB();

// 토큰 검증
if (!empty($token)) {
    try {
        $stmt = $db->prepare("SELECT pr.*, m.mb_id FROM mb1_password_reset pr 
                              JOIN mb1_member m ON pr.mb_id = m.mb_id 
                              WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()");
        $stmt->execute([$token]);
        $reset_data = $stmt->fetch();
        
        if ($reset_data) {
            $valid_token = true;
        } else {
            $error = $lang['invalid_or_expired_token'] ?? '유효하지 않거나 만료된 토큰입니다.';
        }
    } catch (Exception $e) {
        $error = $lang['error_occurred'] ?? '오류가 발생했습니다.';
    }
}

// 비밀번호 재설정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = $lang['password_required'] ?? '비밀번호를 입력해주세요.';
    } elseif ($new_password !== $confirm_password) {
        $error = $lang['password_mismatch'] ?? '비밀번호가 일치하지 않습니다.';
    } elseif (strlen($new_password) < 6) {
        $error = $lang['password_too_short'] ?? '비밀번호는 최소 6자 이상이어야 합니다.';
    } else {
        try {
            // 토큰 재검증
            $stmt = $db->prepare("SELECT pr.*, m.mb_id FROM mb1_password_reset pr 
                                  JOIN mb1_member m ON pr.mb_id = m.mb_id 
                                  WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()");
            $stmt->execute([$token]);
            $reset_data = $stmt->fetch();
            
            if ($reset_data) {
                // 비밀번호 업데이트
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE mb1_member SET mb_password = ? WHERE mb_id = ?");
                $stmt->execute([$hashed_password, $reset_data['mb_id']]);
                
                // 토큰 사용 처리
                $stmt = $db->prepare("UPDATE mb1_password_reset SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);
                
                $message = $lang['password_reset_success'] ?? '비밀번호가 성공적으로 재설정되었습니다.';
                $valid_token = false;
            } else {
                $error = $lang['invalid_or_expired_token'] ?? '유효하지 않거나 만료된 토큰입니다.';
            }
        } catch (Exception $e) {
            $error = $lang['error_occurred'] ?? '오류가 발생했습니다.';
        }
    }
}

require_once 'inc/header.php';
?>

<div style="max-width: 500px; margin: 4rem auto; padding: 2rem; background: var(--bg-secondary); border-radius: var(--radius); box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h2 style="margin-top: 0; margin-bottom: 1.5rem; color: var(--secondary-color); text-align: center;">
        🔐 <?php echo $lang['reset_password'] ?? '새 비밀번호 설정'; ?>
    </h2>
    
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border-left: 4px solid #ef4444;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($message): ?>
        <div style="background: #dcfce7; color: #15803d; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border-left: 4px solid #16a34a;">
            ✅ <?php echo $message; ?>
            <div style="margin-top: 1rem; text-align: center;">
                <a href="login.php" style="color: #15803d; font-weight: 600; text-decoration: underline;">
                    <?php echo $lang['go_to_login'] ?? '로그인하러 가기'; ?> →
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($valid_token && !$message): ?>
        <form method="post" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div>
                <label for="new_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">
                    <?php echo $lang['new_password'] ?? '새 비밀번호'; ?>
                </label>
                <input type="password" name="new_password" id="new_password" required minlength="6"
                       placeholder="<?php echo $lang['enter_new_password'] ?? '새 비밀번호를 입력하세요'; ?>"
                       style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color); color: var(--text-color);">
            </div>
            
            <div>
                <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">
                    <?php echo $lang['confirm_password'] ?? '비밀번호 확인'; ?>
                </label>
                <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                       placeholder="<?php echo $lang['confirm_new_password'] ?? '비밀번호를 다시 입력하세요'; ?>"
                       style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-size: 1rem; background: var(--bg-color); color: var(--text-color);">
            </div>
            
            <button type="submit" style="padding: 0.75rem 1.5rem; background: var(--primary-color); color: white; border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
                <?php echo $lang['reset_password_button'] ?? '비밀번호 재설정'; ?>
            </button>
        </form>
    <?php elseif (!$valid_token && !$message): ?>
        <div style="text-align: center; padding: 2rem;">
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                <?php echo $lang['request_new_reset_link'] ?? '새로운 재설정 링크를 요청해주세요.'; ?>
            </p>
            <a href="password_reset.php" style="padding: 0.75rem 1.5rem; background: var(--primary-color); color: white; border-radius: var(--radius); text-decoration: none; font-weight: 600; display: inline-block;">
                <?php echo $lang['request_reset_link'] ?? '재설정 링크 요청'; ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'inc/footer.php'; ?>
