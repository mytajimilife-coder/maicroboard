<?php
// 페이지 제목 설정
$page_title = $page_title ?? 'MicroBoard';
?>
<!DOCTYPE html>
<html lang="<?php echo substr($lang_code ?? 'ko', 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - MicroBoard</title>
    <link rel="stylesheet" href="skin/default/style.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <a href="list.php">
                    <img src="img/logo.png" alt="MicroBoard Logo" style="height: 40px;">
                </a>
            </div>
            
            <nav class="main-nav">
                <ul class="nav-menu">
                    <?php if (isLoggedIn()): ?>
                        <li><a href="list.php"><?php echo $lang['board_list']; ?></a></li>
                        <li><a href="user/mypage.php"><?php echo $lang['mypage']; ?></a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/index.php"><?php echo $lang['admin_home']; ?></a></li>
                            <li><a href="admin/users.php"><?php echo $lang['user_management']; ?></a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="user-info">
                <div class="lang-selector" style="margin-right: 15px; display: flex; gap: 8px; align-items: center;">
                    <?php 
                    $langs = ['ko' => '🇰🇷', 'en' => '🇺🇸', 'ja' => '🇯🇵', 'zh' => '🇨🇳'];
                    $current_lang = $_SESSION['lang'] ?? 'ko';
                    foreach ($langs as $code => $flag) {
                        $params = $_GET;
                        $params['lang'] = $code;
                        $url = '?' . http_build_query($params);
                        $opacity = ($current_lang === $code) ? '1' : '0.4';
                        echo "<a href=\"{$url}\" style=\"text-decoration: none; opacity: {$opacity}; transition: opacity 0.2s; font-size: 1.2em; filter: grayscale(" . ($current_lang === $code ? '0' : '1') . ");\">{$flag}</a>";
                    }
                    ?>
                </div>
                <?php if (isLoggedIn()): ?>
                    <span class="username">
                        <?php echo htmlspecialchars($_SESSION['user']); ?><?php echo $lang['user_suffix']; ?>
                        <?php 
                        // 포인트 표시
                        $db = getDB();
                        $stmt = $db->prepare("SELECT mb_point FROM mb1_member WHERE mb_id = ?");
                        $stmt->execute([$_SESSION['user']]);
                        $member = $stmt->fetch();
                        if ($member && isset($member['mb_point'])) {
                            echo " <span style='font-size: 0.9em; color: #ffc107;'>(" . number_format($member['mb_point']) . " P)</span>";
                        }
                        ?>
                    </span>
                    <a href="logout.php" class="btn secondary"><?php echo $lang['logout']; ?></a>
                <?php else: ?>
                    <a href="login.php" class="btn"><?php echo $lang['login']; ?></a>
                    <a href="register.php" class="btn secondary"><?php echo $lang['register']; ?></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main-content">
