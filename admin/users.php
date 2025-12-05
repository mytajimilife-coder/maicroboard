<?php
define('IN_ADMIN', true);
$admin_title_key = 'user_management';
require_once 'common.php';

// 관리자 권한 확인
if (!isAdmin()) {
  die('<div class="admin-card"><p>' . $lang['admin_only'] . '</p></div>');
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
          if (deleteUser($username)) {
            $success = sprintf($lang['user_deleted_success'], $username);
          } else {
            $error = sprintf($lang['user_delete_fail'], $username);
          }
          break;
          
        case 'block_user':
          $reason = trim($_POST['block_reason'] ?? '');
          if (blockMember($username, $reason)) {
            $success = $lang['member_blocked_success'];
          } else {
            $error = $lang['withdraw_failed'];
          }
          break;
          
        case 'unblock_user':
          if (unblockMember($username)) {
            $success = $lang['member_unblocked_success'];
          } else {
            $error = $lang['withdraw_failed'];
          }
          break;
          
        case 'change_level':
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

<?php if ($error): ?>
  <div style="background: var(--danger-color); color: white; padding: 1rem; border-radius: var(--radius); margin-bottom: 2rem;">
    <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div style="background: var(--success-color, #28a745); color: white; padding: 1rem; border-radius: var(--radius); margin-bottom: 2rem;">
    <?php echo htmlspecialchars($success); ?>
  </div>
<?php endif; ?>

<div class="admin-card">
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="margin: 0; color: var(--secondary-color);"><?php echo $lang['all_users']; ?> <span style="color: var(--text-light); font-weight: 400; font-size: 0.9em;">(<?php echo $total_users; ?>)</span></h3>
    </div>

    <?php if ($users): ?>
      <div style="overflow-x: auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th><?php echo $lang['user_id']; ?></th>
              <th style="text-align: center;"><?php echo $lang['member_level']; ?></th>
              <th style="text-align: center;"><?php echo $lang['member_status']; ?></th>
              <th><?php echo $lang['join_date']; ?></th>
              <th style="text-align: center; min-width: 150px;"><?php echo $lang['action']; ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): 
              $member_info = getMemberInfo($user['mb_id']);
              $is_blocked = $member_info['mb_blocked'] ?? 0;
              $is_withdrawn = $member_info['mb_leave_date'] !== null;
              $level = $member_info['mb_level'] ?? 1;
              
              // 관리자 여부 확인 수정
              $is_admin_user = ($user['mb_id'] === 'admin');
            ?>
              <tr>
                <td>
                  <strong style="color: var(--secondary-color);"><?php echo htmlspecialchars($user['mb_id']); ?></strong>
                  <?php if ($is_admin_user): ?>
                    <span style="background: var(--danger-color); color: white; padding: 2px 8px; border-radius: 999px; font-size: 0.75rem; margin-left: 0.5rem;"><?php echo $lang['admin_role']; ?></span>
                  <?php endif; ?>
                </td>
                <td style="text-align: center;">
                  <?php if ($is_admin_user): ?>
                    <span style="background: gold; color: black; padding: 4px 10px; border-radius: 999px; font-weight: bold; font-size: 0.85rem;">Lv.10</span>
                  <?php else: ?>
                    <form method="post" style="display: inline-flex; align-items: center; justify-content: center;">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                      <input type="hidden" name="action" value="change_level">
                      <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['mb_id']); ?>">
                      <select name="level" onchange="this.form.submit()" style="padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--bg-secondary); color: var(--text-color);">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                          <option value="<?php echo $i; ?>" <?php echo $level == $i ? 'selected' : ''; ?>>Lv.<?php echo $i; ?></option>
                        <?php endfor; ?>
                      </select>
                    </form>
                  <?php endif; ?>
                </td>
                <td style="text-align: center;">
                  <?php if ($is_withdrawn): ?>
                    <span style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; padding: 4px 10px; border-radius: 999px; font-size: 0.85rem; box-shadow: 0 1px 2px rgba(0,0,0,0.1);"><?php echo $lang['withdrawn']; ?></span>
                  <?php elseif ($is_blocked): ?>
                    <span style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; padding: 4px 10px; border-radius: 999px; font-size: 0.85rem; box-shadow: 0 1px 2px rgba(0,0,0,0.1);" title="<?php echo htmlspecialchars($member_info['mb_blocked_reason'] ?? ''); ?>"><?php echo $lang['blocked']; ?></span>
                  <?php else: ?>
                    <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 4px 10px; border-radius: 999px; font-size: 0.85rem; box-shadow: 0 1px 2px rgba(0,0,0,0.1);"><?php echo $lang['active']; ?></span>
                  <?php endif; ?>
                </td>
                <td style="color: var(--text-light); font-size: 0.9rem;">
                  <?php echo substr($user['mb_datetime'] ?? '-', 0, 16); ?>
                </td>
                <td style="text-align: center;">
                  <?php if ($is_admin_user): ?>
                    <span style="color: var(--text-light); font-size: 0.85rem; font-style: italic;">Locked</span>
                  <?php else: ?>
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                      <?php if (!$is_withdrawn): ?>
                        <?php if ($is_blocked): ?>
                          <form method="post" onsubmit="return confirm('<?php echo $lang['confirm_unblock']; ?>');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="unblock_user">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['mb_id']); ?>">
                            <button type="submit" class="btn-sm" style="background: #10b981; color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer;">✅</button>
                          </form>
                        <?php else: ?>
                          <button onclick="showBlockModal('<?php echo htmlspecialchars($user['mb_id']); ?>')" class="btn-sm" style="background: #f59e0b; color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer;">⛔</button>
                        <?php endif; ?>
                        
                        <form method="post" onsubmit="return confirm('<?php echo $lang['delete_user_confirm']; ?>');">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                          <input type="hidden" name="action" value="delete_user">
                          <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['mb_id']); ?>">
                          <button type="submit" class="btn-sm" style="background: #ef4444; color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer;">🗑️</button>
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
      <div style="text-align: center; padding: 3rem; color: var(--text-light);">
        <p><?php echo $lang['no_users']; ?></p>
      </div>
    <?php endif; ?>
</div>

<!-- 차단 모달 -->
<div id="blockModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
  <div style="background: var(--bg-color); padding: 2rem; border-radius: var(--radius-lg); max-width: 500px; width: 90%; box-shadow: var(--shadow-xl); border: 1px solid var(--border-color);">
    <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: var(--secondary-color);"><?php echo $lang['block_member']; ?></h3>
    <form method="post" id="blockForm">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <input type="hidden" name="action" value="block_user">
      <input type="hidden" name="username" id="blockUsername">
      
      <div style="margin-bottom: 1.5rem;">
        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);"><?php echo $lang['block_reason']; ?>:</label>
        <textarea name="block_reason" rows="4" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius); background: var(--bg-secondary); color: var(--text-color);" placeholder="<?php echo $lang['block_reason']; ?>"></textarea>
      </div>
      
      <div style="display: flex; gap: 1rem; justify-content: flex-end;">
        <button type="button" onclick="closeBlockModal()" style="background: var(--bg-secondary); color: var(--text-color); border: 1px solid var(--border-color); padding: 0.75rem 1.5rem; border-radius: var(--radius); cursor: pointer;"><?php echo $lang['cancel']; ?></button>
        <button type="submit" style="background: var(--danger-color); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: var(--radius); cursor: pointer; font-weight: 600;"><?php echo $lang['block_member']; ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function showBlockModal(username) {
  document.getElementById('blockUsername').value = username;
  document.getElementById('blockModal').style.display = 'flex';
}

function closeBlockModal() {
  document.getElementById('blockModal').style.display = 'none';
}

document.getElementById('blockModal').addEventListener('click', function(e) {
  if (e.target === this) closeBlockModal();
});
</script>

</main>
</div>
</body>
</html>
