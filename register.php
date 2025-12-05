<?php
require_once 'config.php';

// 이미 로그인한 사용자는 리디렉션
if (isLoggedIn()) {
  header('Location: list.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF 토큰 검증
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $error = $lang['csrf_token_invalid'];
  } else {
    // 입력값 검증 및 이스케이프
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // 입력값 길이 및 형식 검증
    if (strlen($username) > 20 || strlen($username) < 3) {
      $error = $lang['invalid_username'];
    } elseif (strlen($password) < 6) {
      $error = $lang['invalid_password'];
    } elseif ($password !== $password_confirm) {
      $error = $lang['password_mismatch'];
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
      $error = $lang['invalid_format'];
    } else {
      // 중복 체크
      if (isUsernameExists($username)) {
        $error = $lang['username_exists'];
      } else {
        // 회원가입 처리
        if (registerUser($username, $password)) {
          $success = $lang['register_success'];
        } else {
          $error = $lang['register_failed'];
        }
      }
    }
  }
}

// CSRF 토큰 생성
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = $lang['register'];
require_once 'inc/header.php';
?>
<div class="content-wrapper">
<div class="login-container">
  <h2><?php echo $lang['register']; ?></h2>
  
  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  
  <?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    
    <div class="form-group">
      <label for="username"><?php echo $lang['username']; ?></label>
      <input type="text" name="username" id="username" placeholder="<?php echo $lang['username']; ?>" maxlength="20" required>
      <small style="color: var(--text-light); font-size: 0.875rem;"><?php echo $lang['username_help']; ?></small>
    </div>
    
    <div class="form-group">
      <label for="password"><?php echo $lang['password']; ?></label>
      <input type="password" name="password" id="password" placeholder="<?php echo $lang['password']; ?>" maxlength="255" required>
      <small style="color: var(--text-light); font-size: 0.875rem;"><?php echo $lang['password_help']; ?></small>
    </div>
    
    <div class="form-group">
      <label for="password_confirm"><?php echo $lang['password_confirm']; ?></label>
      <input type="password" name="password_confirm" id="password_confirm" placeholder="<?php echo $lang['password_confirm']; ?>" maxlength="255" required>
    </div>
    
    <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;"><?php echo $lang['register']; ?></button>
  </form>
  
  <?php
  // OAuth 소셜 로그인 버튼
  require_once 'inc/oauth.php';
  $enabled_providers = getEnabledOAuthProviders();
  if (!empty($enabled_providers)):
  ?>
  <div style="margin-top: 30px; padding: 20px; border-top: 1px solid var(--border-color);">
    <p style="text-align: center; color: var(--text-light); margin-bottom: 15px;"><?php echo $lang['oauth_register_with'] ?? '소셜 계정으로 가입'; ?></p>
    <div style="display: flex; flex-direction: column; gap: 10px;">
      <?php foreach ($enabled_providers as $provider): 
        $login_url = getOAuthLoginUrl($provider);
        if ($login_url):
      ?>
        <?php if ($provider === 'google'): ?>
          <a href="<?php echo htmlspecialchars($login_url); ?>" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--radius); text-decoration: none; color: var(--text-color); font-weight: 500;">
            <img src="https://www.google.com/favicon.ico" width="20" height="20" alt="Google">
            <span>Google<?php echo $lang['oauth_register_suffix'] ?? '로 가입'; ?></span>
          </a>
        <?php elseif ($provider === 'line'): ?>
          <a href="<?php echo htmlspecialchars($login_url); ?>" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; background: #00B900; border: 1px solid #00B900; border-radius: var(--radius); text-decoration: none; color: white; font-weight: 500;">
            <span style="font-weight: bold;">LINE</span>
            <span><?php echo $lang['oauth_register_suffix'] ?? '로 가입'; ?></span>
          </a>
        <?php elseif ($provider === 'apple'): ?>
          <a href="<?php echo htmlspecialchars($login_url); ?>" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; background: #000; border: 1px solid #000; border-radius: var(--radius); text-decoration: none; color: white; font-weight: 500;">
            <img src="https://www.apple.com/favicon.ico" width="20" height="20" alt="Apple">
            <span>Apple<?php echo $lang['oauth_register_suffix'] ?? '로 가입'; ?></span>
          </a>
        <?php endif; ?>
      <?php 
        endif;
      endforeach; 
      ?>
    </div>
  </div>
  <?php endif; ?>
  
  <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
    <p><?php echo $lang['already_member']; ?> <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;"><?php echo $lang['login']; ?></a></p>
  </div>
</div>
</div>
<?php require_once 'inc/footer.php'; ?>
