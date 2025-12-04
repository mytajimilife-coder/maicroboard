<?php
define('IN_ADMIN', true);
require_once 'common.php';

$db = getDB();
$action = $_GET['action'] ?? '';
$bo_table = $_GET['bo_table'] ?? '';

// CSRF 토큰 검증
if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($action === 'delete' && $bo_table)) {
    if (!isset($_REQUEST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_REQUEST['csrf_token'])) {
        die($lang['csrf_token_invalid']);
    }
}

// 게시판 생성/수정
if ($_POST) {
  $bo_table = $_POST['bo_table'];
  
  // 테이블명 검증 (영문, 숫자, 언더스코어만 허용)
  if (!preg_match('/^[a-zA-Z0-9_]+$/', $bo_table)) {
    die($lang['invalid_table_name'] ?? '잘못된 테이블명입니다. 영문, 숫자, 언더스코어만 사용 가능합니다.');
  }
  
  // 플러그인 목록 처리
  $bo_plugins = isset($_POST['bo_plugins']) && is_array($_POST['bo_plugins']) ? implode(',', $_POST['bo_plugins']) : '';
  
  $data = [
    'bo_table' => $bo_table,
    'bo_subject' => $_POST['bo_subject'],
    'bo_admin' => $_POST['bo_admin'],
    'bo_list_count' => (int)$_POST['bo_list_count'],
    'bo_use_comment' => isset($_POST['bo_use_comment']) ? 1 : 0,
    'bo_skin' => $_POST['bo_skin'] ?? 'default',
    'bo_plugins' => $bo_plugins
  ];
  
  // 기존 게시판인지 확인
  $stmt = $db->prepare("SELECT COUNT(*) FROM mb1_board_config WHERE bo_table = ?");
  $stmt->execute([$bo_table]);
  $is_existing = $stmt->fetchColumn() > 0;
  
  // 게시판 설정 저장
  $sql = "REPLACE INTO mb1_board_config SET 
    bo_table = :bo_table, 
    bo_subject = :bo_subject, 
    bo_admin = :bo_admin, 
    bo_list_count = :bo_list_count, 
    bo_use_comment = :bo_use_comment,
    bo_skin = :bo_skin,
    bo_plugins = :bo_plugins";
  
  $stmt = $db->prepare($sql);
  $stmt->execute($data);
  
  // 새 게시판인 경우 테이블 생성
  if (!$is_existing) {
    try {
      // 게시판 테이블 생성 (mb1_write_{bo_table})
      $write_table = "mb1_write_" . $bo_table;
      $db->exec("
        CREATE TABLE IF NOT EXISTS `{$write_table}` (
          `wr_id` int(11) NOT NULL AUTO_INCREMENT,
          `wr_subject` varchar(255) NOT NULL,
          `wr_content` longtext NOT NULL,
          `wr_name` varchar(50) NOT NULL,
          `wr_datetime` datetime NOT NULL,
          `wr_hit` int(11) NOT NULL DEFAULT 0,
          `wr_comment` int(11) NOT NULL DEFAULT 0,
          PRIMARY KEY (`wr_id`),
          KEY `wr_name` (`wr_name`),
          KEY `wr_datetime` (`wr_datetime`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
      
      // 댓글 테이블 생성 (mb1_comment_{bo_table})
      $comment_table = "mb1_comment_" . $bo_table;
      $db->exec("
        CREATE TABLE IF NOT EXISTS `{$comment_table}` (
          `co_id` int(11) NOT NULL AUTO_INCREMENT,
          `wr_id` int(11) NOT NULL,
          `co_content` text NOT NULL,
          `co_name` varchar(50) NOT NULL,
          `co_datetime` datetime NOT NULL,
          PRIMARY KEY (`co_id`),
          KEY `wr_id` (`wr_id`),
          KEY `co_name` (`co_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
      
      // 파일 테이블 생성 (mb1_board_file_{bo_table})
      $file_table = "mb1_board_file_" . $bo_table;
      $db->exec("
        CREATE TABLE IF NOT EXISTS `{$file_table}` (
          `bf_no` int(11) NOT NULL AUTO_INCREMENT,
          `wr_id` int(11) NOT NULL,
          `bf_source` varchar(255) NOT NULL,
          `bf_file` varchar(255) NOT NULL,
          `bf_download` int(11) NOT NULL DEFAULT 0,
          `bf_content` text,
          `bf_filesize` int(11) NOT NULL DEFAULT 0,
          `bf_datetime` datetime NOT NULL,
          PRIMARY KEY (`bf_no`),
          KEY `wr_id` (`wr_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
      ");
      
    } catch (PDOException $e) {
      die($lang['table_creation_failed'] ?? '테이블 생성 실패: ' . $e->getMessage());
    }
  }
  
  header('Location: board.php');
  exit;
}

// 게시판 삭제
if ($action === 'delete' && $bo_table) {
  // 테이블명 검증
  if (!preg_match('/^[a-zA-Z0-9_]+$/', $bo_table)) {
    die($lang['invalid_table_name'] ?? '잘못된 테이블명입니다.');
  }
  
  try {
    // 게시판 설정 삭제
    $stmt = $db->prepare("DELETE FROM mb1_board_config WHERE bo_table = ?");
    $stmt->execute([$bo_table]);
    
    // 관련 테이블 삭제
    $write_table = "mb1_write_" . $bo_table;
    $comment_table = "mb1_comment_" . $bo_table;
    $file_table = "mb1_board_file_" . $bo_table;
    
    // 테이블이 존재하는지 확인 후 삭제
    $db->exec("DROP TABLE IF EXISTS `{$write_table}`");
    $db->exec("DROP TABLE IF EXISTS `{$comment_table}`");
    $db->exec("DROP TABLE IF EXISTS `{$file_table}`");
    
  } catch (PDOException $e) {
    die($lang['table_deletion_failed'] ?? '테이블 삭제 실패: ' . $e->getMessage());
  }
  
  header('Location: board.php');
  exit;
}

$board = [];
if ($bo_table) {
  $stmt = $db->prepare("SELECT * FROM mb1_board_config WHERE bo_table = ?");
  $stmt->execute([$bo_table]);
  $board = $stmt->fetch();
}

$boards = $db->query("SELECT * FROM mb1_board_config ORDER BY bo_table")->fetchAll();

// 플러그인 목록 가져오기
$plugin_dir = '../plugin';
$available_plugins = [];
if (is_dir($plugin_dir)) {
    $dirs = glob($plugin_dir . '/*', GLOB_ONLYDIR);
    if ($dirs) {
        foreach ($dirs as $dir) {
            $plugin_name = basename($dir);
            $available_plugins[] = $plugin_name;
        }
    }
}
?>
<h2><?php echo $lang['board_manager']; ?></h2>
<a href="board.php?action=create" class="btn"><?php echo $lang['create_board']; ?></a>

<h3><?php echo $lang['board_list_title']; ?></h3>
<table>
  <tr>
    <th><?php echo $lang['table_name']; ?></th>
    <th><?php echo $lang['board_name']; ?></th>
    <th><?php echo $lang['manager']; ?></th>
    <th><?php echo $lang['list_count']; ?></th>
    <th><?php echo $lang['use_comment']; ?></th>
    <th><?php echo $lang['skin']; ?></th>
    <th><?php echo $lang['function']; ?></th>
  </tr>
  <?php foreach ($boards as $b): ?>
  <tr>
    <td><?php echo $b['bo_table']; ?></td>
    <td><?php echo $b['bo_subject']; ?></td>
    <td><?php echo $b['bo_admin']; ?></td>
    <td><?php echo $b['bo_list_count']; ?></td>
    <td><?php echo $b['bo_use_comment'] ? 'Y' : 'N'; ?></td>
    <td><?php echo $b['bo_skin']; ?></td>
    <td>
      <a href="board.php?bo_table=<?php echo $b['bo_table']; ?>" class="btn"><?php echo $lang['edit']; ?></a>
      <a href="board.php?action=delete&bo_table=<?php echo $b['bo_table']; ?>&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>" 
         class="btn" onclick="return confirm('<?php echo $lang['delete_confirm']; ?>')"><?php echo $lang['delete']; ?></a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if ($action === 'create' || $bo_table): ?>
<h3><?php echo $lang['board']; ?> <?php echo $bo_table ? $lang['edit'] : $lang['create']; ?></h3>
<form method="post">
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
  <div>
    <label><?php echo $lang['table_name_eng']; ?>:</label>
    <input type="text" name="bo_table" value="<?php echo $board['bo_table'] ?? ''; ?>" required>
  </div>
  <div>
    <label><?php echo $lang['board_name']; ?>:</label>
    <input type="text" name="bo_subject" value="<?php echo $board['bo_subject'] ?? ''; ?>" required>
  </div>
  <div>
    <label><?php echo $lang['manager']; ?>:</label>
    <input type="text" name="bo_admin" value="<?php echo $board['bo_admin'] ?? 'admin'; ?>">
  </div>
  <div>
    <label><?php echo $lang['list_count']; ?>:</label>
    <input type="number" name="bo_list_count" value="<?php echo $board['bo_list_count'] ?? 15; ?>">
  </div>
  <div>
    <label>
      <input type="checkbox" name="bo_use_comment" <?php echo ($board['bo_use_comment'] ?? 0) ? 'checked' : ''; ?>>
      <?php echo $lang['use_comment_label']; ?>
    </label>
  </div>
  <div>
    <label><?php echo $lang['skin']; ?>:</label>
    <select name="bo_skin">
      <option value="default" <?php echo ($board['bo_skin'] ?? 'default') === 'default' ? 'selected' : ''; ?>><?php echo $lang['default_skin']; ?></option>
      <option value="modern" <?php echo ($board['bo_skin'] ?? 'default') === 'modern' ? 'selected' : ''; ?>><?php echo $lang['modern_skin']; ?></option>
    </select>
  </div>
  
  <!-- 플러그인 선택 -->
  <div style="margin-top: 20px; padding: 15px; background: var(--bg-secondary); border-radius: 5px; border: 1px solid var(--border-color);">
    <label style="display: block; margin-bottom: 10px; font-weight: bold;">플러그인 설정 (Plugins)</label>
    <?php if (empty($available_plugins)): ?>
        <p style="color: #666;">설치된 플러그인이 없습니다. (plugin 폴더에 플러그인을 추가하세요)</p>
    <?php else: ?>
        <?php 
        $active_plugins = isset($board['bo_plugins']) ? explode(',', $board['bo_plugins']) : [];
        foreach ($available_plugins as $plugin): 
        ?>
        <label style="display: inline-block; margin-right: 15px; margin-bottom: 5px;">
            <input type="checkbox" name="bo_plugins[]" value="<?php echo htmlspecialchars($plugin); ?>" 
                   <?php echo in_array($plugin, $active_plugins) ? 'checked' : ''; ?>>
            <?php echo htmlspecialchars($plugin); ?>
        </label>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
  
  <button type="submit" class="btn" style="margin-top: 20px;"><?php echo $lang['save']; ?></button>
</form>
<?php endif; ?>
</body>
</html>
