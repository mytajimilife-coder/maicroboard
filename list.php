<?php
require_once 'config.php';
requireLogin();

// 게시판 정보 가져오기
$board = [];
$board_skin = 'default';
$bo_table = $_GET['bo_table'] ?? '';

if ($bo_table) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM mb1_board_config WHERE bo_table = ?');
    $stmt->execute([$bo_table]);
    $board = $stmt->fetch();
    
    if ($board) {
        $board_skin = $board['bo_skin'] ?? 'default';
        
        // 플러그인 로드
        if (!empty($board['bo_plugins'])) {
            $plugins = explode(',', $board['bo_plugins']);
            foreach ($plugins as $plugin) {
                $plugin_file = "plugin/" . trim($plugin) . "/index.php";
                if (file_exists($plugin_file)) {
                    include_once $plugin_file;
                }
            }
        }
    }
}

// 페이지 제목 및 메타 데이터 설정
if ($board) {
    $page_title = $board['bo_subject'];
    $meta_description = $board['bo_subject'] . ' - MicroBoard 게시판입니다.';
} else {
    $page_title = $lang['board_list'];
    $meta_description = 'MicroBoard 전체 게시글 목록입니다.';
}

// 헤더 포함
require_once 'inc/header.php';

// bo_table이 없으면 게시판 목록 페이지를 보여줌
if (!$bo_table) {
    $db = getDB();
    try {
        $boards = $db->query("SELECT * FROM mb1_board_config ORDER BY bo_subject ASC")->fetchAll();
    } catch (Exception $e) {
        $boards = [];
    }

    if (!empty($boards)) {
        ?>
        <div class="content-wrapper" style="padding-top: 4rem; padding-bottom: 4rem;">
            <div class="section-header">
                <h2 class="section-title">
                    📋 <?php echo isset($lang['board_list']) ? $lang['board_list'] : 'Boards'; ?>
                </h2>
                <div style="width: 50px; height: 4px; background: var(--primary-color); margin: 1rem auto; border-radius: 2px;"></div>
            </div>
            
            <div class="board-grid">
                <?php foreach ($boards as $board): ?>
                <div class="board-card">
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem; color: var(--text-color);">
                            <a href="list.php?bo_table=<?php echo $board['bo_table']; ?>" style="color: inherit; text-decoration: none;">
                                <?php echo htmlspecialchars($board['bo_subject']); ?>
                            </a>
                        </h3>
                        <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.6;">
                            <?php echo !empty($board['bo_description']) ? htmlspecialchars($board['bo_description']) : sprintf($lang['join_discussion'] ?? 'Join the discussion in %s', htmlspecialchars($board['bo_subject'])); ?>
                        </p>
                    </div>
                    <div style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo isset($lang['admin_role']) ? $lang['admin_role'] : 'Admin'; ?>: <?php echo htmlspecialchars($board['bo_admin']); ?></span>
                        <a href="list.php?bo_table=<?php echo $board['bo_table']; ?>" style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem; text-decoration: none;">
                            <?php echo isset($lang['explore']) ? $lang['explore'] : 'Explore'; ?> &rarr;
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    } else {
        echo "<div class='content-wrapper' style='padding: 4rem 0; text-align: center;'><p>" . ($lang['no_posts'] ?? 'No boards found.') . "</p></div>";
    }

    require_once 'inc/footer.php';
    exit;
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$stx = $_GET['stx'] ?? '';
$sfl = $_GET['sfl'] ?? '';
$limit = 15;

$total_posts = getTotalPostCount($bo_table, $stx, $sfl);
$total_pages = ceil($total_posts / $limit);
$posts = loadPosts($bo_table, $page, $limit, $stx, $sfl);
?>

<link rel="stylesheet" href="skin/<?php echo $board_skin; ?>/style.css">

<div class="content-wrapper">
  <?php run_event('board_head', $board); ?>
  <?php
  $skin_path = "skin/$board_skin/list.skin.php";
  if (file_exists($skin_path)) {
    $board_config = $board ?: ['bo_subject' => $lang['board_list']]; // $board가 없으면 기본값 사용
    $list = array_map(function($key, $post) {
      return [
        'num' => $key + 1,
        'wr_id' => $post['wr_id'],
        'wr_subject' => htmlspecialchars($post['wr_subject']),
        'wr_name' => htmlspecialchars($post['wr_name']),
        'wr_datetime' => $post['wr_datetime'],
        'wr_hit' => $post['wr_hit']
      ];
    }, array_keys($posts), $posts);
    
    // 스킨 파일 로드
    include $skin_path;
  } else {
    echo "Skin not found: $skin_path";
  }
  ?>
</div>

<?php require_once 'inc/footer.php'; ?>
