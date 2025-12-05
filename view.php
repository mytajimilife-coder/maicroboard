<?php
require_once 'config.php';
requireLogin();

// 입력값 검증
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$action = filter_var($_GET['action'] ?? '', FILTER_SANITIZE_STRING);
$bo_table = filter_var($_GET['bo_table'] ?? '', FILTER_SANITIZE_STRING);

if (!$id || $id <= 0) {
  header('Location: list.php');
  exit;
}

// 게시글 정보 가져오기
// getPost 함수가 bo_table을 지원하는지 확인 필요하지만, 기존 코드대로 호출
// 만약 bo_table별 테이블을 쓴다면 getPost($id, $bo_table) 형태여야 함.
// 여기서는 기존 view.php 로직을 따름.
$post = getPost($id); 

if (!$post || !$post['wr_id']) {
  header('Location: list.php');
  exit;
}

// SEO 메타 데이터 설정
$page_title = htmlspecialchars($post['wr_subject']);
// 본문 내용에서 태그 제거하고 앞부분만 추출하여 설명으로 사용
$plain_content = strip_tags($post['wr_content']);
$meta_description = mb_substr(str_replace(["\r", "\n"], " ", $plain_content), 0, 160, 'utf-8');
$meta_keywords = 'microboard, ' . htmlspecialchars($post['wr_name']);

// 헤더 포함
require_once 'inc/header.php';

// CSRF 토큰 검증 (삭제 작업 시)
if ($action === 'delete' && $id) {
  if (!isAdmin() && $_SESSION['user'] !== $post['wr_name']) {
    header('Location: list.php');
    exit;
  }
  
  if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['token'])) {
    die($lang['delete_permission_denied']);
  }
  
  deletePost($id);
  header('Location: list.php');
  exit;
}

incrementView($id);
?>

<div class="content-wrapper">
<?php
// XSS 공격 방지를 위한 추가 이스케이프
$post_view = [
  'wr_id' => $post['wr_id'],
  'wr_subject' => htmlspecialchars($post['wr_subject'], ENT_QUOTES, 'UTF-8'),
  // 에디터 사용 시 HTML을 그대로 출력해야 함 (XSS 방지는 입력 시 처리하거나 HTMLPurifier 등을 사용 권장)
  // 여기서는 에디터 호환성을 위해 htmlspecialchars 및 nl2br 제거
  'wr_content' => $post['wr_content'], 
  'wr_name' => htmlspecialchars($post['wr_name'], ENT_QUOTES, 'UTF-8'),
  'wr_datetime' => $post['wr_datetime'],
  'wr_hit' => $post['wr_hit']
];

// 스킨 로드 (기본값 default)
// bo_table이 있으면 해당 게시판 스킨 사용 가능
$board_skin = 'default';
if ($bo_table) {
    $db = getDB();
    $stmt = $db->prepare('SELECT bo_skin FROM mb1_board_config WHERE bo_table = ?');
    $stmt->execute([$bo_table]);
    $config = $stmt->fetch();
    if ($config) $board_skin = $config['bo_skin'];
}
?>
<link rel="stylesheet" href="skin/<?php echo $board_skin; ?>/style.css">

<div class="view-wrap">
    <div class="view-header">
        <h1><?php echo $post_view['wr_subject']; ?></h1>
        <div class="view-info">
            <span class="writer"><?php echo $post_view['wr_name']; ?></span>
            <span class="date"><?php echo $post_view['wr_datetime']; ?></span>
            <span class="hit"><?php echo $lang['hit']; ?>: <?php echo $post_view['wr_hit']; ?></span>
        </div>
    </div>
    
    <div class="view-content">
        <?php echo $post_view['wr_content']; ?>
    </div>
    
    <div class="view-btn">
        <a href="list.php?bo_table=<?php echo htmlspecialchars($bo_table); ?>" class="btn secondary"><?php echo $lang['list']; ?></a>
        <?php if (isAdmin() || $_SESSION['user'] === $post['wr_name']): ?>
            <a href="write.php?w=u&id=<?php echo $id; ?>&bo_table=<?php echo htmlspecialchars($bo_table); ?>" class="btn"><?php echo $lang['edit']; ?></a>
            <a href="view.php?action=delete&id=<?php echo $id; ?>&bo_table=<?php echo htmlspecialchars($bo_table); ?>&token=<?php echo $_SESSION['csrf_token']; ?>" 
               class="btn" onclick="return confirm('<?php echo $lang['delete_confirm']; ?>');" style="background-color: #dc3545; color: white;"><?php echo $lang['delete']; ?></a>
        <?php endif; ?>
    </div>
</div>

</div>

<?php require_once 'inc/footer.php'; ?>
