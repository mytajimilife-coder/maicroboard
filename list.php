<?php
require_once 'config.php';
requireLogin();

// 페이지 제목 설정
$page_title = $lang['board_list'];

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

$board = [];
$board_skin = 'default';

if (!empty($_GET['bo_table'])) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM mb1_board_config WHERE bo_table = ?');
    $stmt->execute([$_GET['bo_table']]);
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
?>

<link rel="stylesheet" href="skin/<?php echo $board_skin; ?>/style.css">

<div class="content-wrapper">
  <?php run_event('board_head', $board); ?>
  <?php
  $skin_path = "skin/$board_skin/list.skin.php";
  if (file_exists($skin_path)) {
    $board_config = ['bo_subject' => $lang['board_list']];
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
    $bo_table = $_GET['bo_table'] ?? '';
    
    // 검색 및 페이징 변수 전달
    $page = $GLOBALS['page'];
    $total_pages = $GLOBALS['total_pages'];
    $stx = $GLOBALS['stx'];
    $sfl = $GLOBALS['sfl'];
    
    include $skin_path;
  } else {
    echo '<p>' . $lang['skin_not_found'] . '</p>';
  }
  ?>
</div>

<?php
// 푸터 포함
require_once 'inc/footer.php';
?>
