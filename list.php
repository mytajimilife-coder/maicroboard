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

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$stx = $_GET['stx'] ?? '';
$sfl = $_GET['sfl'] ?? '';
$limit = 15;

$total_posts = getTotalPostCount($stx, $sfl);
$total_pages = ceil($total_posts / $limit);
$posts = loadPosts($page, $limit, $stx, $sfl);
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
