<?php
require_once 'config.php';
$page_title = isset($lang['home']) ? $lang['home'] : "MicroBoard";
require_once 'inc/header.php';
?>

<!-- 랜딩 히어로 섹션 -->
<div class="landing-hero" style="min-height: 60vh; background: linear-gradient(135deg, var(--bg-color), var(--bg-tertiary)); display: flex; align-items: center; justify-content: center; padding: 4rem 1.5rem;">
    <div class="hero-content" style="text-align: center; max-width: 800px; width: 100%;">
        <div style="margin-bottom: 1rem; dispaly: inline-block;">
            <span style="font-size: 4rem;">✨</span>
        </div>
        <h1 style="font-size: clamp(2.5rem, 5vw, 4rem); margin-bottom: 1.5rem; background: linear-gradient(to right, var(--primary-color), var(--primary-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: var(--primary-color); font-weight: 800; line-height: 1.1;"><?php echo $lang['welcome_to_microboard']; ?></h1>
        <p style="font-size: clamp(1.1rem, 2vw, 1.25rem); color: var(--text-light); margin-bottom: 2.5rem; line-height: 1.6; max-width: 600px; margin-left: auto; margin-right: auto;"><?php echo $lang['landing_description']; ?></p>
        
        <div class="hero-actions" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <?php if (isLoggedIn()): ?>
                <a href="list.php" class="btn btn-large" style="background: var(--primary-color); color: white; padding: 1rem 2.5rem; border-radius: 999px; font-size: 1.1rem; text-decoration: none; transition: transform 0.2s; display: inline-flex; align-items: center; gap: 0.5rem;">
                    🚀 <?php echo $lang['go_to_board']; ?>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-large" style="background: var(--primary-color); color: white; padding: 1rem 2.5rem; border-radius: 999px; font-size: 1.1rem; text-decoration: none; transition: transform 0.2s; display: inline-flex; align-items: center; gap: 0.5rem;">
                    🔐 <?php echo $lang['login']; ?>
                </a>
                <a href="register.php" class="btn btn-large btn-outline" style="border: 2px solid var(--primary-color); color: var(--primary-color)!important; padding: 1rem 2.5rem; border-radius: 999px; font-size: 1.1rem; background: transparent; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem;">
                    📝 <?php echo $lang['register']; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 게시판 목록 섹션 -->
<?php
$db = getDB();
try {
    $boards = $db->query("SELECT * FROM mb1_board_config ORDER BY bo_subject ASC")->fetchAll();
} catch (Exception $e) {
    $boards = [];
}

if (!empty($boards)): 
?>
<div class="content-wrapper" style="padding-top: 4rem; padding-bottom: 4rem;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h2 style="font-size: 2rem; color: var(--secondary-color); font-weight: 700;">
            📋 <?php echo isset($lang['board_list']) ? $lang['board_list'] : 'Boards'; ?>
        </h2>
        <div style="width: 50px; height: 4px; background: var(--primary-color); margin: 1rem auto; border-radius: 2px;"></div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
        <?php foreach ($boards as $board): ?>
        <div class="card" style="transition: transform 0.2s, box-shadow 0.2s; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem; background: var(--bg-color); height: 100%; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
            <div style="flex: 1;">
                <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem; color: var(--text-color);">
                    <a href="list.php?bo_table=<?php echo $board['bo_table']; ?>" style="color: inherit; text-decoration: none;">
                        <?php echo htmlspecialchars($board['bo_subject']); ?>
                    </a>
                </h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.6;">
                    <?php echo !empty($board['bo_description']) ? htmlspecialchars($board['bo_description']) : 'Join the discussion in ' . htmlspecialchars($board['bo_subject']); ?>
                </p>
            </div>
            <div style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.85rem; color: var(--text-muted);">Admin: <?php echo htmlspecialchars($board['bo_admin']); ?></span>
                <a href="list.php?bo_table=<?php echo $board['bo_table']; ?>" style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem; text-decoration: none;">
                    Explore &rarr;
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- 기능 소개 섹션 (Features) -->
<div style="background: var(--bg-secondary); padding: 5rem 1.5rem; border-top: 1px solid var(--border-color);">
    <div class="content-wrapper">
        <div style="text-align: center; margin-bottom: 4rem;">
            <span style="color: var(--primary-color); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; font-size: 0.9rem;">Features</span>
            <h2 style="font-size: 2.5rem; font-weight: 800; color: var(--secondary-color); margin-top: 0.5rem; margin-bottom: 1rem;">Why MicroBoard?</h2>
            <p style="color: var(--text-light); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Simple, Fast, and Modern PHP Bulletin Board System designed for community.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1.5rem; background: white; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; margin: 0 auto 1.5rem; box-shadow: var(--shadow-md);">⚡</div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-color);">Fast Performance</h3>
                <p style="color: var(--text-light); line-height: 1.6;">Optimized for speed with minimal overhead. Light on resources, heavy on performance.</p>
            </div>
            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1.5rem; background: white; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; margin: 0 auto 1.5rem; box-shadow: var(--shadow-md);">🎨</div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-color);">Modern Design</h3>
                <p style="color: var(--text-light); line-height: 1.6;">Beautiful, responsive UI out of the box. Dark mode support and customizable themes.</p>
            </div>
            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1.5rem; background: white; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; margin: 0 auto 1.5rem; box-shadow: var(--shadow-md);">🛡️</div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-color);">Secure</h3>
                <p style="color: var(--text-light); line-height: 1.6;">Built with security in mind. XSS protection, CSRF prevention, and secure authentication.</p>
            </div>
        </div>
    </div>
</div>

<style>
/* 카드 호버 효과 추가 */
.card:hover {
    transform: translateY(-5px) !important;
    box-shadow: var(--shadow-lg) !important;
}
</style>

<?php require_once 'inc/footer.php'; ?>
