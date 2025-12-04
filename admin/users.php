<?php
define('IN_ADMIN', true);
require_once 'common.php';

// 관리자 권한 확인
if (!isAdmin()) {
  die('<p>' . $lang['admin_only'] . '</p>');
}

$error = '';
$success = '';

// 회원 차단 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  // CSRF 토큰 검증
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $error = $lang['csrf_token_invalid'];
  } else {
    $username = trim($_POST['username'] ?? '');
    
    if (empty($username)) {
      $error = $lang['no_user_id'];
    } else {
      switch ($_POST['action']) {
        case 'delete_user':
          // 회원 탈퇴 처리
          if (deleteUser($username)) {
            $success = sprintf($lang['user_deleted_success'], $username);
          } else {
            $error = sprintf($lang['user_delete_fail'], $username);
          }
          break;
          
        case 'block_user':
          // 회원 차단 처리
          $reason = trim($_POST['block_reason'] ?? '');
          if (blockMember($username, $reason)) {
            $success = $lang['member_blocked_success'];
          } else {
            $error = $lang['withdraw_failed'];
          }
          break;
          
        case 'unblock_user':
          // 회원 차단 해제
          if (unblockMember($username)) {
            $success = $lang['member_unblocked_success'];
          } else {
            $error = $lang['withdraw_failed'];
          }
          break;
          
        case 'change_level':
          // 회원 등급 변경
          $level = (int)($_POST['level'] ?? 1);
          if (updateMemberLevel($username, $level)) {
            $success = $lang['level_updated'];
          } else {
            $error = $lang['level_update_failed'];
          }
          break;
      }
    }
  }
}

// 모든 회원 조회
$users = getAllUsers();
$total_users = count($users);
?>
<h1><?php echo $lang['user_management']; ?></h1>

<?php if ($error): ?>
  <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
    <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
    <?php echo htmlspecialchars($success); ?>
  </div>
<?php endif; ?>

<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
  <div>
      <strong><?php echo $lang['all_users']; ?>: <?php echo $total_users; ?></strong>
  </div>
  <div>
    <a href="index.php" class="btn" style="background: #6c757d; text-decoration: none; color: white; padding: 8px 16px; border-radius: 4px;">← <?php echo $lang['admin_home']; ?></a>
  </div>
</div>

<?php if ($users): ?>
  <div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
      <thead>
        <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
          <th style="padding: 12px 15px; text-align: left;"><?php echo $lang['user_id']; ?></th>
          <th style="padding: 12px 15px; text-align: center;"><?php echo $lang['member_level']; ?></th>
          <th style="padding: 12px 15px; text-align: center;"><?php echo $lang['member_status']; ?></th>
          <th style="padding: 12px 15px; text-align: left;"><?php echo $lang['join_date']; ?></th>
          <th style="padding: 12px 15px; text-align: center; width: 250px;"><?php echo $lang['action']; ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): 
          $member_info = getMemberInfo($user['mb_id']);
          $is_blocked = $member_info['mb_blocked'] ?? 0;
          $is_withdrawn = $member_info['mb_leave_date'] !== null;
          $level = $member_info['mb_level'] ?? 1;
        ?>
          <tr style="border-bottom: 1px solid #dee2e6; transition: background-color 0.2s ease;">
            <td style="padding: 12px 15px;">
              <strong><?php echo htmlspecialchars($user['mb_id']); ?></strong>
              <?php if ($user['mb_id'] === 'admin'): ?>
                <span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;"><?php echo $lang['admin_role']; ?></span>
              <?php endif; ?>
            </td>
            <td style="padding: 12px 15px; text-align: center;">
              <?php if ($user['mb_id'] === 'admin'): ?>
                <span style="background: #ffc107; color: #000; padding: 4px 12px; border-radius: 12px; font-weight: bold;">Lv.10</span>
              <?php else: ?>
                <form method="post" style="display: inline-flex; align-items: center; gap: 5px;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="action" value="change_level">
                  <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['mb_id']); ?>">
                  <select name="level" onchange="this.form.submit()" style="padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                      <option value="<?php echo $i; ?>" <?php echo $level == $i ? 'selected' : ''; ?>>Lv.<?php echo $i; ?></option>
                    <?php endfor; ?>
                  </select>
                </form>
              <?php endif; ?>
            </td>
            <td style="padding: 12px 15px; text-align: center;">
              <?php if ($is_withdrawn): ?>
                <span style="background: #6c757d; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;"><?php echo $lang['withdrawn']; ?></span>
              <?php elseif ($is_blocked): ?>
                <span style="background: #dc3545; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;" title="<?php echo htmlspecialchars($member_info['mb_blocked_reason'] ?? ''); ?>"><?php echo $lang['blocked']; ?></span>
              <?php else: ?>
                <span style="background: #28a745; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;"><?php echo $lang['active']; ?></span>
              <?php endif; ?>
            </td>
            <td style="padding: 12px 15px; color: #666;">
              <?php echo htmlspecialchars($user['mb_datetime'] ?? 'N/A'); ?>
            </td>
            <td style="padding: 12px 15px; text-align: center;">
              <?php if ($user['mb_id'] === 'admin'): ?>
                <span style="color: #666; font-style: italic;"><?php echo $lang['admin_protected']; ?></span>
              <?php else: ?>
                <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                  <?php if (!$is_withdrawn): ?>
                    <?php if ($is_blocked): ?>
                      <!-- 차단 해제 버튼 -->
                      <form method="post" style="display: inline;" onsubmit="return confirm('<?php echo $lang['confirm_unblock']; ?>');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="action" value="unblock_user">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['mb_id']); ?>">
                        <button type="submit" style="background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;"><?php echo $lang['unblock_member']; ?></button>
                      </form>
                    <?php else: ?>
                      <!-- 차단 버튼 -->
                      <button onclick="showBlockModal('<?php echo htmlspecialchars($user['mb_id']); ?>')" style="background: #ffc107; color: #000; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;"><?php echo $lang['block_member']; ?></button>
                    <?php endif; ?>
                    
                    <!-- 탈퇴 버튼 -->
                    <form method="post" style="display: inline;" onsubmit="return confirm('<?php echo $lang['delete_user_confirm']; ?>');">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['mb_id']); ?>">
                      <button type="submit" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;"><?php echo $lang['delete_user']; ?></button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <div style="text-align: center; padding: 40px; color: #888; background: #f8f9fa; border-radius: 8px; margin-top: 20px;">
    <p><?php echo $lang['no_users']; ?></p>
  </div>
<?php endif; ?>

<!-- 차단 모달 -->
<div id="blockModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%;">
    <h3><?php echo $lang['block_member']; ?></h3>
    <form method="post" id="blockForm">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <input type="hidden" name="action" value="block_user">
      <input type="hidden" name="username" id="blockUsername">
      
      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php echo $lang['block_reason']; ?>:</label>
        <textarea name="block_reason" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="<?php echo $lang['block_reason']; ?>"></textarea>
      </div>
      
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="closeBlockModal()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;"><?php echo $lang['cancel']; ?></button>
        <button type="submit" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;"><?php echo $lang['block_member']; ?></button>
      </div>
    </form>
  </div>
</div>

<style>
  .btn:hover {
    opacity: 0.8;
  }
  
  table tbody tr:hover {
    background-color: #f1f3f4;
  }
  
  form {
    margin: 0;
  }
  
  button[type="submit"] {
    transition: background-color 0.2s ease;
  }
  
  button[type="submit"]:hover {
    opacity: 0.9;
  }
</style>

<script>
function showBlockModal(username) {
  document.getElementById('blockUsername').value = username;
  document.getElementById('blockModal').style.display = 'flex';
}

function closeBlockModal() {
  document.getElementById('blockModal').style.display = 'none';
}

// 모달 외부 클릭 시 닫기
document.getElementById('blockModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeBlockModal();
  }
});
</script>
