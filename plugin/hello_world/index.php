<?php
if (!defined('MICROBOARD_VERSION')) exit;

// 플러그인 정보
$plugin_info = [
    'name' => 'Hello World',
    'version' => '1.0',
    'author' => 'MicroBoard Team',
    'description' => '간단한 예제 플러그인입니다.'
];

// 훅 등록
add_event('board_head', function($board) {
    echo '<div style="padding: 10px; background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; border-radius: 5px; margin-bottom: 20px;">';
    echo '<strong>Hello Plugin!</strong> 이 메시지는 플러그인에 의해 출력되었습니다.';
    echo ' (게시판: ' . htmlspecialchars($board['bo_subject']) . ')';
    echo '</div>';
});
?>
