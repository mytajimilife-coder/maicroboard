<?php
define('IN_ADMIN', true);
require_once 'common.php';

// 관리자 권한 확인
if (!isAdmin()) {
    die('<p>' . $lang['admin_only'] . '</p>');
}

$db = getDB();
$success = '';
$error = '';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF 토큰 검증
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = $lang['csrf_token_invalid'];
    } else {
        $provider = $_POST['provider'] ?? '';
        $client_id = trim($_POST['client_id'] ?? '');
        $client_secret = trim($_POST['client_secret'] ?? '');
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        if (in_array($provider, ['google', 'line', 'apple'])) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO mb1_oauth_config (provider, client_id, client_secret, enabled) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    client_id = VALUES(client_id),
                    client_secret = VALUES(client_secret),
                    enabled = VALUES(enabled)
                ");
                $stmt->execute([$provider, $client_id, $client_secret, $enabled]);
                $success = $lang['settings_saved'];
            } catch (Exception $e) {
                $error = $lang['error_occurred'] . ': ' . $e->getMessage();
            }
        }
    }
}

// 현재 설정 가져오기
$oauth_configs = [];
$stmt = $db->query("SELECT * FROM mb1_oauth_config");
while ($row = $stmt->fetch()) {
    $oauth_configs[$row['provider']] = $row;
}
?>

<h1><?php echo $lang['oauth_settings']; ?></h1>

<?php if ($error): ?>
<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
    <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<div style="background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
    <h3 style="margin-top: 0;"><?php echo $lang['oauth_setup_guide']; ?></h3>
    <ul style="margin: 10px 0;">
        <li><strong>Google:</strong> <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> - OAuth 2.0 클라이언트 ID 생성</li>
        <li><strong>LINE:</strong> <a href="https://developers.line.biz/console/" target="_blank">LINE Developers Console</a> - 채널 생성 및 설정</li>
        <li><strong>Apple:</strong> <a href="https://developer.apple.com/account/" target="_blank">Apple Developer</a> - Sign in with Apple 설정</li>
    </ul>
    <p style="margin-bottom: 0;"><strong><?php echo $lang['oauth_callback_url']; ?>:</strong> <code><?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '/oauth_callback.php'; ?></code></p>
</div>

<!-- Google OAuth 설정 -->
<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px; justify-content: space-between;">
        <span style="display: flex; align-items: center; gap: 10px;">
            <img src="https://www.google.com/favicon.ico" width="24" height="24" alt="Google">
            Google OAuth
        </span>
        <?php 
        $google_config = $oauth_configs['google'] ?? [];
        $is_configured = !empty($google_config['client_id']) && !empty($google_config['client_secret']) && $google_config['enabled'];
        ?>
        <span style="padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; <?php echo $is_configured ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
            <?php echo $is_configured ? '✓ ' . $lang['oauth_configured'] : '⚠ ' . $lang['oauth_not_configured']; ?>
        </span>
    </h2>
    <?php if (!empty($google_config['client_id']) && !empty($google_config['client_secret']) && !$google_config['enabled']): ?>
    <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
        <small><?php echo $lang['oauth_configured_but_disabled']; ?></small>
    </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="provider" value="google">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php echo $lang['oauth_client_id']; ?>:
            </label>
            <input type="text" name="client_id" value="<?php echo htmlspecialchars($oauth_configs['google']['client_id'] ?? ''); ?>" 
                   style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php echo $lang['oauth_client_secret']; ?>:
            </label>
            <input type="text" name="client_secret" value="<?php echo htmlspecialchars($oauth_configs['google']['client_secret'] ?? ''); ?>" 
                   style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>
                <input type="checkbox" name="enabled" <?php echo ($oauth_configs['google']['enabled'] ?? 0) ? 'checked' : ''; ?>>
                <?php echo $lang['oauth_enabled']; ?>
            </label>
        </div>
        
        <button type="submit" class="btn"><?php echo $lang['save']; ?></button>
    </form>
</div>

<!-- LINE OAuth 설정 -->
<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px; justify-content: space-between;">
        <span style="display: flex; align-items: center; gap: 10px;">
            <span style="color: #00B900; font-weight: bold;">LINE</span>
        </span>
        <?php 
        $line_config = $oauth_configs['line'] ?? [];
        $is_configured = !empty($line_config['client_id']) && !empty($line_config['client_secret']) && $line_config['enabled'];
        ?>
        <span style="padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; <?php echo $is_configured ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
            <?php echo $is_configured ? '✓ ' . $lang['oauth_configured'] : '⚠ ' . $lang['oauth_not_configured']; ?>
        </span>
    </h2>
    <?php if (!empty($line_config['client_id']) && !empty($line_config['client_secret']) && !$line_config['enabled']): ?>
    <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
        <small><?php echo $lang['oauth_configured_but_disabled']; ?></small>
    </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="provider" value="line">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php echo $lang['oauth_client_id']; ?> (Channel ID):
            </label>
            <input type="text" name="client_id" value="<?php echo htmlspecialchars($oauth_configs['line']['client_id'] ?? ''); ?>" 
                   style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php echo $lang['oauth_client_secret']; ?> (Channel Secret):
            </label>
            <input type="text" name="client_secret" value="<?php echo htmlspecialchars($oauth_configs['line']['client_secret'] ?? ''); ?>" 
                   style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>
                <input type="checkbox" name="enabled" <?php echo ($oauth_configs['line']['enabled'] ?? 0) ? 'checked' : ''; ?>>
                <?php echo $lang['oauth_enabled']; ?>
            </label>
        </div>
        
        <button type="submit" class="btn"><?php echo $lang['save']; ?></button>
    </form>
</div>

<!-- Apple OAuth 설정 -->
<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px; justify-content: space-between;">
        <span style="display: flex; align-items: center; gap: 10px;">
            <img src="https://www.apple.com/favicon.ico" width="24" height="24" alt="Apple">
            Apple OAuth
        </span>
        <?php 
        $apple_config = $oauth_configs['apple'] ?? [];
        $is_configured = !empty($apple_config['client_id']) && !empty($apple_config['client_secret']) && $apple_config['enabled'];
        ?>
        <span style="padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; <?php echo $is_configured ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
            <?php echo $is_configured ? '✓ ' . $lang['oauth_configured'] : '⚠ ' . $lang['oauth_not_configured']; ?>
        </span>
    </h2>
    <?php if (!empty($apple_config['client_id']) && !empty($apple_config['client_secret']) && !$apple_config['enabled']): ?>
    <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
        <small><?php echo $lang['oauth_configured_but_disabled']; ?></small>
    </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="provider" value="apple">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php echo $lang['oauth_client_id']; ?> (Service ID):
            </label>
            <input type="text" name="client_id" value="<?php echo htmlspecialchars($oauth_configs['apple']['client_id'] ?? ''); ?>" 
                   style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php echo $lang['oauth_client_secret']; ?> (Team ID):
            </label>
            <input type="text" name="client_secret" value="<?php echo htmlspecialchars($oauth_configs['apple']['client_secret'] ?? ''); ?>" 
                   style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <small style="color: #666;">Apple의 경우 추가 설정이 필요합니다 (Key ID, Private Key 등)</small>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>
                <input type="checkbox" name="enabled" <?php echo ($oauth_configs['apple']['enabled'] ?? 0) ? 'checked' : ''; ?>>
                <?php echo $lang['oauth_enabled']; ?>
            </label>
        </div>
        
        <button type="submit" class="btn"><?php echo $lang['save']; ?></button>
    </form>
</div>

</body>
</html>
