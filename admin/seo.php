<?php
define('IN_ADMIN', true);
$admin_title_key = 'seo_settings';
require_once 'common.php';

$db = getDB();

// 테이블 자동 생성
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `mb1_seo_config` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `bing_verification` VARCHAR(255) DEFAULT NULL,
        `google_search_console` VARCHAR(255) DEFAULT NULL,
        `google_analytics` TEXT DEFAULT NULL,
        `google_tag_manager` VARCHAR(255) DEFAULT NULL,
        `google_adsense` TEXT DEFAULT NULL,
        `header_script` TEXT DEFAULT NULL,
        `footer_script` TEXT DEFAULT NULL,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // 기본 레코드 확인 및 생성
    $stmt = $db->query("SELECT COUNT(*) FROM mb1_seo_config");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO mb1_seo_config (id) VALUES (1)");
    }
} catch (Exception $e) {
    // 오류 무시
}

// 설정 저장
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('<div class="admin-card"><p>' . $lang['csrf_token_invalid'] . '</p></div>');
    }
    
    $data = [
        'bing_verification' => trim($_POST['bing_verification'] ?? ''),
        'google_search_console' => trim($_POST['google_search_console'] ?? ''),
        'google_analytics' => trim($_POST['google_analytics'] ?? ''),
        'google_tag_manager' => trim($_POST['google_tag_manager'] ?? ''),
        'google_adsense' => trim($_POST['google_adsense'] ?? ''),
        'header_script' => trim($_POST['header_script'] ?? ''),
        'footer_script' => trim($_POST['footer_script'] ?? '')
    ];
    
    $sql = "UPDATE mb1_seo_config SET 
        bing_verification = :bing_verification,
        google_search_console = :google_search_console,
        google_analytics = :google_analytics,
        google_tag_manager = :google_tag_manager,
        google_adsense = :google_adsense,
        header_script = :header_script,
        footer_script = :footer_script
        WHERE id = 1";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($data);
    
    echo "<script>alert('" . ($lang['settings_saved'] ?? '설정이 저장되었습니다.') . "'); location.href='seo.php';</script>";
    exit;
}

// 현재 설정 가져오기
$stmt = $db->query("SELECT * FROM mb1_seo_config WHERE id = 1");
$config = $stmt->fetch() ?: [];
?>

<style>
.seo-section {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.seo-section h3 {
    margin: 0 0 1rem 0;
    color: var(--secondary-color);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-color);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    background: var(--bg-primary);
    color: var(--text-color);
    font-size: 1rem;
    font-family: 'Consolas', 'Monaco', monospace;
}

.form-control:focus {
    border-color: var(--primary-color);
    outline: none;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.help-text {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-top: 0.25rem;
}

.code-example {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 0.75rem;
    border-radius: var(--radius);
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 0.85rem;
    margin-top: 0.5rem;
    overflow-x: auto;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}

.btn-primary:hover {
    opacity: 0.9;
}
</style>

<div class="admin-card">
    <h2 style="margin-top: 0; color: var(--secondary-color);">
        🔍 <?php echo $lang['seo_settings'] ?? 'SEO 및 분석 도구 설정'; ?>
    </h2>
    <p style="color: var(--text-light); margin-bottom: 2rem;">
        <?php echo $lang['seo_settings_desc'] ?? '검색엔진 최적화 및 분석 도구를 설정합니다.'; ?>
    </p>
    
    <form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        
        <!-- Bing Webmaster -->
        <div class="seo-section">
            <h3>🔷 Bing Webmaster Tools</h3>
            <div class="form-group">
                <label><?php echo $lang['bing_verification_code'] ?? 'Bing 인증 메타 태그 content 값'; ?></label>
                <input type="text" name="bing_verification" class="form-control" 
                       value="<?php echo htmlspecialchars($config['bing_verification'] ?? ''); ?>"
                       placeholder="예: 1234567890ABCDEF">
                <p class="help-text">
                    💡 Bing Webmaster Tools에서 제공하는 메타 태그의 content 값만 입력하세요.
                </p>
                <div class="code-example">
                    &lt;meta name="msvalidate.01" content="<span style="color: #ce9178;">여기에 표시된 코드</span>" /&gt;
                </div>
            </div>
        </div>
        
        <!-- Google Search Console -->
        <div class="seo-section">
            <h3>🔍 Google Search Console</h3>
            <div class="form-group">
                <label><?php echo $lang['google_search_console_code'] ?? 'Google Search Console 인증 메타 태그 content 값'; ?></label>
                <input type="text" name="google_search_console" class="form-control" 
                       value="<?php echo htmlspecialchars($config['google_search_console'] ?? ''); ?>"
                       placeholder="예: 1234567890ABCDEF">
                <p class="help-text">
                    💡 Google Search Console에서 제공하는 메타 태그의 content 값만 입력하세요.
                </p>
                <div class="code-example">
                    &lt;meta name="google-site-verification" content="<span style="color: #ce9178;">여기에 표시된 코드</span>" /&gt;
                </div>
            </div>
        </div>
        
        <!-- Google Analytics -->
        <div class="seo-section">
            <h3>📊 Google Analytics (GA4)</h3>
            <div class="form-group">
                <label><?php echo $lang['google_analytics_script'] ?? 'Google Analytics 측정 ID'; ?></label>
                <input type="text" name="google_analytics" class="form-control" 
                       value="<?php echo htmlspecialchars($config['google_analytics'] ?? ''); ?>"
                       placeholder="예: G-XXXXXXXXXX">
                <p class="help-text">
                    💡 GA4 측정 ID를 입력하세요 (G-로 시작).
                </p>
            </div>
        </div>
        
        <!-- Google Tag Manager -->
        <div class="seo-section">
            <h3>🏷️ Google Tag Manager</h3>
            <div class="form-group">
                <label><?php echo $lang['google_tag_manager_id'] ?? 'Google Tag Manager ID'; ?></label>
                <input type="text" name="google_tag_manager" class="form-control" 
                       value="<?php echo htmlspecialchars($config['google_tag_manager'] ?? ''); ?>"
                       placeholder="예: GTM-XXXXXXX">
                <p class="help-text">
                    💡 Google Tag Manager 컨테이너 ID를 입력하세요 (GTM-으로 시작).
                </p>
            </div>
        </div>
        
        <!-- Google AdSense -->
        <div class="seo-section">
            <h3>💰 Google AdSense</h3>
            <div class="form-group">
                <label><?php echo $lang['google_adsense_client'] ?? 'Google AdSense 클라이언트 ID'; ?></label>
                <input type="text" name="google_adsense" class="form-control" 
                       value="<?php echo htmlspecialchars($config['google_adsense'] ?? ''); ?>"
                       placeholder="예: ca-pub-1234567890123456">
                <p class="help-text">
                    💡 AdSense 클라이언트 ID를 입력하세요 (ca-pub-로 시작).
                </p>
            </div>
        </div>
        
        <!-- Custom Header Script -->
        <div class="seo-section">
            <h3>📝 헤더 추가 스크립트</h3>
            <div class="form-group">
                <label><?php echo $lang['header_script'] ?? '헤더에 추가할 스크립트/메타 태그'; ?></label>
                <textarea name="header_script" class="form-control" rows="8"><?php echo htmlspecialchars($config['header_script'] ?? ''); ?></textarea>
                <p class="help-text">
                    💡 &lt;head&gt; 태그 안에 추가할 스크립트나 메타 태그를 입력하세요.
                </p>
            </div>
        </div>
        
        <!-- Custom Footer Script -->
        <div class="seo-section">
            <h3>📝 푸터 추가 스크립트</h3>
            <div class="form-group">
                <label><?php echo $lang['footer_script'] ?? '푸터에 추가할 스크립트'; ?></label>
                <textarea name="footer_script" class="form-control" rows="8"><?php echo htmlspecialchars($config['footer_script'] ?? ''); ?></textarea>
                <p class="help-text">
                    💡 &lt;/body&gt; 태그 직전에 추가할 스크립트를 입력하세요.
                </p>
            </div>
        </div>
        
        <div style="text-align: right; margin-top: 2rem;">
            <button type="submit" class="btn-primary">
                💾 <?php echo $lang['save'] ?? '저장'; ?>
            </button>
        </div>
    </form>
</div>

</main>
</div>
</body>
</html>
