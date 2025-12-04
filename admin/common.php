<?php
if (!defined('IN_ADMIN')) die();

require_once '../config.php';
requireAdmin();

// 설정 가져오기
$config = get_config();
$default_theme = $config['cf_theme'] ?? 'light';
$bg_type = $config['cf_bg_type'] ?? 'color';
$bg_value = $config['cf_bg_value'] ?? '#ffffff';

// 배경 스타일 생성
$custom_bg_style = '';
if ($bg_type === 'image') {
    // admin 폴더 기준이므로 ../를 붙여야 함
    $custom_bg_style = "url('../" . htmlspecialchars($bg_value) . "')";
} else {
    $custom_bg_style = $bg_value;
}

// 언어 처리
$lang_code = $_SESSION['lang'] ?? 'ko';
$lang_file = "../lang/{$lang_code}.php";
if (file_exists($lang_file)) {
    $lang = require $lang_file;
} else {
    $lang = require '../lang/ko.php';
}

$admin_title = $lang['admin_page_title'];
?><!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
<title><?php echo $admin_title; ?></title>
<meta charset="UTF-8">
<link rel="stylesheet" href="../skin/default/style.css">
<style>
/* 관리자 페이지 전용 스타일 */
.admin-page { max-width: 1200px; margin: 50px auto; padding: 20px; }
.admin-menu { margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }
.admin-menu .btn { margin-right: 10px; margin-bottom: 5px; }

/* 배경 설정 적용 */
:root {
    --body-bg: <?php echo $custom_bg_style; ?>;
}
<?php if ($bg_type === 'image'): ?>
body {
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}
<?php endif; ?>
</style>
<script>
// 테마 초기화 스크립트
(function() {
    const savedTheme = localStorage.getItem('theme');
    const defaultTheme = '<?php echo $default_theme; ?>';
    const theme = savedTheme || defaultTheme;
    
    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
})();
</script>
</head>
<body class="admin-page">
<div class="admin-menu">
  <a href="index.php" class="btn"><?php echo $lang['welcome']; ?></a>
  <a href="../user/mypage.php" class="btn"><?php echo $lang['mypage']; ?></a>
  <a href="users.php" class="btn"><?php echo $lang['user_management']; ?></a>
  <a href="board.php" class="btn"><?php echo $lang['board_management']; ?></a>
  <a href="config.php" class="btn"><?php echo $lang['config_management']; ?></a>
  <a href="oauth.php" class="btn"><?php echo $lang['oauth_settings']; ?></a>
  <a href="policy.php" class="btn"><?php echo $lang['policy_management']; ?></a>
  <a href="../logout.php" class="btn logout" style="background-color: #dc3545; border-color: #dc3545; color: white;"><?php echo $lang['logout']; ?></a>
</div>

<div style="margin-bottom: 20px; padding: 15px; background: var(--bg-secondary); border-radius: 5px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
  <div>
      <strong><?php echo $lang['select_language']; ?>:</strong>
      <form method="post" style="display: inline;">
        <select name="language" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color);">
          <option value="ko" <?php echo $lang_code == 'ko' ? 'selected' : ''; ?>>한국어</option>
          <option value="en" <?php echo $lang_code == 'en' ? 'selected' : ''; ?>>English</option>
          <option value="ja" <?php echo $lang_code == 'ja' ? 'selected' : ''; ?>>日本語</option>
          <option value="zh" <?php echo $lang_code == 'zh' ? 'selected' : ''; ?>>中文</option>
        </select>
        <noscript><input type="submit" value="<?php echo $lang['apply']; ?>"></noscript>
      </form>
  </div>
  
  <!-- 관리자 페이지용 테마 토글 -->
  <button id="admin-theme-toggle" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; padding: 5px;">
      <span class="icon-sun">☀️</span>
      <span class="icon-moon" style="display: none;">🌙</span>
  </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('admin-theme-toggle');
    const iconSun = toggleBtn.querySelector('.icon-sun');
    const iconMoon = toggleBtn.querySelector('.icon-moon');
    
    function updateIcon() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        if (isDark) {
            iconSun.style.display = 'none';
            iconMoon.style.display = 'inline';
        } else {
            iconSun.style.display = 'inline';
            iconMoon.style.display = 'none';
        }
    }
    
    updateIcon();
    
    toggleBtn.addEventListener('click', function() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        
        if (isDark) {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        }
        
        updateIcon();
    });
});
</script>
