<?php
// 페이지 제목 설정
$page_title = $page_title ?? 'MicroBoard';

// 루트 경로 계산
$root_path = './';
if (file_exists('./config.php')) {
    $root_path = './';
} elseif (file_exists('../config.php')) {
    $root_path = '../';
} elseif (file_exists('../../config.php')) {
    $root_path = '../../';
}

// 설정 가져오기
$config = get_config();
$default_theme = $config['cf_theme'] ?? 'light';
$bg_type = $config['cf_bg_type'] ?? 'color';
$bg_value = $config['cf_bg_value'] ?? '#ffffff';

// SEO 메타 데이터 설정 (기본값)
$page_title = $page_title ?? 'MicroBoard';
$meta_description = $meta_description ?? 'MicroBoard - 가볍고 강력한 PHP 게시판';
$meta_keywords = $meta_keywords ?? 'microboard, php board, community, 게시판';
$og_image = $og_image ?? $root_path . 'img/logo.png';
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$canonical_url = $canonical_url ?? $current_url;

// 배경 스타일 생성
$custom_bg_style = '';
if ($bg_type === 'image') {
    // 이미지 경로에 루트 경로 적용
    $custom_bg_style = "url('" . $root_path . htmlspecialchars($bg_value) . "')";
} else {
    $custom_bg_style = $bg_value;
}
?>
<!DOCTYPE html>
<html lang="<?php echo substr($lang_code ?? 'ko', 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($page_title); ?> - MicroBoard</title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="MicroBoard">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image); ?>">
    
    <?php
    // SEO 설정 불러오기
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM mb1_seo_config WHERE id = 1");
        $seo_config = $stmt->fetch();
        
        if ($seo_config) {
            // Bing Webmaster 인증
            if (!empty($seo_config['bing_verification'])) {
                echo '<meta name="msvalidate.01" content="' . htmlspecialchars($seo_config['bing_verification']) . '">' . "\n    ";
            }
            
            // Google Search Console 인증
            if (!empty($seo_config['google_search_console'])) {
                echo '<meta name="google-site-verification" content="' . htmlspecialchars($seo_config['google_search_console']) . '">' . "\n    ";
            }
            
            // Google Analytics (GA4)
            if (!empty($seo_config['google_analytics'])) {
                $ga_id = htmlspecialchars($seo_config['google_analytics']);
                echo "<!-- Google Analytics -->\n    ";
                echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$ga_id}\"></script>\n    ";
                echo "<script>\n      window.dataLayer = window.dataLayer || [];\n      function gtag(){dataLayer.push(arguments);}\n      gtag('js', new Date());\n      gtag('config', '{$ga_id}');\n    </script>\n    ";
            }
            
            // Google Tag Manager (Head)
            if (!empty($seo_config['google_tag_manager'])) {
                $gtm_id = htmlspecialchars($seo_config['google_tag_manager']);
                echo "<!-- Google Tag Manager -->\n    ";
                echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n    })(window,document,'script','dataLayer','{$gtm_id}');</script>\n    ";
                echo "<!-- End Google Tag Manager -->\n    ";
            }
            
            // Google AdSense
            if (!empty($seo_config['google_adsense'])) {
                $adsense_id = htmlspecialchars($seo_config['google_adsense']);
                echo "<!-- Google AdSense -->\n    ";
                echo "<script async src=\"https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={$adsense_id}\" crossorigin=\"anonymous\"></script>\n    ";
            }
            
            // 헤더 추가 스크립트
            if (!empty($seo_config['header_script'])) {
                echo "<!-- Custom Header Script -->\n    ";
                echo $seo_config['header_script'] . "\n    ";
            }
        }
    } catch (Exception $e) {
        // SEO 테이블이 없을 경우 무시
    }
    ?>
    
    <link rel="stylesheet" href="<?php echo $root_path; ?>skin/default/style.css">
    <link rel="icon" type="image/svg+xml" href="<?php echo $root_path; ?>img/favicon.svg">
    <link rel="alternate icon" href="<?php echo $root_path; ?>img/favicon.svg">
    <style>
        /* 관리자 설정 배경 적용 */
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
<body>
    <?php
    // Google Tag Manager (Body) - noscript 버전
    try {
        if (isset($seo_config) && !empty($seo_config['google_tag_manager'])) {
            $gtm_id = htmlspecialchars($seo_config['google_tag_manager']);
            echo "<!-- Google Tag Manager (noscript) -->\n    ";
            echo "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$gtm_id}\"\n    height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n    ";
            echo "<!-- End Google Tag Manager (noscript) -->\n    ";
        }
    } catch (Exception $e) {
        // 무시
    }
    ?>
    <!-- 공지사항 바 -->
    <div id="notice-bar" style="background: var(--primary-color); color: white; padding: 0.75rem 1rem; text-align: center; position: relative; display: none;">
        <div id="notice-content" style="font-weight: 600; font-size: 0.9rem;"></div>
        <button id="close-notice" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; line-height: 1;">×</button>
    </div>

    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo $root_path; ?>index.php" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
                    <?php
                    // 로고 출력 훅 호출
                    $logo = 'img/logo.png'; // 기본 로고
                    $logo = apply_hooks('before_logo_display', $logo);
                    
                    // 사이트명 가져오기
                    $site_title = 'MicroBoard'; // 기본 사이트명
                    if (function_exists('get_config')) {
                        $config = get_config();
                        $site_title = $config['cf_site_title'] ?? 'MicroBoard';
                    }
                    ?>
                    <?php if (file_exists($logo)): ?>
                        <img src="<?php echo $root_path . $logo; ?>" alt="<?php echo htmlspecialchars($site_title); ?> Logo" style="height: 30px;">
                    <?php else: ?>
                        <span style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);"><?php echo htmlspecialchars($site_title); ?></span>
                    <?php endif; ?>
                </a>
            </div>
            
            <nav class="main-nav">
                <ul class="nav-menu">
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo $root_path; ?>list.php"><?php echo $lang['board_list']; ?></a></li>
                        <li><a href="<?php echo $root_path; ?>search.php">🔍 <?php echo $lang['integrated_search'] ?? '통합검색'; ?></a></li>
                        <li><a href="<?php echo $root_path; ?>user/mypage.php"><?php echo $lang['mypage']; ?></a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="<?php echo $root_path; ?>admin/index.php"><?php echo $lang['admin_home']; ?></a></li>
                            <li><a href="<?php echo $root_path; ?>admin/users.php"><?php echo $lang['user_management']; ?></a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="<?php echo $root_path; ?>search.php">🔍 <?php echo $lang['integrated_search'] ?? '통합검색'; ?></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="user-info">
                <!-- 테마 토글 버튼 -->
                <button id="theme-toggle" class="theme-toggle-btn" title="Toggle Theme" style="margin-right: 15px;">
                    <span class="icon-sun">☀️</span>
                    <span class="icon-moon" style="display: none;">🌙</span>
                </button>

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
                        // 포인트 시스템 사용 여부 확인
                        $config = get_config();
                        if (isset($config['cf_use_point']) && $config['cf_use_point']) {
                            $db = getDB();
                            $stmt = $db->prepare("SELECT mb_point FROM mb1_member WHERE mb_id = ?");
                            $stmt->execute([$_SESSION['user']]);
                            $member = $stmt->fetch();
                            if ($member && isset($member['mb_point'])) {
                                echo " <span style='font-size: 0.9em; color: #ffc107;'>(" . number_format($member['mb_point']) . " P)</span>";
                            }
                        }
                        ?>
                    </span>
                    <a href="<?php echo $root_path; ?>logout.php" class="btn secondary"><?php echo $lang['logout']; ?></a>
                <?php else: ?>
                    <a href="<?php echo $root_path; ?>login.php" class="btn"><?php echo $lang['login']; ?></a>
                    <a href="<?php echo $root_path; ?>register.php" class="btn secondary"><?php echo $lang['register']; ?></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main-content">
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('theme-toggle');
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

        // 공지사항 슬라이드 기능
        const noticeBar = document.getElementById('notice-bar');
        const noticeContent = document.getElementById('notice-content');
        const closeNotice = document.getElementById('close-notice');

        // 공지사항 가져오기
        fetch('<?php echo $root_path; ?>get_notices.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notices && data.notices.length > 0) {
                    let currentIndex = 0;
                    const notices = data.notices;

                    // 공지사항 표시
                    noticeContent.textContent = notices[currentIndex].title;
                    noticeBar.style.display = 'block';

                    // 공지사항 클릭 시 공지사항 페이지로 이동
                    noticeContent.addEventListener('click', function() {
                        window.location.href = '<?php echo $root_path; ?>notice_view.php?id=' + notices[currentIndex].id;
                    });

                    // 공지사항 닫기 버튼
                    closeNotice.addEventListener('click', function() {
                        noticeBar.style.display = 'none';
                        localStorage.setItem('noticeHidden', 'true');
                    });

                    // 오늘은 공지 끄기 기능
                    if (localStorage.getItem('noticeHidden') === 'true') {
                        noticeBar.style.display = 'none';
                    }

                    // 슬라이드 기능 (5초 간격)
                    if (notices.length > 1) {
                        setInterval(function() {
                            currentIndex = (currentIndex + 1) % notices.length;
                            noticeContent.textContent = notices[currentIndex].title;

                            // 공지사항 클릭 시 공지사항 페이지로 이동
                            noticeContent.onclick = function() {
                                window.location.href = '<?php echo $root_path; ?>notice_view.php?id=' + notices[currentIndex].id;
                            };
                        }, 5000);
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching notices:', error);
            });
    });
    </script>
