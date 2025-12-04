<?php
require_once '../config.php';
requireLogin();

$error = '';
$success = '';

// 회원 탈퇴 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF 토큰 검증
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $error = $lang['csrf_token_invalid'];
  } else {
    $password = $_POST['password'] ?? '';
    $username = $_SESSION['user'];
    
    if (empty($password)) {
      $error = $lang['withdraw_password_confirm'];
    } else {
      // 회원 탈퇴 처리
      if (withdrawMember($username, $password)) {
        // 세션 종료
        session_unset();
        session_destroy();
        
        // 로그인 페이지로 리다이렉트
        header('Location: ../login.php?withdrawn=1');
        exit;
      } else {
        $error = $lang['withdraw_failed'];
      }
    }
  }
}

// CSRF 토큰 생성
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $lang['withdraw_account']; ?> - MicroBoard</title>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="../skin/default/style.css">
  <link rel="icon" type="image/png" href="../img/favicon.png">
  <style>
    .withdraw-container {
      max-width: 600px;
      margin: 100px auto;
      padding: 40px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .warning-box {
      background: #fff3cd;
      border: 1px solid #ffc107;
      border-radius: 4px;
      padding: 20px;
      margin-bottom: 30px;
    }
    .warning-box h3 {
      color: #856404;
      margin-top: 0;
    }
    .warning-box ul {
      color: #856404;
      margin: 10px 0;
      padding-left: 20px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    .btn-group {
      display: flex;
      gap: 10px;
      margin-top: 30px;
    }
    .btn {
      flex: 1;
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      text-decoration: none;
      text-align: center;
    }
    .btn-danger {
      background: #dc3545;
      color: white;
    }
    .btn-danger:hover {
      background: #c82333;
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    .btn-secondary:hover {
      background: #5a6268;
    }
    .error {
      color: #dc3545;
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="withdraw-container">
    <h2><?php echo $lang['withdraw_account']; ?></h2>
    
    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="warning-box">
      <h3>⚠️ <?php echo $lang['withdraw_confirm_title']; ?></h3>
      <p><?php echo $lang['withdraw_confirm_message']; ?></p>
      <ul>
        <li>작성한 모든 게시글과 댓글이 삭제됩니다.</li>
        <li>포인트 및 활동 내역이 모두 삭제됩니다.</li>
        <li>탈퇴 후 같은 아이디로 재가입할 수 없습니다.</li>
        <li>탈퇴 처리 후에는 복구가 불가능합니다.</li>
      </ul>
    </div>
    
    <form method="post" onsubmit="return confirm('<?php echo $lang['withdraw_confirm_title']; ?>\n\n<?php echo $lang['withdraw_confirm_message']; ?>');">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      
      <div class="form-group">
        <label for="password"><?php echo $lang['withdraw_password_confirm']; ?></label>
        <input type="password" name="password" id="password" placeholder="<?php echo $lang['password']; ?>" required>
      </div>
      
      <div class="btn-group">
        <a href="mypage.php" class="btn btn-secondary"><?php echo $lang['cancel']; ?></a>
        <button type="submit" class="btn btn-danger"><?php echo $lang['withdraw_account']; ?></button>
      </div>
    </form>
  </div>
</body>
</html>
