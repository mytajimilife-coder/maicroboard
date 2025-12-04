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
?>
<!DOCTYPE html>
<html>
<head>
  <title>MicroBoard - <?php echo $lang['register']; ?></title>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="skin/default/style.css">
  <link rel="icon" type="image/png" href="img/favicon.png">
  <style>
    .register-page {
      max-width: 400px;
      margin: 100px auto;
      padding: 30px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    .form-group input:focus {
      outline: none;
      border-color: #007bff;
    }
    .btn {
      background: #007bff;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
    }
    .btn:hover {
      background: #0056b3;
    }
    .error {
      color: #dc3545;
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .success {
      color: #155724;
      background: #d4edda;
      border: 1px solid #c3e6cb;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .login-link {
      text-align: center;
      margin-top: 20px;
    }
    .login-link a {
      color: #007bff;
      text-decoration: none;
    }
  </style>
</head>
<body class="register-page">
  <div style="position: absolute; top: 20px; right: 20px;">
    <?php 
    $lang_code = $_SESSION['lang'] ?? 'ko';
    $langs = ['ko' => '🇰🇷', 'en' => '🇺🇸', 'ja' => '🇯🇵', 'zh' => '🇨🇳'];
    foreach ($langs as $code => $flag) {
        $params = $_GET;
        $params['lang'] = $code;
        $url = '?' . http_build_query($params);
        $opacity = ($lang_code === $code) ? '1' : '0.4';
        echo "<a href=\"{$url}\" style=\"text-decoration: none; opacity: {$opacity}; margin-left: 10px; font-size: 1.5em; filter: grayscale(" . ($lang_code === $code ? '0' : '1') . ");\">{$flag}</a>";
    }
    ?>
  </div>
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
      <small><?php echo $lang['username_help']; ?></small>
    </div>
    
    <div class="form-group">
      <label for="password"><?php echo $lang['password']; ?></label>
      <input type="password" name="password" id="password" placeholder="<?php echo $lang['password']; ?>" maxlength="255" required>
      <small><?php echo $lang['password_help']; ?></small>
    </div>
    
    <div class="form-group">
      <label for="password_confirm"><?php echo $lang['password_confirm']; ?></label>
      <input type="password" name="password_confirm" id="password_confirm" placeholder="<?php echo $lang['password_confirm']; ?>" maxlength="255" required>
    </div>
    
    <button type="submit" class="btn"><?php echo $lang['register']; ?></button>
  </form>
  
  <?php
  // OAuth 소셜 로그인 버튼
  require_once 'inc/oauth.php';
  $enabled_providers = getEnabledOAuthProviders();
  if (!empty($enabled_providers)):
  ?>
  <div style="margin-top: 30px; padding: 20px; border-top: 1px solid #ddd;">
    <p style="text-align: center; color: #666; margin-bottom: 15px;"><?php echo $lang['oauth_register_with'] ?? '소셜 계정으로 가입'; ?></p>
    <div style="display: flex; flex-direction: column; gap: 10px;">
      <?php foreach ($enabled_providers as $provider): 
        $login_url = getOAuthLoginUrl($provider);
        if ($login_url):
      ?>
        <?php if ($provider === 'google'): ?>
          <a href="<?php echo htmlspecialchars($login_url); ?>" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; background: #fff; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; font-weight: 500;">
            <img src="https://www.google.com/favicon.ico" width="20" height="20" alt="Google">
            <span>Google<?php echo $lang['oauth_register_suffix'] ?? '로 가입'; ?></span>
          </a>
        <?php elseif ($provider === 'line'): ?>
          <a href="<?php echo htmlspecialchars($login_url); ?>" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; background: #00B900; border: 1px solid #00B900; border-radius: 4px; text-decoration: none; color: white; font-weight: 500;">
            <span style="font-weight: bold;">LINE</span>
            <span><?php echo $lang['oauth_register_suffix'] ?? '로 가입'; ?></span>
          </a>
        <?php elseif ($provider === 'apple'): ?>
          <a href="<?php echo htmlspecialchars($login_url); ?>" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; background: #000; border: 1px solid #000; border-radius: 4px; text-decoration: none; color: white; font-weight: 500;">
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
  
  <div class="login-link">
    <p><?php echo $lang['already_member']; ?> <a href="login.php"><?php echo $lang['login']; ?></a></p>
  </div>
</body>
</html>
