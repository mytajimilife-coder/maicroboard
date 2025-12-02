<?php
require_once 'config.php';
require_once 'inc/oauth.php';

// State 검증
if (!isset($_SESSION['oauth_state']) || !isset($_REQUEST['state']) || $_SESSION['oauth_state'] !== $_REQUEST['state']) {
    die($lang['oauth_error_invalid_state'] ?? 'Invalid state parameter');
}

$provider = $_SESSION['oauth_provider'] ?? null;
if (!$provider) {
    die($lang['oauth_error_no_provider'] ?? 'No provider specified');
}

// 에러 체크
if (isset($_GET['error'])) {
    $error_msg = $_GET['error_description'] ?? $_GET['error'];
    header('Location: login.php?error=' . urlencode($error_msg));
    exit;
}

// Authorization code 받기
$code = $_REQUEST['code'] ?? null;
if (!$code) {
    header('Location: login.php?error=' . urlencode($lang['oauth_error_no_code'] ?? 'No authorization code'));
    exit;
}

// Access token 교환
$token_data = exchangeOAuthCode($provider, $code);
if (!$token_data || !isset($token_data['access_token'])) {
    header('Location: login.php?error=' . urlencode($lang['oauth_error_token_exchange'] ?? 'Failed to exchange token'));
    exit;
}

$access_token = $token_data['access_token'];

// 사용자 정보 가져오기
$user_info = getOAuthUserInfo($provider, $access_token);
if (!$user_info) {
    header('Location: login.php?error=' . urlencode($lang['oauth_error_user_info'] ?? 'Failed to get user info'));
    exit;
}

// Provider별 사용자 ID 추출
$provider_user_id = null;
switch ($provider) {
    case 'google':
        $provider_user_id = $user_info['id'] ?? null;
        break;
    case 'line':
        $provider_user_id = $user_info['userId'] ?? null;
        break;
    case 'apple':
        $provider_user_id = $user_info['sub'] ?? null;
        break;
}

if (!$provider_user_id) {
    header('Location: login.php?error=' . urlencode($lang['oauth_error_no_user_id'] ?? 'No user ID from provider'));
    exit;
}

// 사용자 생성 또는 로그인
$username = createOrUpdateOAuthUser($provider, $provider_user_id, $user_info);
if (!$username) {
    header('Location: login.php?error=' . urlencode($lang['oauth_error_create_user'] ?? 'Failed to create user'));
    exit;
}

// 로그인 처리
$_SESSION['user'] = $username;
$_SESSION['login_time'] = time();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// OAuth 세션 정보 정리
unset($_SESSION['oauth_state']);
unset($_SESSION['oauth_provider']);

// 메인 페이지로 리다이렉트
header('Location: list.php');
exit;
?>
