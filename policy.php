<?php
require_once 'config.php';

$policy_type = $_GET['type'] ?? 'terms'; // terms 또는 privacy

// 유효한 타입인지 확인
if (!in_array($policy_type, ['terms', 'privacy'])) {
    $policy_type = 'terms';
}

// 정책 내용 가져오기
$lang_code = $_SESSION['lang'] ?? 'ko';
$lang_policy_type = $policy_type . '_' . $lang_code;

// 1. 해당 언어의 정책 시도
$policy = getPolicy($lang_policy_type);

// 2. 없으면 기본(한국어/영어 상관없이 기본 설정된) 정책 시도
if (!$policy) {
    $policy = getPolicy($policy_type);
}

// 3. 그래도 없으면 기본 텍스트 표시
if (!$policy) {
    // 기본 내용
    if ($policy_type === 'terms') {
        $policy = [
            'policy_title' => $lang['terms_of_service'],
            'policy_content' => '<p>' . ($lang['no_content'] ?? '내용이 없습니다.') . '</p>',
            'updated_at' => null
        ];
    } else {
        $policy = [
            'policy_title' => $lang['privacy_policy'],
            'policy_content' => '<p>' . ($lang['no_content'] ?? '내용이 없습니다.') . '</p>',
            'updated_at' => null
        ];
    }
}

$page_title = $policy['policy_title'];
require_once 'inc/header.php';
?>
<style>
    .policy-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem;
        background: var(--bg-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
    }
    .policy-header {
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }
    .policy-header h1 {
        margin: 0 0 0.5rem 0;
        color: var(--secondary-color);
        font-size: 2rem;
    }
    .policy-updated {
        color: var(--text-light);
        font-size: 0.875rem;
    }
    .policy-content {
        line-height: 1.8;
        color: var(--text-color);
    }
    .policy-content h2 {
        color: var(--primary-color);
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-size: 1.5em;
    }
    .policy-content h3 {
        color: var(--secondary-color);
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-size: 1.2em;
    }
    .policy-content p {
        margin-bottom: 1rem;
    }
    .policy-content ul, .policy-content ol {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }
    .policy-content li {
        margin-bottom: 0.5rem;
    }
    .back-link {
        display: inline-block;
        margin-top: 2rem;
        padding: 0.75rem 1.5rem;
        background: var(--bg-secondary);
        color: var(--text-color);
        text-decoration: none;
        border-radius: var(--radius);
        transition: var(--transition);
        border: 1px solid var(--border-color);
    }
    .back-link:hover {
        background: var(--bg-tertiary);
    }
    .policy-tabs {
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .policy-tab {
        padding: 0.75rem 1.5rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        text-decoration: none;
        color: var(--text-color);
        transition: var(--transition);
        font-weight: 500;
    }
    .policy-tab:hover {
        background: var(--bg-tertiary);
    }
    .policy-tab.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
</style>

<div class="content-wrapper">
    <div class="policy-container">
        <div class="policy-tabs">
            <a href="?type=terms" class="policy-tab <?php echo $policy_type === 'terms' ? 'active' : ''; ?>">
                <?php echo $lang['terms_of_service']; ?>
            </a>
            <a href="?type=privacy" class="policy-tab <?php echo $policy_type === 'privacy' ? 'active' : ''; ?>">
                <?php echo $lang['privacy_policy']; ?>
            </a>
        </div>
        
        <div class="policy-header">
            <h1><?php echo htmlspecialchars($policy['policy_title']); ?></h1>
            <?php if ($policy['updated_at']): ?>
                <p class="policy-updated">
                    <?php echo $lang['last_updated']; ?>: <?php echo htmlspecialchars($policy['updated_at']); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="policy-content">
            <?php echo $policy['policy_content']; ?>
        </div>
        
        <a href="javascript:history.back()" class="back-link"><?php echo $lang['list']; ?></a>
    </div>
</div>
<?php require_once 'inc/footer.php'; ?>
