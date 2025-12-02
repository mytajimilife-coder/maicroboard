<?php
require_once 'config.php';

if (isLoggedIn()) {
  header('Location: list.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF 토큰 검증
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $error = $lang['csrf_token_invalid'];
  } else {
    // 입력값 검증 및 이스케이프
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // 입력값 길이 제한
    if (strlen($username) > 50 || strlen($password) > 255) {
      $error = $lang['input_too_long'];
    } elseif (empty($username) || empty($password)) {
      $error = $lang['login_input_required'];
    } else {
      // SQL 인젝션 방지를 위한 파라미터화된 쿼리 사용
      if (verifyUser($username, $password)) {
        $_SESSION['user'] = $username;
        $_SESSION['login_time'] = time();
        // CSRF 토큰 재생성
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: list.php');
        exit;
      } else {
        $error = $lang['login_failed'];
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
  <title>MicroBoard - <?php echo $lang['login']; ?></title>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="skin/default/style.css">
</head>
<body class="login-page">
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
  <h2><?php echo $lang['login']; ?></h2>
  <?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
  <?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <input type="text" name="username" placeholder="<?php echo $lang['username']; ?>" maxlength="50" required>
    <input type="password" name="password" placeholder="<?php echo $lang['password']; ?>" maxlength="255" required>
    <button type="submit"><?php echo $lang['login']; ?></button>
  </form>
  <p><?php echo $lang['test']; ?>: admin / admin</p>
  
  <div style="margin-top: 30px; padding: 15px; border-top: 1px solid #ddd; text-align: center;">
    <p><?php echo $lang['first_visit']; ?> <a href="register.php" style="color: #28a745; text-decoration: none; font-weight: bold;"><?php echo $lang['register']; ?></a></p>
  </div>
</body>
</html>
